<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

global $DB, $OUTPUT, $PAGE, $SESSION;

$context = context_system::instance();
require_capability('local/acculearnfeedback:managesequence', $context);

// Must be triggered by a real POST with a valid sesskey, not a bare GET -
// this makes a live OpenAI API call and writes learning-track data for an
// arbitrary student, so an unprotected GET would let any page that gets an
// already-logged-in admin's browser to load it (e.g. via an <img> tag)
// silently trigger it - a genuine CSRF risk, not just theoretical
// hardening. require_sesskey() throws if the check fails.
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    redirect(new moodle_url('/local/acculearnfeedback/pretest_entry.php'),
        'This action must be triggered from the button on the pre-test entry page.',
        null, \core\output\notification::NOTIFY_ERROR);
}
require_sesskey();

// The target student comes from the session (set on pretest_entry.php when
// an admin selects a student there), not a URL/POST parameter - so this
// page's own URL never needs to carry a student's database ID either.
$userid = $SESSION->acculearn_pretest_userid ?? 0;
if (!$userid) {
    redirect(new moodle_url('/local/acculearnfeedback/pretest_entry.php'),
        'No student is currently selected on the pre-test entry page.',
        null, \core\output\notification::NOTIFY_ERROR);
}

$PAGE->set_url('/local/acculearnfeedback/generate_pretest_track.php');
$PAGE->set_context($context);
$PAGE->set_title('Generate Initial Track');
$PAGE->set_heading('Generate Initial Track');

echo $OUTPUT->header();

$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
$studentName = $user->firstname . ' ' . $user->lastname;

require_once('/var/www/html/acculearn-backend/track_engine.php');

echo html_writer::start_div('al-page');

echo html_writer::tag('p', "Generating initial learning track for $studentName based on pre-test data...",
    ['style' => 'color:var(--al-ink-500);']);

try {
    $engine = new TrackEngine();
    $result = $engine->generateTrack($userid, $studentName, 'pretest');

    // Pre-test data bypasses the quiz-submission observer entirely, so this
    // is the only place that needs to sync unlock-group membership for it.
    local_acculearnfeedback_sync_unlock_groups_for_user($userid);

    local_acculearnfeedback_log_usage($userid, 'track_generated', 'track', null,
        'Initial learning track generated from pre-test results');

    echo html_writer::div('Track generated successfully.', 'al-card al-card--flag-approved');

    echo html_writer::start_div('al-card al-card--accent', ['style' => 'margin-bottom:24px;']);
    echo html_writer::tag('h4', '<i class="fa fa-compass" aria-hidden="true"></i>Narrative', ['style' => 'margin-top:0;']);
    echo html_writer::tag('p', s($result['narrative']['overview']));
    echo html_writer::tag('p', html_writer::tag('strong', 'Next focus: ') . s($result['narrative']['next_focus']));
    echo html_writer::tag('p', s($result['narrative']['encouragement']),
        ['style' => 'font-style:italic; color:var(--al-blue);']);
    echo html_writer::end_div();

    echo html_writer::tag('h4', '<i class="fa fa-chart-line" aria-hidden="true"></i>Competency Breakdown');
    echo html_writer::start_div('al-table-wrap');
    echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%;']);
    echo html_writer::tag('tr', html_writer::tag('th', 'Competency') . html_writer::tag('th', 'CM') . html_writer::tag('th', 'Mastery') . html_writer::tag('th', 'Pathway'));
    foreach ($result['competencies'] as $c) {
        echo html_writer::tag('tr',
            html_writer::tag('td', s($c['competency_name'])) .
            html_writer::tag('td',
                html_writer::tag('span', $c['cm'] ?? 'N/A', ['class' => 'tnum']) . '%' .
                html_writer::div(local_acculearnfeedback_progress_bar($c['cm'] ?? null, $c['mastery']), '', ['style' => 'min-width:90px;'])
            ) .
            html_writer::tag('td', s($c['mastery'])) .
            html_writer::tag('td', local_acculearnfeedback_lp_badge($c['lp'] ?? null, $c['lp_label']))
        );
    }
    echo html_writer::end_tag('table');
    echo html_writer::end_div();

} catch (Exception $e) {
    // The raw message here can be an upstream OpenAI error containing the
    // verbatim API response body, so it goes to the server log for the
    // admin to inspect rather than onto the page. Echoing third-party
    // error payloads straight into the browser is how internal request
    // details end up in screenshots and bug reports.
    error_log('AccuLearn pre-test track generation failed for user '
        . $userid . ': ' . $e->getMessage());
    echo html_writer::div(
        'Failed to generate the track. The error has been recorded in the server log — '
        . 'if this persists, check the AccuLearn backend configuration and OpenAI API status.',
        'al-card al-card--flag-needs'
    );
}

echo html_writer::link(
    new moodle_url('/local/acculearnfeedback/pretest_entry.php'),
    'Back to pre-test entry',
    ['class' => 'al-btn al-btn-secondary', 'style' => 'margin-top:16px;']
);

echo html_writer::end_div();

echo $OUTPUT->footer();
