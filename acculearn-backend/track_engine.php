<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mastery_engine.php';

class TrackEngine {

    private $mastery;

    public function __construct() {
        $this->mastery = new MasteryEngine();
    }

    /**
     * Generates (or regenerates) a student's personalized learning track.
     * $source = 'pretest' for the initial paper pre-test based track,
     * $source = 'quiz' (default) for ongoing progress updates from real Moodle quiz data.
     */
    public function generateTrack($userid, $studentName, $source = 'quiz') {
        $fullTrack = $this->mastery->computeFullTrack($userid, $source);

        $narrative = $this->generateNarrative($studentName, $fullTrack, $source);

        $record = [
            'competencies' => $fullTrack,
            'narrative' => $narrative,
            'source' => $source,
        ];

        $db = get_db();
        $stmt = $db->prepare("
            INSERT INTO acculearn_learning_tracks (userid, track_json, generated_at)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE track_json = VALUES(track_json), generated_at = VALUES(generated_at)
        ");
        $stmt->execute([$userid, json_encode($record), time()]);

        return $record;
    }

    private function generateNarrative($studentName, $fullTrack, $source) {
        $context = ($source === 'pretest')
            ? "This is the student's INITIAL learning track, based on a paper pre-test administered before instruction began."
            : "This is the student's UPDATED learning track, based on their real quiz performance so far in the course.";

        $systemPrompt = "You are AccuLearn's track summary assistant for FABM 1 bookkeeping. "
            . "You are given a student's mastery status and assigned learning pathway for each of 7 competencies, "
            . "already determined by the system's rules. You do NOT change, question, or reinterpret these pathway "
            . "assignments. Your only job is to explain the track to the student in an encouraging, plain-language way, "
            . "and to give a short overview of what they should focus on next. $context";

        $trackSummary = [];
        foreach ($fullTrack as $t) {
            $trackSummary[] = [
                'competency' => $t['competency_name'],
                'mastery' => $t['mastery'],
                'pathway' => $t['lp_label'],
            ];
        }

        $userPrompt = "Student: $studentName\n"
            . "Learning track (already determined by the system, do not change):\n"
            . json_encode($trackSummary, JSON_PRETTY_PRINT);

        $schema = [
            "type" => "object",
            "properties" => [
                "overview" => ["type" => "string"],
                "next_focus" => ["type" => "string"],
                "encouragement" => ["type" => "string"]
            ],
            "required" => ["overview", "next_focus", "encouragement"],
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
                    "name" => "track_narrative",
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

    public function getStoredTrack($userid) {
        $db = get_db();
        $stmt = $db->prepare("SELECT * FROM acculearn_learning_tracks WHERE userid = ?");
        $stmt->execute([$userid]);
        return $stmt->fetch();
    }
}
