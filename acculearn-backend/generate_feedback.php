<?php
require_once __DIR__ . '/feedback_engine.php';
require_once __DIR__ . '/db.php';

/**
 * Generates and stores AI feedback for a completed quiz attempt.
 * Called automatically after quiz submission (see Moodle observer).
 */
function acculearn_generate_feedback_for_attempt($attemptid) {
    $db = get_db();

    // Skip if feedback already generated for this attempt
    $check = $db->prepare("SELECT id FROM acculearn_feedback WHERE attemptid = ?");
    $check->execute([$attemptid]);
    if ($check->fetch()) {
        return; // already done
    }

    $stmt = $db->prepare("
        SELECT u.id AS userid, u.firstname, u.lastname, q.name AS quiz_name
        FROM mdl_quiz_attempts qa
        JOIN mdl_user u ON u.id = qa.userid
        JOIN mdl_quiz q ON q.id = qa.quiz
        WHERE qa.id = ?
    ");
    $stmt->execute([$attemptid]);
    $info = $stmt->fetch();

    if (!$info) {
        error_log("AccuLearn: attempt $attemptid not found, skipping feedback generation.");
        return;
    }

    $studentName = $info->firstname . ' ' . $info->lastname;

    $engine = new FeedbackEngine();
    $details = $engine->getAttemptDetails($attemptid);

    if (empty($details)) {
        return; // no confidence data for this attempt (e.g. not using our behaviour)
    }

    $indices = $engine->computeIndices($details);
    $incorrectItems = $engine->getIncorrectItems($details);

    try {
        $feedback = $engine->generateFeedback($studentName, $info->quiz_name, $indices, $incorrectItems);
    } catch (Exception $e) {
        error_log("AccuLearn feedback generation failed for attempt $attemptid: " . $e->getMessage());
        return;
    }

    $insert = $db->prepare("
        INSERT INTO acculearn_feedback
            (attemptid, userid, quizname, cm, cmi, gi, cbm_total, feedback_json, timecreated)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insert->execute([
        $attemptid,
        $info->userid,
        $info->quiz_name,
        $indices['cm'],
        $indices['cmi'],
        $indices['gi'],
        $indices['cbm_total'],
        json_encode($feedback),
        time(),
    ]);
}

// Allow direct CLI invocation for testing: php generate_feedback.php 2
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    acculearn_generate_feedback_for_attempt((int)$argv[1]);
    echo "Done.\n";
}
