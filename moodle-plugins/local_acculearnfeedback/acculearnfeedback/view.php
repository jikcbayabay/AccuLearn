<?php
require_once(__DIR__ . '/../../config.php');

require_once(__DIR__ . '/lib.php');

// require_login() must come FIRST. With required_param() ahead of it, an
// anonymous request with no 'attempt' parameter got Moodle's "A required
// parameter (attempt) was missing" error page (HTTP 404) instead of being
// redirected to the login page - the only AccuLearn endpoint that answered
// an unauthenticated caller with an application error rather than a login
// redirect, which confirms to an unauthenticated prober that the endpoint
// exists and what parameter it expects. No data was exposed (the DB lookups
// below all sit after this point), but authentication should gate the
// endpoint before any request parsing happens.
require_login();

$attemptid = required_param('attempt', PARAM_INT);

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
    // print_error() was REMOVED in Moodle 5.0 (deprecated, then deleted - see
    // lib/upgrade.txt: "The function print_error has been deprecated. Kindly
    // use moodle_exception."). Calling it here meant the access-denied path
    // died with a "call to undefined function" fatal instead of a clean
    // permission error - which, with developer debugging enabled, renders a
    // stack trace exposing server file paths to the blocked user.
    throw new moodle_exception('notyours', 'local_acculearnfeedback');
}

$PAGE->set_url('/local/acculearnfeedback/view.php', ['attempt' => $attemptid]);
$PAGE->set_cm($cm, $course);
$PAGE->set_context($context);
$PAGE->set_title(get_string('feedbacktitle', 'local_acculearnfeedback'));
$PAGE->set_heading(get_string('feedbacktitle', 'local_acculearnfeedback'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('feedbacktitle', 'local_acculearnfeedback'));

local_acculearnfeedback_log_usage($USER->id, 'view_feedback', 'attempt', $attemptid,
    'AI feedback for: ' . $quiz->name . ' (attempt ' . $attempt->attempt . ')', $course->id);

$feedbackrow = $DB->get_record_sql(
    "SELECT * FROM acculearn_feedback WHERE attemptid = ?",
    [$attemptid]
);

if (!$feedbackrow) {
    echo html_writer::start_div('al-page', ['style' => 'max-width: 800px;']);
    echo html_writer::div(
        html_writer::div('<span class="al-spinner"></span>', 'al-empty__icon', ['style' => 'background:var(--al-blue-50); color:var(--al-blue);']) .
        html_writer::div('Feedback is still generating', 'al-empty__title') .
        html_writer::div(get_string('notfound', 'local_acculearnfeedback'), 'al-empty__sub'),
        'al-card al-empty'
    );
    echo html_writer::end_div();
    echo $OUTPUT->footer();
    exit;
}

$feedback = json_decode($feedbackrow->feedback_json, true);

echo html_writer::start_div('al-page', ['style' => 'max-width: 800px;']);

// Summary
echo html_writer::tag('div',
    html_writer::tag('h4', '<i class="fa fa-compass" aria-hidden="true"></i>Summary', ['style' => 'margin-top:0;']) . html_writer::tag('p', s($feedback['summary'])),
    ['class' => 'al-card al-card--accent', 'style' => 'margin-bottom:16px;']
);

// Indices
echo html_writer::start_div('al-stat-row');
foreach ([
    'Mastery' => $feedbackrow->cm . '%',
    'Confidence Misconception Index' => $feedbackrow->cmi,
    'Guessing Index' => $feedbackrow->gi,
] as $label => $value) {
    echo html_writer::div(
        html_writer::tag('strong', $value, ['class' => 'tnum']) . html_writer::tag('span', $label),
        'al-stat'
    );
}
echo html_writer::end_div();

// Misconceptions
if (!empty($feedback['misconceptions'])) {
    echo html_writer::tag('h4', '<i class="fa fa-triangle-exclamation" aria-hidden="true"></i>What went wrong');
    echo html_writer::start_tag('ul');
    foreach ($feedback['misconceptions'] as $m) {
        echo html_writer::tag('li', s($m));
    }
    echo html_writer::end_tag('ul');
}

// Corrective guidance
if (!empty($feedback['corrective_guidance'])) {
    echo html_writer::tag('h4', '<i class="fa fa-bullseye" aria-hidden="true"></i>How to improve');
    echo html_writer::start_tag('ul');
    foreach ($feedback['corrective_guidance'] as $g) {
        echo html_writer::tag('li', s($g));
    }
    echo html_writer::end_tag('ul');
}

// Encouragement
echo html_writer::tag('p', s($feedback['encouragement']), ['style' => 'font-style:italic; color:var(--al-blue); margin-top:16px;']);

echo html_writer::end_div();

echo $OUTPUT->footer();
