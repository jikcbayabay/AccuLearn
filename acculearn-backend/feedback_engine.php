<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/moodle_client.php';
require_once __DIR__ . '/db.php';

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

    public function getIncorrectItems($details) {
        $items = [];
        foreach ($details as $d) {
            if (!$d->is_correct) {
                $items[] = [
                    'question_text' => $d->question_text,
                    'student_answer' => $d->selected_answer_text,
                    'correct_answer' => $d->correct_answer_text,
                    'confidence' => (int) $d->confidence,
                ];
            }
        }
        return $items;
    }

    public function generateFeedback($studentName, $topicName, $indices, $incorrectItems) {
        if (empty($incorrectItems)) {
            return [
                'summary' => "Great work, $studentName! You answered every question correctly.",
                'misconceptions' => [],
                'corrective_guidance' => [],
                'encouragement' => "Keep up the strong performance as you move to the next topic.",
            ];
        }

        $systemPrompt = "You are AccuLearn's formative feedback assistant for FABM 1 bookkeeping. "
            . "You do NOT grade, do NOT change scores, and do NOT introduce new topics. "
            . "You only explain errors already identified by the system and provide corrective guidance "
            . "aligned strictly with standard bookkeeping procedure. Be encouraging and concise.";

        $userPrompt = "Student: $studentName\n"
            . "Topic: $topicName\n"
            . "Competency Mastery: {$indices['cm']}%\n"
            . "Confidence Misconception Index: {$indices['cmi']}\n"
            . "Guessing Index: {$indices['gi']}\n"
            . "Incorrect items (with question text, student's answer, and the correct answer):\n"
            . json_encode($incorrectItems, JSON_PRETTY_PRINT);

        $schema = [
            "type" => "object",
            "properties" => [
                "summary" => ["type" => "string"],
                "misconceptions" => ["type" => "array", "items" => ["type" => "string"]],
                "corrective_guidance" => ["type" => "array", "items" => ["type" => "string"]],
                "encouragement" => ["type" => "string"]
            ],
            "required" => ["summary", "misconceptions", "corrective_guidance", "encouragement"],
            "additionalProperties" => false
        ];

        $payload = [
            "model" => "gpt-4o-mini",
            "input" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => $userPrompt],
            ],
            "text" => [
                "format" => [
                    "type" => "json_schema",
                    "name" => "feedback_response",
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
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

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
