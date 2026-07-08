<?php
require_once(__DIR__ . '/../../config.php');

$attemptid = required_param('attempt', PARAM_INT);

require_login();

global $DB, $USER, $OUTPUT, $PAGE;

// Verify this attempt belongs to the logged-in user (or they're a teacher who can grade it).
$attempt = $DB->get_record('quiz_attempts', ['id' => $attemptid], '*', MUST_EXIST);
$quiz = $DB->get_record('quiz', ['id' => $attempt->quiz], '*', MUST_EXIST);
$course = $DB->get_record('course', ['id' => $quiz->course], '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('quiz', $quiz->id, $quiz->course, false, MUST_EXIST);
$context = context_module::instance($cm->id);

$isowner = ($attempt->userid == $USER->id);
$canreview = has_capability('mod/quiz:viewreports', $context);

if (!$isowner && !$canreview) {
    print_error('notyours', 'local_acculearnfeedback');
}

$PAGE->set_url('/local/acculearnfeedback/view.php', ['attempt' => $attemptid]);
$PAGE->set_cm($cm, $course);
$PAGE->set_context($context);
$PAGE->set_title(get_string('feedbacktitle', 'local_acculearnfeedback'));
$PAGE->set_heading(get_string('feedbacktitle', 'local_acculearnfeedback'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('feedbacktitle', 'local_acculearnfeedback'));

$feedbackrow = $DB->get_record_sql(
    "SELECT * FROM acculearn_feedback WHERE attemptid = ?",
    [$attemptid]
);

if (!$feedbackrow) {
    echo html_writer::div(get_string('notfound', 'local_acculearnfeedback'), 'alert alert-info');
    echo $OUTPUT->footer();
    exit;
}

$feedback = json_decode($feedbackrow->feedback_json, true);

echo html_writer::start_div('acculearn-feedback-card', ['style' => 'max-width: 800px; margin: auto;']);

// Summary
echo html_writer::tag('div',
    html_writer::tag('h4', 'Summary') . html_writer::tag('p', s($feedback['summary'])),
    ['style' => 'background:#f0f7ff; border-left:4px solid #0066cc; padding:16px; border-radius:4px; margin-bottom:16px;']
);

// Indices
echo html_writer::start_div('', ['style' => 'display:flex; gap:16px; margin-bottom:16px;']);
foreach ([
    'Mastery' => $feedbackrow->cm . '%',
    'Confidence Misconception Index' => $feedbackrow->cmi,
    'Guessing Index' => $feedbackrow->gi,
] as $label => $value) {
    echo html_writer::div(
        html_writer::tag('strong', $value) . html_writer::tag('div', $label, ['style' => 'font-size:0.85em; color:#555;']),
        '', ['style' => 'flex:1; background:#f5f5f5; padding:12px; border-radius:4px; text-align:center;']
    );
}
echo html_writer::end_div();

// Misconceptions
if (!empty($feedback['misconceptions'])) {
    echo html_writer::tag('h4', 'What went wrong');
    echo html_writer::start_tag('ul');
    foreach ($feedback['misconceptions'] as $m) {
        echo html_writer::tag('li', s($m));
    }
    echo html_writer::end_tag('ul');
}

// Corrective guidance
if (!empty($feedback['corrective_guidance'])) {
    echo html_writer::tag('h4', 'How to improve');
    echo html_writer::start_tag('ul');
    foreach ($feedback['corrective_guidance'] as $g) {
        echo html_writer::tag('li', s($g));
    }
    echo html_writer::end_tag('ul');
}

// Encouragement
echo html_writer::tag('p', s($feedback['encouragement']), ['style' => 'font-style:italic; color:#0066cc; margin-top:16px;']);

echo html_writer::end_div();

echo $OUTPUT->footer();
