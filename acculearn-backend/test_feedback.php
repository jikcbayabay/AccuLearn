<?php
require_once __DIR__ . '/feedback_engine.php';
require_once __DIR__ . '/db.php';

$attemptid = 2; // update to your real attempt id if needed

$db = get_db();
$stmt = $db->prepare("
    SELECT u.firstname, u.lastname, q.name AS quiz_name
    FROM mdl_quiz_attempts qa
    JOIN mdl_user u ON u.id = qa.userid
    JOIN mdl_quiz q ON q.id = qa.quiz
    WHERE qa.id = ?
");
$stmt->execute([$attemptid]);
$attemptInfo = $stmt->fetch();

if (!$attemptInfo) {
    die("Attempt $attemptid not found.\n");
}

$studentName = $attemptInfo->firstname . ' ' . $attemptInfo->lastname;
$topicName = $attemptInfo->quiz_name;

echo "Student: $studentName\n";
echo "Quiz: $topicName\n\n";

$engine = new FeedbackEngine();

$details = $engine->getAttemptDetails($attemptid);
echo "Retrieved " . count($details) . " question responses:\n";
foreach ($details as $d) {
    $status = $d->is_correct ? 'CORRECT' : 'INCORRECT';
    echo "  Slot {$d->slot}: {$d->question_name} | confidence={$d->confidence} | $status | answer={$d->selected_answer_text}\n";
}

$indices = $engine->computeIndices($details);
echo "\nComputed indices:\n";
print_r($indices);

$incorrectItems = $engine->getIncorrectItems($details);
echo "\nIncorrect items for AI:\n";
print_r($incorrectItems);

echo "\n--- Generating AI feedback ---\n";
try {
    $feedback = $engine->generateFeedback($studentName, $topicName, $indices, $incorrectItems);
    echo "AI FEEDBACK SUCCESS:\n";
    print_r($feedback);
} catch (Exception $e) {
    echo "AI FEEDBACK FAILED: " . $e->getMessage() . "\n";
}
