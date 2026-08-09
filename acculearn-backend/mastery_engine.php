<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

class MasteryEngine {

    /** A mastered competency with at least this CMI is flagged as a likely misconception. */
    const CMI_MISCONCEPTION_THRESHOLD = 0.20;

    /**
     * Fewest answered items before a competency may be called "Mastered".
     *
     * CM is correct/answered, so the DENOMINATOR is whatever evidence exists -
     * not the intended test length. With a partially entered pre-test that is
     * actively dangerous: enter one correct item for a competency and CM is
     * 1/1 = 100% = Mastered, which satisfies the prerequisite check and
     * unlocks the next competency on the strength of a single question.
     * (Real example on this site: teststudent has competency 2 recorded as
     * 1 correct out of 2 answered = 50%. Had that one item been the only one
     * entered and correct, the chain would have opened.)
     *
     * 8 is the floor because of how the thresholds divide. With n items CM can
     * only land on multiples of 1/n, and below 8 items no score falls in the
     * 75-84% "Developing" band while "Mastered" (>=85%) demands a perfect
     * paper: at n=5 mastery needs 5/5, at n=7 the Developing band cannot be
     * reached at all. From n=8, 7/8 = 87.5% is Mastered and 6/8 = 75% is
     * Developing, so both outcomes are actually reachable.
     *
     * Below this, a competency reports "Insufficient Evidence": CM/CMI/GI are
     * still computed and shown, but the competency is never treated as
     * Mastered and therefore never unlocks what follows it.
     */
    const MIN_ITEMS_FOR_MASTERY = 8;

    /**
     * Per-quiz pass status for every quiz belonging to a competency.
     *
     * A competency is Mastered only when EVERY formative quiz AND the
     * summative quiz have been passed - not when a pooled average crosses
     * 85%. Passing marks live on the Moodle grade item (formatives 80%,
     * summative 85%), so this reads the gradebook rather than recomputing
     * scores: that way a teacher's manual grade override (teacher_review.php)
     * correctly counts as a pass, and Moodle's "Highest grade" setting
     * already picks the student's best attempt for us.
     *
     * Summative vs formative is decided by quiz name. Verified consistent
     * across all 7 courses: each has exactly one quiz named "Summative Quiz"
     * plus 2-5 "Formative Quiz N". If a course is ever renamed so that no
     * quiz matches, 'summative_passed' stays false and the competency simply
     * cannot be mastered - it fails closed rather than silently opening the
     * chain.
     */
    public function competencyQuizStatus($userid, $competencyId) {
        $db = get_db();

        $stmt = $db->prepare("
            SELECT c.courseid FROM acculearn_competencies c WHERE c.id = ?");
        $stmt->execute([$competencyId]);
        $comp = $stmt->fetch();

        $empty = [
            'quizzes' => [], 'formatives_passed' => false,
            'summative_passed' => false, 'all_passed' => false,
            'passed_count' => 0, 'total_count' => 0,
        ];
        if (!$comp || !$comp->courseid) {
            return $empty;
        }

        $stmt = $db->prepare("
            SELECT q.id, q.name, gi.grademax, gi.gradepass, gg.finalgrade
              FROM mdl_quiz q
              JOIN mdl_grade_items gi
                ON gi.iteminstance = q.id AND gi.itemmodule = 'quiz'
               AND gi.itemtype = 'mod' AND gi.courseid = q.course
         LEFT JOIN mdl_grade_grades gg ON gg.itemid = gi.id AND gg.userid = ?
             WHERE q.course = ?
          ORDER BY q.id");
        $stmt->execute([$userid, $comp->courseid]);
        $rows = $stmt->fetchAll();

        if (!$rows) {
            return $empty;
        }

        $quizzes = [];
        $formatives = [];
        $summatives = [];
        foreach ($rows as $r) {
            $issummative = (stripos($r->name, 'summative') !== false);
            // A pass needs a real grade AND a configured passing mark. If no
            // gradepass is set the quiz cannot be passed - deliberately fails
            // closed, since an unset mark used to be the norm here.
            $passed = ($r->finalgrade !== null)
                && ((float) $r->gradepass > 0)
                && ((float) $r->finalgrade >= (float) $r->gradepass);

            $q = [
                'id' => (int) $r->id,
                'name' => $r->name,
                'is_summative' => $issummative,
                'grade' => $r->finalgrade === null ? null : round((float) $r->finalgrade, 2),
                'grademax' => round((float) $r->grademax, 2),
                'gradepass' => round((float) $r->gradepass, 2),
                'attempted' => $r->finalgrade !== null,
                'passed' => $passed,
            ];
            $quizzes[] = $q;
            if ($issummative) { $summatives[] = $q; } else { $formatives[] = $q; }
        }

        $formativespassed = !empty($formatives);
        foreach ($formatives as $q) {
            if (!$q['passed']) { $formativespassed = false; break; }
        }

        $summativepassed = !empty($summatives);
        foreach ($summatives as $q) {
            if (!$q['passed']) { $summativepassed = false; break; }
        }

        $passedcount = 0;
        foreach ($quizzes as $q) { if ($q['passed']) { $passedcount++; } }

        return [
            'quizzes' => $quizzes,
            'formatives_passed' => $formativespassed,
            'summative_passed' => $summativepassed,
            'all_passed' => ($formativespassed && $summativepassed),
            'passed_count' => $passedcount,
            'total_count' => count($quizzes),
        ];
    }

    /**
     * Sub-select yielding exactly ONE quiz attempt id per quiz for a user:
     * their BEST finished attempt, ties broken by the later attempt number.
     *
     * This exists because mastery was previously computed over EVERY attempt
     * a student had ever made. mdl_qbehaviour_confidence stores one row per
     * (attemptid, questionattemptid), so each retake APPENDED a fresh set of
     * rows rather than superseding the last one, and CM was correct/total
     * over the whole accumulated pile. A student who scored 0/5 and then 5/5
     * twice computed as 10/15 = 66.7%, so three attempts capped them below
     * the 85% Mastered threshold - permanently, since competencies form a
     * prerequisite chain and the quizaccess rule blocks a new attempt until
     * the prerequisite is Mastered. Retaking to improve, which the whole
     * three-retake design assumes, could therefore only ever hurt.
     *
     * "Best attempt" matches the gradebook: all 29 quizzes are configured
     * grademethod = 1 (Highest grade), so the mastery panel and the Moodle
     * grade now agree rather than contradicting each other.
     *
     * preview = 0 excludes teacher preview attempts, which are not student
     * evidence and would otherwise pollute a student's own figures if a
     * teacher previewed while logged in as them.
     */
    private function bestAttemptSubquery() {
        return "
            SELECT id FROM (
                SELECT id,
                       ROW_NUMBER() OVER (
                           PARTITION BY quiz
                           ORDER BY sumgrades DESC, attempt DESC
                       ) AS rn
                  FROM mdl_quiz_attempts
                 WHERE userid = ? AND state = 'finished' AND preview = 0
            ) ranked
            WHERE rn = 1
        ";
    }

    public function computeCompetencyStats($userid, $competencyId) {
        $db = get_db();
        $best = $this->bestAttemptSubquery();
        $sql = "
            SELECT
                qbc.confidence,
                CASE laststep.state
                    WHEN 'gradedright' THEN 1
                    WHEN 'gradedpartial' THEN 0
                    WHEN 'gradedwrong' THEN 0
                    ELSE 0
                END AS is_correct
            FROM mdl_qbehaviour_confidence qbc
            JOIN mdl_question_attempts qas ON qas.id = qbc.questionattemptid
            JOIN acculearn_question_competency qc ON qc.questionid = qas.questionid
            JOIN ($best) best ON best.id = qbc.attemptid
            LEFT JOIN mdl_question_attempt_steps laststep
                ON laststep.id = (
                    SELECT id FROM mdl_question_attempt_steps
                    WHERE questionattemptid = qbc.questionattemptid
                    ORDER BY sequencenumber DESC LIMIT 1
                )
            WHERE qbc.userid = ? AND qc.competency_id = ?
        ";
        $stmt = $db->prepare($sql);
        // The subquery's placeholder comes first in the SQL text, so $userid
        // is bound twice: once for the best-attempt filter, once for the
        // outer confidence rows.
        $stmt->execute([$userid, $userid, $competencyId]);
        $rows = $stmt->fetchAll();

        return $this->summarize($rows);
    }

    public function computeCompetencyStatsFromPretest($userid, $competencyId) {
        $db = get_db();
        $sql = "
            SELECT confidence, is_correct
            FROM acculearn_pretest_responses
            WHERE userid = ? AND competency_id = ?
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userid, $competencyId]);
        $rows = $stmt->fetchAll();

        return $this->summarize($rows);
    }

    private function summarize($rows) {
        $total = count($rows);
        if ($total === 0) {
            return ['cm' => null, 'cmi' => null, 'gi' => null, 'attempted' => 0];
        }

        $correctCount = 0;
        $highConfidenceIncorrect = 0;
        $lowConfidenceCorrect = 0;

        foreach ($rows as $r) {
            $correct = (bool) $r->is_correct;
            if ($correct) $correctCount++;
            if (!$correct && $r->confidence == 3) $highConfidenceIncorrect++;
            if ($correct && $r->confidence == 1) $lowConfidenceCorrect++;
        }

        return [
            'cm' => round(($correctCount / $total) * 100, 1),
            'cmi' => round($highConfidenceIncorrect / $total, 3),
            'gi' => round($lowConfidenceCorrect / $total, 3),
            'attempted' => $total,
        ];
    }

    /**
     * @param float|null $cm
     * @param int|null $attempted How many items the CM was computed over. When
     *   supplied and below MIN_ITEMS_FOR_MASTERY, the competency cannot be
     *   reported as Mastered - see that constant for why. Left optional so
     *   existing callers that only have a CM keep working unchanged.
     */
    public function classifyMastery($cm, $attempted = null) {
        if ($cm === null) return 'Not Attempted';
        if ($attempted !== null && $attempted > 0 && $attempted < self::MIN_ITEMS_FOR_MASTERY) {
            // Genuinely weak performance is still reportable on thin evidence -
            // it is only the claim of MASTERY (which unlocks the chain) that
            // needs a real sample behind it.
            return ($cm >= 75) ? 'Insufficient Evidence' : 'Needs Improvement';
        }
        if ($cm >= 85) return 'Mastered';
        if ($cm >= 75) return 'Developing';
        return 'Needs Improvement';
    }

    /**
     * Descriptive band for a competency that has NOT been mastered.
     *
     * Mastery is now decided solely by passing every quiz, so this never
     * returns 'Mastered' - a high pooled average with quizzes still
     * outstanding is 'Developing', not a pass. Keeping the two separate is
     * what stops the contradiction of a competency reading "Mastered" while
     * the next one sits visibly Locked.
     */
    private function bandBelowMastered($cm, $attempted) {
        if ($cm === null) return 'Not Attempted';
        if ($attempted > 0 && $attempted < self::MIN_ITEMS_FOR_MASTERY) {
            return ($cm >= 75) ? 'Insufficient Evidence' : 'Needs Improvement';
        }
        return ($cm >= 75) ? 'Developing' : 'Needs Improvement';
    }

    public function checkPrerequisite($userid, $competencyId, $source = 'quiz') {
        $db = get_db();
        $stmt = $db->prepare("SELECT prerequisite_id FROM acculearn_competencies WHERE id = ?");
        $stmt->execute([$competencyId]);
        $comp = $stmt->fetch();

        if (!$comp || !$comp->prerequisite_id) {
            return 1;
        }

        // Unlocking is now decided ONLY by whether the prerequisite
        // competency's quizzes were actually passed - every formative plus
        // the summative. Note this ignores $source deliberately: a strong
        // pre-test is a placement signal, not a demonstration, so it can
        // reorder a student's path but must never open the next competency.
        $prereq = $this->competencyQuizStatus($userid, $comp->prerequisite_id);

        return $prereq['all_passed'] ? 1 : 0;
    }

    public function computeLearningPathway($userid, $competencyId, $source = 'quiz') {
        $stats = ($source === 'pretest')
            ? $this->computeCompetencyStatsFromPretest($userid, $competencyId)
            : $this->computeCompetencyStats($userid, $competencyId);

        $pr = $this->checkPrerequisite($userid, $competencyId, $source);

        $cm = $stats['cm'];
        $cmi = $stats['cmi'];
        $gi = $stats['gi'];

        // Mastery is now earned by PASSING QUIZZES, not by a pooled average.
        // Every formative plus the summative must be passed; CM/CMI/GI remain
        // as diagnostics that shape the guidance, but no longer decide the gate.
        $quizstatus = $this->competencyQuizStatus($userid, $competencyId);
        $allpassed = $quizstatus['all_passed'];

        $mastery = $allpassed
            ? 'Mastered'
            : $this->bandBelowMastered($cm, $stats['attempted']);

        if ($pr === 0) {
            $lp = 1;                       // prerequisite competency not yet passed
        } elseif ($allpassed) {
            $lp = 4;                       // every quiz passed - advance
        } elseif ($cm === null) {
            $lp = null;                    // nothing attempted yet
        } elseif ($cm < 75) {
            $lp = 2;                       // guided remediation
        } else {
            $lp = 3;                       // on track, quizzes still outstanding
        }

        // Label the specific reason a non-mastered competency is held, so the
        // student is never told "advance" while a gate is visibly shut.
        if ($allpassed) {
            $lplabel = $this->lpLabel(4);
        } else if ($pr === 0) {
            $lplabel = $this->lpLabel(1);
        } else if ($quizstatus['total_count'] === 0) {
            $lplabel = $this->lpLabel($lp);
        } else if (!$quizstatus['formatives_passed']) {
            $lplabel = 'Pass every formative quiz to unlock the summative';
        } else {
            $lplabel = 'Pass the summative quiz to master this competency';
        }

        return [
            'competency_id' => $competencyId,
            'cm' => $cm,
            'cmi' => $cmi,
            'gi' => $gi,
            'pr' => $pr,
            'mastery' => $mastery,
            'attempted' => $stats['attempted'],
            'lp' => $lp,
            'lp_label' => $lplabel,
            // Per-quiz detail so the track can show exactly which quizzes are
            // still outstanding rather than only a percentage.
            'quizzes' => $quizstatus['quizzes'],
            'formatives_passed' => $quizstatus['formatives_passed'],
            'summative_passed' => $quizstatus['summative_passed'],
            'quizzes_passed' => $quizstatus['passed_count'],
            'quizzes_total' => $quizstatus['total_count'],
        ];
    }

    private function lpLabel($lp) {
        switch ($lp) {
            case 1: return 'Prerequisite Review';
            case 2: return 'Guided Remediation';
            case 3: return 'Guided Remediation';
            case 4: return 'Advancement to Next Competency';
            default: return 'Not Yet Assessed';
        }
    }

    public function computeFullTrack($userid, $source = 'quiz') {
        $db = get_db();
        $competencies = $db->query("SELECT * FROM acculearn_competencies ORDER BY sequence")->fetchAll();

        $track = [];
        foreach ($competencies as $c) {
            $result = $this->computeLearningPathway($userid, $c->id, $source);
            $result['competency_name'] = $c->name;
            $result['sequence'] = (int) $c->sequence;
            $track[] = $result;
        }
        return $this->prioritize($track);
    }

    /**
     * Turns the fixed co1..co7 list into an ADAPTIVE order driven by the
     * student's own evidence (pre-test first, then live quiz performance).
     *
     * The prerequisite chain is a real pedagogical dependency - you cannot
     * journalize before you know the five major accounts - so this does NOT
     * permute the chain and does NOT skip, hide or newly lock anything.
     * Every competency stays in the track and stays required. What adapts is
     * the ORDER things are presented in and which single competency is
     * called out as "do this next":
     *
     *   next     - the frontier: the first competency, in chain order, whose
     *              prerequisite is satisfied and which is not yet Mastered.
     *              A strong pre-test moves this forward, so a student who
     *              already knows co1-co3 starts at co4 instead of grinding
     *              through material they have demonstrably mastered.
     *   revisit  - Mastered, but with a Confidence Misconception Index at or
     *              above the threshold: answered confidently AND wrongly
     *              often enough to suggest a real misconception hiding
     *              behind a passing score. Surfaced ABOVE upcoming work
     *              because a wrong-but-confident foundation propagates
     *              forward through the chain. Advisory only - it never
     *              re-locks the competency or blocks progression.
     *   upcoming - not yet mastered and not the frontier; kept in chain
     *              order, since that is the order they unlock in.
     *   maintain - Mastered and no misconception signal. Ordered weakest
     *              first, so optional review effort goes where the margin
     *              is thinnest.
     */
    private function prioritize(array $track) {
        // Frontier: first unlocked, not-yet-mastered competency in chain order.
        $frontier = null;
        foreach ($track as $t) {
            if ((int) $t['pr'] === 1 && $t['mastery'] !== 'Mastered') {
                $frontier = $t['competency_id'];
                break;
            }
        }

        foreach ($track as &$t) {
            $mastered = ($t['mastery'] === 'Mastered');
            $misconception = $mastered
                && $t['cmi'] !== null
                && $t['cmi'] >= self::CMI_MISCONCEPTION_THRESHOLD;

            if ($t['competency_id'] === $frontier) {
                $t['priority_group'] = 'next';
                $t['priority_rank'] = 0;
                // Say exactly which quizzes are outstanding. "Still below
                // mastery" is useless guidance when what the student actually
                // needs is a specific quiz they have not passed yet.
                $outstanding = [];
                foreach (($t['quizzes'] ?? []) as $q) {
                    if (!$q['passed']) { $outstanding[] = $q['name']; }
                }
                if (empty($t['quizzes'])) {
                    $t['priority_reason'] =
                        'This is where your path continues - you have not been assessed on it yet.';
                } else if (!($t['formatives_passed'] ?? false)) {
                    $t['priority_reason'] = 'Pass every formative quiz here to unlock the summative. '
                        . 'Still to pass: ' . implode(', ', $outstanding) . '.';
                } else {
                    $t['priority_reason'] = 'All formative quizzes passed - the summative is now '
                        . 'unlocked. Pass it to master this competency and open the next one.';
                }
            } else if ($misconception) {
                $t['priority_group'] = 'revisit';
                $t['priority_rank'] = 1;
                $t['priority_reason'] =
                    'You passed this, but several answers were confidently incorrect - worth a second look '
                    . 'before it affects later topics.';
            } else if (!$mastered) {
                $t['priority_group'] = 'upcoming';
                $t['priority_rank'] = 2;
                $t['priority_reason'] = ((int) $t['pr'] === 0)
                    ? 'Unlocks once you master the competency it builds on.'
                    : 'Still ahead of you on your path.';
            } else {
                $t['priority_group'] = 'maintain';
                $t['priority_rank'] = 3;
                $t['priority_reason'] = 'Already mastered - revisit any time to keep it fresh.';
            }

            $t['blocked'] = ((int) $t['pr'] === 0);
            $t['is_next'] = ($t['competency_id'] === $frontier);
        }
        unset($t);

        usort($track, function ($a, $b) {
            if ($a['priority_rank'] !== $b['priority_rank']) {
                return $a['priority_rank'] <=> $b['priority_rank'];
            }
            // Worst misconception first.
            if ($a['priority_group'] === 'revisit') {
                return ($b['cmi'] ?? 0) <=> ($a['cmi'] ?? 0);
            }
            // Thinnest mastery margin first.
            if ($a['priority_group'] === 'maintain') {
                return ($a['cm'] ?? 0) <=> ($b['cm'] ?? 0);
            }
            // Everything else follows the chain, which is the order it unlocks in.
            return $a['sequence'] <=> $b['sequence'];
        });

        return $track;
    }
}
