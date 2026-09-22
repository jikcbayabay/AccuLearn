<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mastery_engine.php';
require_once __DIR__ . '/decision_engine.php';
require_once __DIR__ . '/rate_limiter.php';

class TrackEngine {

    private $mastery;

    /** Whether the most recent generateTrack() actually wrote a new track. */
    private $laststored = false;

    public function __construct() {
        $this->mastery = new MasteryEngine();
    }

    /**
     * True when the last generateTrack() call really rewrote the stored track,
     * false when it returned the existing one unchanged.
     *
     * Callers log "learning track recalculated" for the student; that log is a
     * research instrument, so it must not record a recalculation that was
     * skipped as identical.
     */
    public function lastRunStoredTrack() {
        return $this->laststored;
    }

    /**
     * Generates (or regenerates) a student's personalized learning track.
     * $source = 'pretest' for the initial paper pre-test based track,
     * $source = 'quiz' (default) for ongoing progress updates from real Moodle quiz data.
     *
     * Under MasteryEngine::DECISION_MODE 'ai' the track's decisions - mastery
     * tier, pathway, adaptive order, priority - are made by the model
     * (DecisionEngine) from the student's evidence, in the same call that
     * writes the narrative. The stored track records who decided it
     * ('decision' => by/model/decided_at/evidence_signature) because the
     * quiz gate reads the stored decision back (MasteryEngine::currentPathway()).
     *
     * Skips regeneration (and the model call) when the EVIDENCE hasn't
     * changed since the last AI-decided track - a resubmission that moves no
     * index and passes no new quiz. A track whose decision came from the
     * formula fallback is always retried, so a student is not left on the
     * fallback any longer than the outage lasts.
     *
     * Fallback: when the model is unavailable or answers unusably, the
     * LP_MODE formula decides instead and the narrative is written by the
     * older narrative-only call (or, failing that too, the previous
     * narrative is kept). The refreshed FACTS are always stored so the
     * per-quiz checklist never lags because prose could not be written.
     *
     * @param bool $force re-decide even when the stored track already rests
     *   on identical evidence (verify_ai_decides.php uses this; nothing in
     *   the normal flow does).
     */
    public function generateTrack($userid, $studentName, $source = 'quiz', $force = false) {
        $this->laststored = false;

        $evidence = $this->mastery->computeFullEvidence($userid, $source);
        $signature = MasteryEngine::evidenceSignature($evidence);

        $previous = null;
        $previousNarrative = null;
        $existing = $this->getStoredTrack($userid);
        if ($existing) {
            $previous = json_decode($existing->track_json, true);
            if (is_array($previous)) {
                $previousNarrative = $previous['narrative'] ?? null;
                $previousby = $previous['decision']['by'] ?? null;
                $previoussig = $previous['decision']['evidence_signature'] ?? null;
                if ($force) {
                    // fall through to a fresh decision
                } else if (MasteryEngine::DECISION_MODE === 'ai') {
                    if ($previousby === 'ai' && $previoussig === $signature) {
                        return $previous;
                    }
                } else if ($previoussig === $signature && $previousby !== null) {
                    return $previous;
                }
            }
        }

        $decision = [
            'by' => 'rule',
            'model' => null,
            'decided_at' => time(),
            'evidence_signature' => $signature,
            'fallback_reason' => null,
        ];
        $competencies = null;
        $narrative = null;

        if (MasteryEngine::DECISION_MODE === 'ai') {
            try {
                $engine = new DecisionEngine();
                $decided = $engine->decide($evidence, $studentName, $source);
                $competencies = $decided['competencies'];
                $narrative = $decided['narrative'];
                $decision['by'] = 'ai';
                $decision['model'] = DecisionEngine::MODEL;
            } catch (Exception $e) {
                // Logged, then decided by the formula below. The student gets
                // a rule-decided track now and an AI-decided one on their
                // next trigger (see the retry note in the docblock).
                error_log('AccuLearn: AI decision failed for user ' . $userid
                    . ', falling back to the rule: ' . $e->getMessage());
                $decision['fallback_reason'] = $e->getMessage();
            }
        }

        if ($competencies === null) {
            $competencies = $this->mastery->computeFullTrack($userid, $source);
            try {
                $narrative = $this->generateNarrative($studentName, $competencies, $source);
            } catch (Exception $e) {
                // A failed narrative call must not cost the student their
                // refreshed FACTS. The prose is the only part that needs the
                // model, so fall back to the last narrative and store the
                // recomputed competencies regardless. With no previous
                // narrative there is nothing to store, so the error propagates.
                if (!is_array($previousNarrative) || empty($previousNarrative)) {
                    throw $e;
                }
                error_log('AccuLearn: keeping previous track narrative for user ' . $userid
                    . ' after narrative generation failed: ' . $e->getMessage());
                $narrative = $previousNarrative;
            }
        }

        $record = [
            'competencies' => $competencies,
            'narrative' => $narrative,
            'source' => $source,
            'decision' => $decision,
        ];

        $db = get_db();
        $stmt = $db->prepare("
            INSERT INTO acculearn_learning_tracks (userid, track_json, generated_at)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE track_json = VALUES(track_json), generated_at = VALUES(generated_at)
        ");
        $stmt->execute([$userid, json_encode($record), time()]);
        $this->laststored = true;

        return $record;
    }

    /**
     * Whether a stored track's decision no longer matches the student's
     * current evidence - i.e. something changed outside a quiz submission
     * (a regrade, a granted extra attempt that was then used) and the
     * decision should be made again. Only meaningful for AI-decided tracks;
     * rule-era tracks are never reported stale here, because regenerating
     * them would overwrite the study's stored tracks with a different rule.
     */
    public function storedTrackIsStale($userid) {
        $track = $this->mastery->storedTrack($userid);
        if ($track === null || (($track['decision']['by'] ?? null) !== 'ai')) {
            return false;
        }
        $evidence = $this->mastery->computeFullEvidence($userid, $track['source'] ?? 'quiz');
        return MasteryEngine::evidenceSignature($evidence) !== ($track['decision']['evidence_signature'] ?? null);
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
            . "already determined by the system's fallback rule. You do NOT change, question, or reinterpret these pathway "
            . "assignments. Your only job is to explain the track to the student in an encouraging, plain-language way, "
            . "and to give a short overview of what they should focus on next. $context\n"
            . "The list is given to you in the system's ADAPTIVE ORDER, not in curriculum order. Each entry has a "
            . "'priority' field: 'next' is the single competency the student should work on now; 'revisit' means they "
            . "passed it but answered confidently and incorrectly often enough to suggest a misconception; 'upcoming' "
            . "is still ahead of them; 'maintain' is already mastered and only optional review. Your 'next_focus' MUST "
            . "be about the competency marked 'next' (if there is none, every competency is mastered - say so). Never "
            . "tell the student a competency has been skipped or removed: nothing is skipped, the order is simply "
            . "personalised to them.\n"
            . "The student is identified to you only as the literal token "
            . FeedbackEngine::STUDENT_PLACEHOLDER . ". Address them using that exact "
            . "token wherever you would use a name, and never invent or guess a real name.";

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
