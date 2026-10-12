<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/rate_limiter.php';
require_once __DIR__ . '/feedback_engine.php';

/**
 * The AI decision layer.
 *
 * Given a student's assessment EVIDENCE for every competency (CM/CMI/GI,
 * how many items they rest on, and the per-quiz pass record), one
 * structured-output call to the model returns the DECISIONS: mastery tier,
 * learning pathway, adaptive order and priority group per competency, plus
 * the narrative that explains them.
 *
 * Since 2026-10-06 the model must follow the Chapter 3 formula for mastery
 * and LP (MasteryEngine::PASSING_THRESHOLD documents it): the formula is
 * stated in the prompt, and any answer whose mastery or LP differs from what
 * the formula gives (MasteryEngine::formulaDecisions()) is sent back for
 * correction and, failing that, rejected so the formula decides outright.
 * What remains the model's own is the adaptive order, the single "next"
 * competency, the priority groups, and all of the wording. Before
 * 2026-10-06 the model was given no formula and decided freely.
 *
 * The decisions are applied directly: an LP of 1 locks the competency's
 * quizzes (quizaccess_acculearn) and removes the student from the course's
 * "AccuLearn Unlocked" group. Because of that, the response is checked here
 * for STRUCTURAL validity - every competency present exactly once, the
 * labels drawn from the fixed vocabulary the pages render, a single "next"
 * competency - and for agreement with the formula and COHERENCE between
 * labels (see coherenceProblems()). A response that fails
 * those checks is rejected as a whole (an exception, after one corrective
 * retry for coherence), not patched into something the model did not say.
 */
class DecisionEngine {

    /** Model that makes the decisions. Recorded on every stored track. */
    const MODEL = 'gpt-4o-mini';

    const MASTERY_LABELS = ['Mastered', 'Initial Mastery', 'Developing', 'Needs Improvement', 'Insufficient Evidence', 'Not Attempted'];
    const PRIORITY_GROUPS = ['next', 'revisit', 'upcoming', 'maintain'];
    /** What the model may put on a row; 'next' is set from next_competency_id instead. */
    const ROW_GROUPS = ['revisit', 'upcoming', 'maintain'];
    const PRIORITY_RANKS = ['next' => 0, 'revisit' => 1, 'upcoming' => 2, 'maintain' => 3];

    /** Longest free-text field the pages will render; anything past this is cut. */
    const MAX_TEXT = 700;

    /** How many times an incoherent answer is sent back for correction before the rule decides. */
    const MAX_CORRECTIONS = 2;

    /**
     * Decides a whole track at once.
     *
     * @param array $evidence rows from MasteryEngine::computeFullEvidence(),
     *   in curriculum (chain) order.
     * @param string $studentName the placeholder token, never a real name.
     * @param string $source 'quiz' or 'pretest'.
     * @param array $formula MasteryEngine::formulaDecisions() for the same
     *   student and source: competency id => pr, mastery, lp. The model is
     *   given each pr and must reproduce each mastery and lp.
     * @return array ['competencies' => [...in adaptive order...], 'narrative' => [...]]
     *   Each competency row is the evidence row plus the decision fields:
     *   mastery, lp, lp_label, pr, blocked, priority_group, priority_rank,
     *   priority_reason, rationale, is_next, decided_by.
     * @throws Exception when the model is unavailable or its answer is not
     *   structurally usable.
     */
    public function decide(array $evidence, $studentName, $source, array $formula) {
        if (empty($evidence)) {
            throw new Exception('DecisionEngine: no competencies to decide.');
        }

        $limiter = new RateLimiter();
        if (!$limiter->allow('openai', OPENAI_RATE_LIMIT_PER_HOUR, 3600)) {
            throw new Exception("OpenAI rate limit reached (" . OPENAI_RATE_LIMIT_PER_HOUR . "/hour) - decision deferred.");
        }

        $system = $this->systemPrompt($source);
        $user = $this->userPrompt($evidence, $studentName, $source, $formula);

        $messages = [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ];
        list($raw, $rawtext) = $this->callModel($messages, $this->schema());
        $decided = $this->validate($raw, $evidence);

        // The labels have consequences that must not contradict each other
        // (a competency the student is told to advance INTO must not be
        // locked). An answer that does contradict itself goes back to the
        // model as its own previous turn, with the specific problems named,
        // up to MAX_CORRECTIONS times - shown its own answer it usually fixes
        // them; told only the problems (at temperature 0) it tended to make
        // the same answer again. If it still cannot, the answer is rejected
        // and the formula decides: a self-contradictory track is worse than a
        // rule-decided one.
        $problems = $this->coherenceProblems($decided['competencies'], $evidence, $formula);
        $corrections = 0;
        while (!empty($problems) && $corrections < self::MAX_CORRECTIONS) {
            $corrections++;
            error_log("AccuLearn: AI decision incoherent (correction $corrections): " . implode(' | ', $problems));
            $messages[] = ['role' => 'assistant', 'content' => $rawtext];
            $messages[] = ['role' => 'user', 'content' =>
                "Your answer above contradicts itself. Decide again, keeping everything that was "
                . "coherent, and resolve every one of these:\n- " . implode("\n- ", $problems)
                // Without this the model fixed the numbers and left the old
                // wording behind - a locked competency still labelled "ready
                // to start" - which the student would read as the decision.
                . "\nFor every competency whose mastery or lp changes, rewrite its lp_label, "
                . "priority_reason and rationale to match the new values, and make the narrative "
                . "agree with them too. A competency at lp 1 is locked: say it opens once the "
                . "competency it builds on reaches the 70% passing level."];
            list($raw, $rawtext) = $this->callModel($messages, $this->schema());
            $decided = $this->validate($raw, $evidence);
            $problems = $this->coherenceProblems($decided['competencies'], $evidence, $formula);
        }
        if (!empty($problems)) {
            throw new Exception("DecisionEngine: answer still contradicts itself after $corrections correction(s): "
                . implode(' | ', $problems));
        }

        return $decided;
    }

    // ---- prompt -----------------------------------------------------------

    private function systemPrompt($source) {
        $context = ($source === 'pretest')
            ? "This is the student's INITIAL placement. The only evidence is a paper pre-test taken "
              . "before instruction began (no quiz records are shown, because the placement is made "
              . "from the pre-test alone). A competency with pre-test items answered HAS evidence: its "
              . "mastery is never 'Not Attempted'."
            : "This is an UPDATE from the student's live quiz performance in the course so far.";

        return "You are AccuLearn's adaptive-learning decision model for a Grade 11 FABM 1 "
            . "(Fundamentals of Accountancy, Business and Management 1) bookkeeping course. For every "
            . "competency you apply AccuLearn's fixed rule for mastery status and learning pathway, then "
            . "decide the adaptive order in which the competencies should be presented to the student, "
            . "and explain all of it to them.\n\n"
            . "Your decisions are applied directly by the system, so they carry real consequences: a "
            . "pathway of 1 locks that competency's quizzes until an earlier competency is mastered, and "
            . "the competency you mark 'next' is the one the student is told to work on now.\n\n"
            . "$context\n\n"
            . "THE COURSE\n"
            . "Seven competencies, listed in curriculum order. Each is taught as its own course of "
            . "lessons with formative (practice) quizzes at an 80% pass mark and one summative quiz at an "
            . "85% pass mark. Each competency builds on the one before it (the five major accounts must be "
            . "understood before journalizing them, and so on). Readiness for a competency is measured by "
            . "the system and given to you as pr (see the rule below).\n\n"
            . "THE EVIDENCE\n"
            . "Every item is answered under confidence-based marking: the student also rates their "
            . "confidence 1 (a guess), 2 (fairly sure) or 3 (certain).\n"
            . "- cm: Competency Mastery, the percentage of answered items that were correct.\n"
            . "- cmi: Confidence Misconception Index, the fraction of answered items that were answered "
            . "CERTAIN and WRONG - a signal of confidently held misconceptions.\n"
            . "- gi: Guessing Index, the fraction of answered items that were a GUESS and CORRECT - a "
            . "signal that a score is partly luck.\n"
            . "- attempted: how many items the three indices rest on. Judge how much a figure is worth by "
            . "this; a percentage from two items is not evidence of much.\n"
            . "- quizzes: the student's record on each quiz in that competency (attempted, grade, pass "
            . "mark, passed). Null cm/cmi/gi means nothing has been answered in that competency.\n\n"
            . "YOUR OUTPUT VOCABULARY (use these labels exactly)\n"
            . "mastery: 'Mastered' | 'Initial Mastery' | 'Developing' | 'Needs Improvement' | "
            . "'Insufficient Evidence' (looks capable but on too little evidence) | 'Not Attempted'.\n"
            . "lp (learning pathway) - what each value means:\n"
            . "  1 = Prerequisite Review: the student is NOT ready for this competency; its quizzes are "
            . "LOCKED until they finish an earlier one. Any competency the student should not be "
            . "attempting yet is 1, whether or not it has evidence.\n"
            . "  Every other value means its quizzes are OPEN to the student right now:\n"
            . "  0 = Not Yet Assessed: open and ready to start, but no evidence yet (normally only the "
            . "'next' competency).\n"
            . "  2 = Guided Remediation: open; the evidence shows they need re-teaching here.\n"
            . "  3 = Reinforcement Practice: open; on track, needs more practice or a misconception "
            . "cleared.\n"
            . "  4 = Advancement to Next Competency: finished; mastered, move on.\n"
            . "next_competency_id: the ONE competency the student should work on now, or 0 if every "
            . "competency is mastered.\n"
            . "group (per competency): 'revisit' = mastered, but the evidence suggests a misconception "
            . "worth clearing before it affects later topics; 'upcoming' = not finished yet (this is "
            . "also what you put on the next_competency_id competency itself); 'maintain' = mastered, "
            . "optional review only.\n"
            . "HOW MASTERY AND PATHWAY ARE DECIDED - a fixed rule; apply it exactly, never override it\n"
            . "Each competency carries pr, its prerequisite readiness as measured by the system: 1 when "
            . "the competency it builds on has reached the 70% passing level on at least 8 answered "
            . "items (always 1 for a competency that builds on nothing), otherwise 0. Use pr exactly as "
            . "given.\n"
            . "mastery, from cm, attempted and evidence_source:\n"
            . "- cm null (nothing answered): 'Not Attempted'.\n"
            . "- fewer than 8 items answered: 'Insufficient Evidence' if cm is 70 or above, otherwise "
            . "'Needs Improvement'.\n"
            . "- cm 85 or above: 'Mastered' when evidence_source is 'quiz'; 'Initial Mastery' when it is "
            . "'pretest' (a strong diagnostic is a placement, not an achievement).\n"
            . "- cm 70 to below 85: 'Developing'.\n"
            . "- cm below 70: 'Needs Improvement'.\n"
            . "lp - check these in order and use the FIRST that applies:\n"
            . "  1 if pr = 0\n"
            . "  0 if cm is null (nothing answered yet)\n"
            . "  2 if cm < 70\n"
            . "  3 if cm < 85\n"
            . "  3 if gi >= 0.20 (a strong score that leans on guessing)\n"
            . "  3 if cmi >= 0.20 (a strong score hiding confident wrong answers)\n"
            . "  3 if mastery is not 'Mastered' (too few items, or only a pre-test placement)\n"
            . "  4 otherwise.\n"
            . "WHAT IS YOURS TO DECIDE\n"
            . "- next_competency_id is where the student works NOW, so it must be a competency whose "
            . "quizzes are open and unfinished: never lp 1 (locked) and never lp 4 (already finished). "
            . "Prefer the earliest such competency in curriculum order unless the evidence gives a clear "
            . "reason otherwise. It is 0 only if no competency is open and unfinished.\n"
            . "- group: 'revisit' and 'maintain' are for 'Mastered' competencies only; anything not "
            . "mastered is 'upcoming'.\n"
            . "- Everything you write to the student must agree with these labels: do not tell them to "
            . "move on to a competency that is locked.\n"
            . "Return the competencies in the ADAPTIVE ORDER the student should see them: what to do "
            . "now first, then anything to revisit, then what is coming up, then what is maintained. "
            . "Include every competency exactly once. Nothing is skipped or removed - you are ordering "
            . "and labelling, not pruning.\n\n"
            . "TEXT FIELDS\n"
            . "- lp_label: at most ten words, student-facing, names the pathway or the specific reason "
            . "for it (e.g. 'Strong score, but confident wrong answers suggest a misconception').\n"
            . "- priority_reason: one or two sentences to the student on why this competency sits where "
            . "it does and what to do about it. Name the specific quizzes still outstanding when that is "
            . "the point.\n"
            . "- rationale: one to three sentences for the TEACHER, written in the third person ('the "
            . "student'), citing the actual evidence (figures, item counts, quiz results) that led to "
            . "the mastery and pathway decision.\n"
            . "- narrative.overview, narrative.next_focus, narrative.encouragement: a plain-language, "
            . "encouraging summary of the whole track for the student. next_focus must be about the "
            . "next_competency_id competency.\n\n"
            . "Be consistent: the same evidence must always lead to the same decision. "
            . "Never tell the student a competency has been skipped or removed.\n"
            . "The student is identified to you only as the literal token "
            . FeedbackEngine::STUDENT_PLACEHOLDER . ". Address them using that exact token wherever you "
            . "would use a name, and never invent or guess a real name.";
    }

    private function userPrompt(array $evidence, $studentName, $source, array $formula) {
        $rows = [];
        foreach ($evidence as $e) {
            // A pre-test placement is made from the paper pre-test alone, as
            // the thesis describes it. The quiz record is course evidence
            // and is kept out of the model's view here (it is still stored
            // on the track for the checklist the page shows).
            if ($source === 'pretest') {
                $e['quizzes'] = [];
                $e['quizzes_passed'] = 0;
                $e['quizzes_total'] = 0;
            }
            $quizzes = [];
            foreach (($e['quizzes'] ?? []) as $q) {
                $quizzes[] = [
                    'name' => $q['name'],
                    'type' => $q['is_summative'] ? 'summative' : 'formative',
                    'attempted' => (bool) $q['attempted'],
                    'grade' => $q['grade'],
                    'grademax' => $q['grademax'],
                    'gradepass' => $q['gradepass'],
                    'passed' => (bool) $q['passed'],
                ];
            }
            $rows[] = [
                'competency_id' => (int) $e['competency_id'],
                'sequence' => (int) $e['sequence'],
                'name' => $e['competency_name'],
                'builds_on_competency_id' => $e['prerequisite_id'],
                // Measured by the system rather than left to the model: for a
                // quiz-sourced track it rests on the prerequisite's QUIZ
                // record, which this row does not show in full.
                'pr' => (int) $formula[(int) $e['competency_id']]['pr'],
                'evidence_source' => $e['evidence_source'] ?? $source,
                // A readable restatement of the figures below it, so the
                // model does not have to reconstruct "how much evidence is
                // this" from raw fields - its first answers over-read a
                // single passed quiz as "the formative quizzes".
                'summary' => $this->evidenceSummary($e),
                'cm' => $e['cm'],
                'cmi' => $e['cmi'],
                'gi' => $e['gi'],
                'attempted' => (int) $e['attempted'],
                'quizzes' => $quizzes,
            ];
        }

        return "Student: $studentName\n"
            . "Evidence source: $source\n"
            . "Competencies in curriculum order:\n"
            . json_encode($rows, JSON_PRETTY_PRINT);
    }

    private function evidenceSummary(array $e) {
        $items = (int) $e['attempted'];
        if ($items === 0 && empty(array_filter($e['quizzes'] ?? [], function ($q) { return !empty($q['attempted']); }))) {
            return 'No evidence: nothing answered, no quiz attempted.';
        }
        $parts = [];
        $parts[] = $items === 0
            ? 'no items answered'
            : "$items item" . ($items === 1 ? '' : 's') . " answered" . (empty($e['quizzes']) ? ' on the pre-test' : '')
              . ", " . $e['cm'] . "% correct"
              . ", " . round($e['cmi'] * 100) . "% answered certain-and-wrong"
              . ", " . round($e['gi'] * 100) . "% answered as a guess-and-right";
        $quizbits = [];
        foreach (($e['quizzes'] ?? []) as $q) {
            if (!empty($q['passed'])) {
                $quizbits[] = $q['name'] . ' passed (' . $q['grade'] . '/' . $q['grademax'] . ')';
            } else if (!empty($q['attempted'])) {
                $quizbits[] = $q['name'] . ' attempted but not passed (' . $q['grade'] . '/' . $q['grademax']
                    . ', needs ' . $q['gradepass'] . ')';
            } else {
                $quizbits[] = $q['name'] . ' not taken';
            }
        }
        if (!empty($quizbits)) {
            $parts[] = (int) $e['quizzes_passed'] . ' of ' . (int) $e['quizzes_total'] . ' quizzes passed: '
                . implode('; ', $quizbits);
        }
        return implode('. ', $parts) . '.';
    }

    /**
     * Contradictions between labels, as described in the system prompt's
     * "what the labels commit you to". Empty when the answer is coherent.
     *
     * @param array $decided validated rows (any order)
     * @param array $evidence the evidence rows, for the prerequisite chain
     */
    private function coherenceProblems(array $decided, array $evidence, array $formula) {
        $byid = [];
        $nextid = 0;
        foreach ($decided as $d) {
            $byid[(int) $d['competency_id']] = $d;
            if (!empty($d['is_next'])) {
                $nextid = (int) $d['competency_id'];
            }
        }
        $problems = [];
        foreach ($evidence as $e) {
            $id = (int) $e['competency_id'];
            $d = $byid[$id];
            $name = $e['competency_name'];
            $rule = $formula[$id];

            // Mastery and LP are the formula's, not the model's. Compared as
            // decided values (lp null = "0, not yet assessed").
            if ($d['mastery'] !== $rule['mastery']) {
                $problems[] = "'$name' has cm " . ($e['cm'] === null ? 'null' : $e['cm'])
                    . " on " . (int) $e['attempted'] . " items (evidence_source "
                    . ($e['evidence_source'] ?? 'quiz') . "), so by the rule its mastery is '{$rule['mastery']}', not '{$d['mastery']}'.";
            }
            if ($d['lp'] !== $rule['lp']) {
                $problems[] = "'$name' has pr {$rule['pr']}, cm " . ($e['cm'] === null ? 'null' : $e['cm'])
                    . ", gi " . ($e['gi'] ?? 'null') . ", cmi " . ($e['cmi'] ?? 'null')
                    . ", so by the rule its lp is " . ($rule['lp'] ?? 0) . ", not " . ($d['lp'] ?? 0) . ".";
            }

            if (in_array($d['priority_group'], ['revisit', 'maintain'], true) && $d['mastery'] !== 'Mastered') {
                $problems[] = "'$name' is grouped '{$d['priority_group']}' but is not 'Mastered': that group is for mastered competencies only; use 'upcoming'.";
            }
            if ($d['priority_group'] === 'next' && ($d['lp'] === 1 || $d['lp'] === 4)) {
                $problems[] = "next_competency_id names '$name' but it has lp {$d['lp']}: the next competency must be open and unfinished (lp 0, 2 or 3).";
            }
            if ($d['priority_group'] !== 'next' && $d['lp'] !== 4 && $d['lp'] !== 1 && $nextid === 0) {
                $problems[] = "next_competency_id is 0 (everything mastered) but '$name' is open and unfinished (lp " . ($d['lp'] ?? 0) . ").";
            }
        }
        return $problems;
    }

    private function schema() {
        return [
            'type' => 'object',
            'properties' => [
                // A single slot: the model cannot mark two competencies
                // "next" the way a per-row flag let it.
                'next_competency_id' => ['type' => 'integer'],
                'competencies' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'competency_id' => ['type' => 'integer'],
                            'mastery' => ['type' => 'string', 'enum' => self::MASTERY_LABELS],
                            // 0 stands in for "not yet assessed" (null) - strict
                            // mode is happier with a single-type integer enum.
                            'lp' => ['type' => 'integer', 'enum' => [0, 1, 2, 3, 4]],
                            'lp_label' => ['type' => 'string'],
                            'group' => ['type' => 'string', 'enum' => self::ROW_GROUPS],
                            'priority_reason' => ['type' => 'string'],
                            'rationale' => ['type' => 'string'],
                        ],
                        'required' => ['competency_id', 'mastery', 'lp', 'lp_label',
                            'group', 'priority_reason', 'rationale'],
                        'additionalProperties' => false,
                    ],
                ],
                'narrative' => [
                    'type' => 'object',
                    'properties' => [
                        'overview' => ['type' => 'string'],
                        'next_focus' => ['type' => 'string'],
                        'encouragement' => ['type' => 'string'],
                    ],
                    'required' => ['overview', 'next_focus', 'encouragement'],
                    'additionalProperties' => false,
                ],
            ],
            'required' => ['next_competency_id', 'competencies', 'narrative'],
            'additionalProperties' => false,
        ];
    }

    // ---- validation -------------------------------------------------------

    /**
     * Structural checks only. See the class docblock for what is and is not
     * checked, and why a failure rejects the whole answer.
     */
    private function validate($raw, array $evidence) {
        if (!is_array($raw) || !isset($raw['competencies']) || !is_array($raw['competencies'])
                || !isset($raw['narrative']) || !is_array($raw['narrative'])) {
            throw new Exception('DecisionEngine: response is missing competencies or narrative.');
        }

        $byid = [];
        foreach ($evidence as $e) {
            $byid[(int) $e['competency_id']] = $e;
        }

        $nextid = (int) ($raw['next_competency_id'] ?? 0);
        if ($nextid !== 0 && !isset($byid[$nextid])) {
            throw new Exception("DecisionEngine: next_competency_id $nextid is not a competency.");
        }

        $seen = [];
        $decided = [];
        foreach ($raw['competencies'] as $d) {
            $id = (int) ($d['competency_id'] ?? 0);
            if (!isset($byid[$id])) {
                throw new Exception("DecisionEngine: decision for unknown competency id $id.");
            }
            if (isset($seen[$id])) {
                throw new Exception("DecisionEngine: competency id $id decided twice.");
            }
            $seen[$id] = true;

            if (!in_array($d['mastery'] ?? null, self::MASTERY_LABELS, true)) {
                throw new Exception("DecisionEngine: unknown mastery label for competency $id.");
            }
            if (!in_array($d['group'] ?? null, self::ROW_GROUPS, true)) {
                throw new Exception("DecisionEngine: unknown group for competency $id.");
            }
            $lp = isset($d['lp']) ? (int) $d['lp'] : 0;
            if ($lp < 0 || $lp > 4) {
                throw new Exception("DecisionEngine: pathway $lp out of range for competency $id.");
            }
            $group = ($id === $nextid) ? 'next' : $d['group'];

            $row = $byid[$id];
            $row['mastery'] = $d['mastery'];
            $row['lp'] = ($lp === 0) ? null : $lp;
            $row['lp_label'] = $this->text($d['lp_label'] ?? '') ?: $this->defaultLabel($row['lp']);
            // PR is the thesis's prerequisite-readiness flag. Under the AI
            // decision it is implied by the pathway: LP 1 IS "not ready".
            $row['pr'] = ($row['lp'] === 1) ? 0 : 1;
            $row['blocked'] = ($row['lp'] === 1);
            $row['priority_group'] = $group;
            $row['priority_rank'] = self::PRIORITY_RANKS[$group];
            $row['priority_reason'] = $this->text($d['priority_reason'] ?? '');
            $row['rationale'] = $this->text($d['rationale'] ?? '');
            $row['is_next'] = ($group === 'next');
            $row['decided_by'] = 'ai';
            $decided[] = $row;
        }

        if (count($seen) !== count($byid)) {
            $missing = array_diff(array_keys($byid), array_keys($seen));
            throw new Exception('DecisionEngine: no decision for competency id(s) '
                . implode(', ', $missing) . '.');
        }
        $narrative = [];
        foreach (['overview', 'next_focus', 'encouragement'] as $k) {
            $narrative[$k] = $this->text($raw['narrative'][$k] ?? '');
        }
        if ($narrative['overview'] === '') {
            throw new Exception('DecisionEngine: empty narrative overview.');
        }

        return ['competencies' => $decided, 'narrative' => $narrative];
    }

    private function text($s) {
        $s = trim((string) $s);
        if (mb_strlen($s) > self::MAX_TEXT) {
            $s = rtrim(mb_substr($s, 0, self::MAX_TEXT - 1)) . '…';
        }
        return $s;
    }

    private function defaultLabel($lp) {
        switch ($lp) {
            case 1: return 'Prerequisite Review';
            case 2: return 'Guided Remediation';
            case 3: return 'Reinforcement Practice';
            case 4: return 'Advancement to Next Competency';
            default: return 'Not Yet Assessed';
        }
    }

    // ---- transport --------------------------------------------------------

    /**
     * @param array $messages role/content turns, system first.
     * @return array [decoded JSON, raw JSON text] - the raw text is what goes
     *   back to the model as its own turn on a correction round.
     */
    private function callModel(array $messages, $schema) {
        $payload = [
            // ACCULEARN_DECISION_MODEL is a test hook: verify_ai_decides.php
            // defines it to a nonexistent model so the call fails and the
            // rule fallback can be shown to engage. Never set in production.
            'model' => defined('ACCULEARN_DECISION_MODEL') ? ACCULEARN_DECISION_MODEL : self::MODEL,
            // Decisions should be repeatable: the same evidence on two
            // different days should place the student the same way.
            'temperature' => 0,
            'input' => $messages,
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'track_decision',
                    'schema' => $schema,
                    'strict' => true,
                ],
            ],
        ];

        $ch = curl_init('https://api.openai.com/v1/responses');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Bounded waits - see the matching note in feedback_engine.php. This
        // runs inside a Moodle cron worker; a hung call would stall it.
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($ch);
        $curlerror = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new Exception("OpenAI request failed (transport): $curlerror");
        }
        if ($httpcode !== 200) {
            throw new Exception("OpenAI API error ($httpcode): $response");
        }

        $data = json_decode($response, true);
        $outputText = $data['output'][0]['content'][0]['text'] ?? null;
        if (!$outputText) {
            throw new Exception("Unexpected OpenAI response format: " . $response);
        }

        $decoded = json_decode($outputText, true);
        if (!is_array($decoded)) {
            throw new Exception('DecisionEngine: model output was not valid JSON.');
        }
        return [$decoded, $outputText];
    }
}
