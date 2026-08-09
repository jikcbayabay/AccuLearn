<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mastery_engine.php';
require_once __DIR__ . '/rate_limiter.php';

class TrackEngine {

    private $mastery;

    public function __construct() {
        $this->mastery = new MasteryEngine();
    }

    /**
     * Generates (or regenerates) a student's personalized learning track.
     * $source = 'pretest' for the initial paper pre-test based track,
     * $source = 'quiz' (default) for ongoing progress updates from real Moodle quiz data.
     *
     * Skips regeneration (and the OpenAI call) when the underlying mastery/
     * pathway evidence hasn't actually changed since the last stored track -
     * e.g. a quiz resubmission that doesn't move any competency's mastery or
     * LP. Avoids burning an AI call and rewriting a narrative that would
     * come out materially the same.
     */
    public function generateTrack($userid, $studentName, $source = 'quiz') {
        $fullTrack = $this->mastery->computeFullTrack($userid, $source);
        $signature = $this->evidenceSignature($fullTrack);

        $existing = $this->getStoredTrack($userid);
        if ($existing) {
            $previous = json_decode($existing->track_json, true);
            $previousSignature = $this->evidenceSignature($previous['competencies'] ?? []);
            if ($previousSignature === $signature) {
                return $previous;
            }
        }

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

    /**
     * Reduces a full track to just the fields that should trigger a
     * regeneration if they change (mastery classification and assigned
     * pathway per competency) - CM/CMI/GI can shift slightly between
     * attempts without crossing a mastery/LP boundary, and that shouldn't
     * be enough to re-narrate the track.
     */
    private function evidenceSignature($fullTrack) {
        $sig = [];
        foreach ($fullTrack as $t) {
            // priority_group is included so a change in the ADAPTIVE ORDER
            // regenerates the narrative even when no mastery/LP boundary was
            // crossed - otherwise the "what to focus on next" paragraph could
            // keep naming a competency that is no longer the recommended one
            // (e.g. a competency crossing the CMI misconception threshold).
            // It also means tracks stored before adaptive ordering existed
            // have no priority_group, so their signature differs and they
            // regenerate once, automatically, on the next trigger.
            $sig[$t['competency_id']] = [
                $t['mastery'] ?? null,
                $t['lp'] ?? null,
                $t['priority_group'] ?? null,
            ];
        }
        ksort($sig);
        return $sig;
    }

    private function generateNarrative($studentName, $fullTrack, $source) {
        $limiter = new RateLimiter();
        if (!$limiter->allow('openai', OPENAI_RATE_LIMIT_PER_HOUR, 3600)) {
            throw new Exception("OpenAI rate limit reached (" . OPENAI_RATE_LIMIT_PER_HOUR . "/hour) - track generation deferred.");
        }

        $context = ($source === 'pretest')
            ? "This is the student's INITIAL learning track, based on a paper pre-test administered before instruction began."
            : "This is the student's UPDATED learning track, based on their real quiz performance so far in the course.";

        $systemPrompt = "You are AccuLearn's track summary assistant for FABM 1 bookkeeping. "
            . "You are given a student's mastery status and assigned learning pathway for each of 7 competencies, "
            . "already determined by the system's rules. You do NOT change, question, or reinterpret these pathway "
            . "assignments. Your only job is to explain the track to the student in an encouraging, plain-language way, "
            . "and to give a short overview of what they should focus on next. $context\n"
            . "The list is given to you in the system's ADAPTIVE ORDER, not in curriculum order. Each entry has a "
            . "'priority' field: 'next' is the single competency the student should work on now; 'revisit' means they "
            . "passed it but answered confidently and incorrectly often enough to suggest a misconception; 'upcoming' "
            . "is still ahead of them; 'maintain' is already mastered and only optional review. Your 'next_focus' MUST "
            . "be about the competency marked 'next' (if there is none, every competency is mastered - say so). Never "
            . "tell the student a competency has been skipped or removed: nothing is skipped, the order is simply "
            . "personalised to them.";

        $trackSummary = [];
        foreach ($fullTrack as $t) {
            $trackSummary[] = [
                'competency' => $t['competency_name'],
                'mastery' => $t['mastery'],
                'pathway' => $t['lp_label'],
                'priority' => $t['priority_group'] ?? 'upcoming',
            ];
        }

        $userPrompt = "Student: $studentName\n"
            . "Learning track in adaptive order (already determined by the system, do not change or reorder):\n"
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
        // Bounded waits - see the matching note in feedback_engine.php. An
        // unbounded call here would hang the same cron worker.
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
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

    public function getStoredTrack($userid) {
        $db = get_db();
        $stmt = $db->prepare("SELECT * FROM acculearn_learning_tracks WHERE userid = ?");
        $stmt->execute([$userid]);
        return $stmt->fetch();
    }
}
