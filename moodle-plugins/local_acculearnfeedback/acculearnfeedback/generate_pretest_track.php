<?php
require_once(__DIR__ . '/../../config.php');
require_login();

global $DB, $OUTPUT, $PAGE;

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$userid = required_param('userid', PARAM_INT);

$PAGE->set_url('/local/acculearnfeedback/generate_pretest_track.php', ['userid' => $userid]);
$PAGE->set_context($context);
$PAGE->set_title('Generate Initial Track');
$PAGE->set_heading('Generate Initial Track');

echo $OUTPUT->header();

$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
$studentName = $user->firstname . ' ' . $user->lastname;

require_once('/var/www/html/acculearn-backend/track_engine.php');

echo html_writer::tag('p', "Generating initial learning track for $studentName based on pre-test data...");

try {
    $engine = new TrackEngine();
    $result = $engine->generateTrack($userid, $studentName, 'pretest');

    echo html_writer::div('Track generated successfully.', 'alert alert-success');

    echo html_writer::tag('h4', 'Narrative');
    echo html_writer::tag('p', s($result['narrative']['overview']));
    echo html_writer::tag('p', '<strong>Next focus:</strong> ' . s($result['narrative']['next_focus']));
    echo html_writer::tag('p', '<em>' . s($result['narrative']['encouragement']) . '</em>');

    echo html_writer::tag('h4', 'Competency Breakdown');
    echo html_writer::start_tag('table', ['class' => 'generaltable']);
    echo html_writer::tag('tr', html_writer::tag('th', 'Competency') . html_writer::tag('th', 'CM') . html_writer::tag('th', 'Mastery') . html_writer::tag('th', 'Pathway'));
    foreach ($result['competencies'] as $c) {
        echo html_writer::tag('tr',
            html_writer::tag('td', $c['competency_name']) .
            html_writer::tag('td', ($c['cm'] ?? 'N/A') . '%') .
            html_writer::tag('td', $c['mastery']) .
            html_writer::tag('td', $c['lp_label'])
        );
    }
    echo html_writer::end_tag('table');

} catch (Exception $e) {
    echo html_writer::div('Failed to generate track: ' . s($e->getMessage()), 'alert alert-danger');
}

echo html_writer::link(
    new moodle_url('/local/acculearnfeedback/pretest_entry.php', ['userid' => $userid]),
    'Back to pre-test entry',
    ['class' => 'btn btn-secondary', 'style' => 'margin-top:16px;']
);

echo $OUTPUT->footer();
