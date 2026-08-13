<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/moodle_client.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/rate_limiter.php';

class FeedbackEngine {

    private $moodle;

    public function __construct() {
        $this->moodle = new MoodleClient();
    }

    private function cbmScore($confidence, $correct) {
        $scoring = [
            1 => ['correct' => 1, 'incorrect' => 0],
            2 => ['correct' => 2, 'incorrect' => -2],
            3 => ['correct' => 3, 'incorrect' => -6],
        ];
        return $correct ? $scoring[$confidence]['correct'] : $scoring[$confidence]['incorrect'];
    }

    /**
     * Pull real per-question data for an attempt: confidence, correctness,
     * question text, student's answer text, and the correct answer text.
     */
public function getAttemptDetails($attemptid) {
    $db = get_db();
    $sql = "
        SELECT
            qbc.slot,
            qbc.confidence,
            qbc.questionattemptid,
            que.id AS question_id,
            que.name AS question_name,
            que.questiontext AS question_text,
            qasdata.value AS selected_answer_index,
            orderdata.value AS answer_order,
            CASE laststep.state
                WHEN 'gradedright' THEN 1
                WHEN 'gradedpartial' THEN 0
                WHEN 'gradedwrong' THEN 0
                ELSE 0
            END AS is_correct
        FROM mdl_qbehaviour_confidence qbc
        JOIN mdl_question_attempts qas ON qas.id = qbc.questionattemptid
        JOIN mdl_question que ON que.id = qas.questionid
        LEFT JOIN mdl_question_attempt_steps laststep
            ON laststep.id = (
                SELECT id FROM mdl_question_attempt_steps
                WHERE questionattemptid = qbc.questionattemptid
                ORDER BY sequencenumber DESC LIMIT 1
            )
        LEFT JOIN mdl_question_attempt_step_data qasdata
            ON qasdata.attemptstepid = (
                SELECT qas2.id
                FROM mdl_question_attempt_steps qas2
                JOIN mdl_question_attempt_step_data qasd2 ON qasd2.attemptstepid = qas2.id
                WHERE qas2.questionattemptid = qbc.questionattemptid
                  AND qasd2.name = 'answer'
                ORDER BY qas2.sequencenumber DESC LIMIT 1
            )
            AND qasdata.name = 'answer'
        LEFT JOIN mdl_question_attempt_step_data orderdata
            ON orderdata.attemptstepid = (
                SELECT qas3.id
                FROM mdl_question_attempt_steps qas3
                JOIN mdl_question_attempt_step_data qasd3 ON qasd3.attemptstepid = qas3.id
                WHERE qas3.questionattemptid = qbc.questionattemptid
                  AND qasd3.name = '_order'
                ORDER BY qas3.sequencenumber ASC LIMIT 1
            )
            AND orderdata.name = '_order'
        WHERE qbc.attemptid = ?
        ORDER BY qbc.slot
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([$attemptid]);
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        // Resolve the shuffled order into real answer IDs
        $orderIds = $row->answer_order ? explode(',', $row->answer_order) : [];
        $selectedAnswerId = $orderIds[$row->selected_answer_index] ?? null;

        $answersById = $this->getAnswersForQuestion($row->question_id);

        $row->selected_answer_text = $selectedAnswerId !== null && isset($answersById[$selectedAnswerId])
            ? $answersById[$selectedAnswerId]['text']
            : '(no answer)';

        $row->correct_answer_text = null;
        foreach ($answersById as $a) {
            if ($a['correct']) {
                $row->correct_answer_text = $a['text'];
                break;
            }
        }
        // Every option, not just the chosen and the correct one. The model
        // cannot explain why a distractor is wrong if it is never shown the
        // distractor - and "why was my answer wrong" is the question students
        // actually ask.
        $row->all_options = array_values($answersById);
        $row->question_text = strip_tags($row->question_text);
    }

    return $rows;
}

private function getAnswersForQuestion($questionid) {
    $db = get_db();
    $stmt = $db->prepare("SELECT id, answer, fraction FROM mdl_question_answers WHERE question = ?");
    $stmt->execute([$questionid]);
    $rows = $stmt->fetchAll();

    $answers = [];
    foreach ($rows as $r) {
        $answers[$r->id] = [
            'text' => strip_tags($r->answer),
            'correct' => ((float)$r->fraction === 1.0),
        ];
    }
    return $answers;
}
    public function computeIndices($details) {
        $total = count($details);
        if ($total === 0) {
            return ['cm' => 0, 'cmi' => 0, 'gi' => 0, 'cbm_total' => 0];
        }

        $correctCount = 0;
        $highConfidenceIncorrect = 0;
        $lowConfidenceCorrect = 0;
        $cbmTotal = 0;

        foreach ($details as $d) {
            $correct = (bool) $d->is_correct;
            if ($correct) $correctCount++;

            $cbmTotal += $this->cbmScore($d->confidence, $correct);

            if (!$correct && $d->confidence == 3) {
                $highConfidenceIncorrect++;
            }
            if ($correct && $d->confidence == 1) {
                $lowConfidenceCorrect++;
            }
        }

        return [
            'cm' => round(($correctCount / $total) * 100, 1),
            'cmi' => round($highConfidenceIncorrect / $total, 3),
            'gi' => round($lowConfidenceCorrect / $total, 3),
            'cbm_total' => $cbmTotal,
        ];
    }

    /**
     * The questions the student got WRONG - the only ones the feedback page
     * shows, and the only ones the model is asked to explain.
     *
     * Deliberately not every question: a 30-item summative answered mostly
     * correctly would produce a wall of "yes, that was right" that buries the
     * five items actually worth reading, and costs latency and tokens to
     * generate. Mistakes are what formative feedback is for.
     *
     * Carries 'slot' so an explanation can be matched back to the exact
     * question it belongs to, and 'options' so the model can say why the
     * particular distractor this student picked is wrong - it cannot reason
     * about a choice it was never shown.
     */
    public function getIncorrectItems($details) {
        $items = [];
        foreach ($details as $d) {
            if ($d->is_correct) {
                continue;
            }
            $options = [];
            foreach (($d->all_options ?? []) as $o) {
                $options[] = $o['text'];
            }
            $items[] = [
                'slot' => (int) $d->slot,
                'question_text' => $d->question_text,
                'options' => $options,
                'student_answer' => $d->selected_answer_text,
                'correct_answer' => $d->correct_answer_text,
                'confidence' => (int) $d->confidence,
            ];
        }
        return $items;
    }

    public function generateFeedback($studentName, $topicName, $indices, $incorrectItems) {
        // Nothing wrong means nothing to explain, so this costs no API call at
        // all - which is why roughly half the feedback rows on this site have
        // never touched OpenAI.
        if (empty($incorrectItems)) {
            $base = [
                'summary' => "Great work, $studentName! You answered every question correctly.",
                'misconceptions' => [],
                'corrective_guidance' => [],
                'encouragement' => "Keep up the strong performance as you move to the next topic.",
                'question_notes' => [],
                'hints' => [],
            ];

            // A perfect paper alone does not make a fast learner. The high-GI
            // branch of the learning-pathway logic exists precisely because a
            // student can answer everything correctly while guessing, and that
            // student needs reinforcement, not enrichment - handing them
            // stretch material would reward the guessing the CBM design is
            // meant to discourage.
            if (($indices['gi'] ?? 1) > self::HINT_MAX_GI) {
                return $base;
            }

            // Summative only. 190 of 447 historical feedback rows are
            // all-correct with low GI, so hinting on every quiz would put an
            // OpenAI call on ~42% of attempts - a path that previously cost
            // nothing at all. At that volume the hourly rate limiter starts
            // refusing calls during a busy session and hints appear for some
            // students and not others, which is worse than not having them.
            // Enrichment also simply matters more at the end of a competency
            // than after a five-item formative.
            //
            // Keys on the quiz name because that is what this method is given.
            // All 29 quizzes follow the "Formative Quiz N" / "Summative Quiz"
            // convention; a quiz named outside it gets no hints rather than
            // unexpected spend.
            if (stripos((string) $topicName, 'summative') === false) {
                return $base;
            }

            // Hints are a bonus on top of a response that is already complete
            // and already correct, so a rate limit or a bad API round-trip must
            // degrade to the canned congratulation rather than fail the whole
            // feedback record. This path previously cost no API call at all and
            // could not fail; that property is worth keeping.
            try {
                $base['hints'] = $this->generateHints($studentName, $topicName, $indices);
            } catch (Throwable $e) {
                error_log('AccuLearn hint generation skipped: ' . $e->getMessage());
            }

            return $base;
        }

        $limiter = new RateLimiter();
        if (!$limiter->allow('openai', OPENAI_RATE_LIMIT_PER_HOUR, 3600)) {
            throw new Exception("OpenAI rate limit reached (" . OPENAI_RATE_LIMIT_PER_HOUR . "/hour) - feedback generation deferred.");
        }

        // The per-question requirement is stated as a COUNT the model can check
        // itself. Testing showed it otherwise returns explanations for only
        // some of the items it was given and silently drops the rest.
        $questioncount = count($incorrectItems);
        $systemPrompt = "You are AccuLearn's formative feedback assistant for FABM 1 bookkeeping. "
            . "You do NOT grade, do NOT change scores, and do NOT introduce new topics. You explain "
            . "mistakes the system has ALREADY identified, and give corrective guidance aligned "
            . "strictly with standard bookkeeping procedure. Be encouraging and concise.\n"
            . "You are given only the questions this student got WRONG. 'summary', 'misconceptions' "
            . "and 'corrective_guidance' summarise them as a whole. 'question_notes' explains them "
            . "one at a time:\n"
            . "- Return exactly one entry per question given, using that question's own 'slot' number. "
            . "That means exactly $questioncount entries. Never invent, renumber or skip a slot.\n"
            . "- 'why_wrong': 1-2 sentences on why the specific option THIS student chose is wrong. "
            . "Name what it would actually do (e.g. 'that reverses the entry, recording a decrease in "
            . "what customers owe'), never just 'that is incorrect'. Never leave this empty.\n"
            . "- Do NOT write a separate explanation of why the correct answer is right. The feedback "
            . "page already shows the student which option was correct. Keep 'why_wrong' focused on "
            . "the flaw in what they actually chose.\n"
            . "- When 'confidence' is 3 the student was certain and still wrong, so be especially "
            . "clear about the misunderstanding; when it is 1 they were guessing, so focus on the "
            . "reasoning they should have used.";

        $userPrompt = "Student: $studentName\n"
            . "Topic: $topicName\n"
            . "Competency Mastery: {$indices['cm']}%\n"
            . "Confidence Misconception Index: {$indices['cmi']}\n"
            . "Guessing Index: {$indices['gi']}\n"
            . "Confidence is the student's self-rating: 1 = guess, 2 = fairly sure, 3 = certain.\n\n"
            . "The $questioncount questions this student answered INCORRECTLY - return exactly "
            . "$questioncount question_notes, one for every slot listed here:\n"
            . json_encode($incorrectItems, JSON_PRETTY_PRINT);

        $schema = [
            "type" => "object",
            "properties" => [
                "summary" => ["type" => "string"],
                "misconceptions" => ["type" => "array", "items" => ["type" => "string"]],
                "corrective_guidance" => ["type" => "array", "items" => ["type" => "string"]],
                "encouragement" => ["type" => "string"],
                // One note per missed question, explaining the student's own
                // mistake. A companion "why the correct answer is right" field
                // was tried and removed: the page already marks which option
                // was correct, so the prose only made each card longer without
                // adding anything about the student's actual error.
                //
                // The field is named rather than left as a generic
                // "explanation" on purpose - asked for a free-text
                // explanation, the model drifted into describing the correct
                // answer and skipped the student's own choice entirely.
                "question_notes" => [
                    "type" => "array",
                    "items" => [
                        "type" => "object",
                        "properties" => [
                            "slot" => ["type" => "integer"],
                            "why_wrong" => ["type" => "string"],
                        ],
                        "required" => ["slot", "why_wrong"],
                        "additionalProperties" => false,
                    ],
                ],
            ],
            // strict mode requires every declared property to be listed here.
            "required" => ["summary", "misconceptions", "corrective_guidance", "encouragement",
                "question_notes"],
            "additionalProperties" => false
        ];

        return $this->callModel($systemPrompt, $userPrompt, $schema, 'feedback_response');
    }

    /**
     * Fewest guessed-but-correct answers before a perfect paper stops counting
     * as "fast learner" evidence.
     *
     * GI is lowConfidenceCorrect/total, so 0.20 means at most one item in five
     * was a lucky guess. Mirrors CMI_MISCONCEPTION_THRESHOLD deliberately: both
     * ask "is this score actually backed by knowledge?", from opposite ends.
     */
    const HINT_MAX_GI = 0.20;

    /**
     * Enrichment hints for a student who has already mastered the topic.
     *
     * Process Rule 6 lists "provide hints for fast learners" among what the AI
     * is allowed to do, and nothing in the system had ever implemented it -
     * a perfect paper short-circuited to a canned congratulation without so
     * much as an API call. This is the LP=4 / Advancement branch of that rule.
     *
     * Scoped tightly on purpose: forward-looking pointers that stretch a
     * student who is ready, NOT new curriculum content. The system prompt
     * forbids introducing material outside FABM 1 for the same reason the
     * corrective path does - the AI supplements instruction here, it does not
     * replace the teacher's sequencing.
     */
    public function generateHints($studentName, $topicName, $indices) {
        $limiter = new RateLimiter();
        if (!$limiter->allow('openai', OPENAI_RATE_LIMIT_PER_HOUR, 3600)) {
            throw new Exception("OpenAI rate limit reached - hint generation deferred.");
        }

        $systemPrompt = "You are AccuLearn's enrichment assistant for FABM 1 bookkeeping. "
            . "This student answered every question correctly with genuine confidence, so they "
            . "are ready to be stretched. Give 2-3 short forward-looking hints that deepen the "
            . "topic they just mastered: a subtler case to think about, a common trap that only "
            . "appears in harder problems, or how this topic connects to the next one in the "
            . "accounting cycle.\n"
            . "Rules: stay strictly inside standard FABM 1 bookkeeping. Do NOT introduce topics "
            . "outside the subject, do NOT re-teach what they already got right, do NOT grade or "
            . "mention scores. Each hint is 1-2 sentences, concrete, and phrased as something to "
            . "try or notice - not praise.";

        $userPrompt = "Student: $studentName\n"
            . "Topic just mastered: $topicName\n"
            . "Competency Mastery: {$indices['cm']}%\n"
            . "Guessing Index: {$indices['gi']} (low, so this mastery is genuine)\n";

        $schema = [
            "type" => "object",
            "properties" => [
                "hints" => ["type" => "array", "items" => ["type" => "string"]],
            ],
            "required" => ["hints"],
            "additionalProperties" => false,
        ];

        $result = $this->callModel($systemPrompt, $userPrompt, $schema, 'hint_response');
        return $result['hints'] ?? [];
    }

    /**
     * One structured-output call to the model. Extracted so the corrective and
     * enrichment paths cannot drift apart on timeouts, TLS verification or
     * response parsing - the curl options here are load-bearing (see below).
     */
    private function callModel($systemPrompt, $userPrompt, $schema, $schemaName) {
        $payload = [
            "model" => "gpt-4o-mini",
            "input" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => $userPrompt],
            ],
            "text" => [
                "format" => [
                    "type" => "json_schema",
                    "name" => $schemaName,
                    "schema" => $schema,
                    "strict" => true
                ]
            ]
        ];

        $ch = curl_init('https://api.openai.com/v1/responses');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Without these, curl's default timeout is UNLIMITED. This call runs
        // inside a Moodle ad-hoc task, so a single hung OpenAI connection
        // would block the cron worker indefinitely and stall the whole task
        // queue - meaning no student gets feedback until someone notices and
        // restarts cron. Bounded waits turn that into one failed task that
        // Moodle simply retries.
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        // Raised from 60s when per-question explanations were added: a 30-item
        // summative now asks for 30 short explanations in one response, which
        // legitimately takes longer to generate than the old summary-only call.
        // Still bounded, for the reason above.
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
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

        return json_decode($outputText, true);
    }
}
