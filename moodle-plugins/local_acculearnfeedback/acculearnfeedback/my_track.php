<?php
require_once(__DIR__ . '/../../config.php');
require_login();

global $DB, $OUTPUT, $PAGE, $USER;

$context = context_system::instance();
$PAGE->set_url('/local/acculearnfeedback/my_track.php');
$PAGE->set_context($context);
$PAGE->set_title('My Learning Track');
$PAGE->set_heading('My Learning Track');

echo $OUTPUT->header();

$trackrow = $DB->get_record_sql("SELECT * FROM acculearn_learning_tracks WHERE userid = ?", [$USER->id]);

if (!$trackrow) {
    echo html_writer::div(
        'Your learning track has not been generated yet. This usually happens after your pre-test results are entered, or after you complete your first quiz.',
        'alert alert-info'
    );
    echo $OUTPUT->footer();
    exit;
}

$track = json_decode($trackrow->track_json, true);
$narrative = $track['narrative'];
$competencies = $track['competencies'];

echo html_writer::start_div('', ['style' => 'max-width:900px; margin:auto;']);

// Narrative card
echo html_writer::start_div('', ['style' => 'background:#f0f7ff; border-left:4px solid #0066cc; padding:16px; border-radius:4px; margin-bottom:24px;']);
echo html_writer::tag('h4', 'Overview');
echo html_writer::tag('p', s($narrative['overview']));
echo html_writer::tag('h5', 'What to focus on next');
echo html_writer::tag('p', s($narrative['next_focus']));
echo html_writer::tag('p', s($narrative['encouragement']), ['style' => 'font-style:italic; color:#0066cc;']);
echo html_writer::end_div();

// Competency track cards
echo html_writer::tag('h4', 'Your Progress by Competency');

$statuscolors = [
    'Mastered' => '#2e7d32',
    'Developing' => '#f9a825',
    'Needs Improvement' => '#c62828',
    'Not Attempted' => '#757575',
];

foreach ($competencies as $c) {
    $color = $statuscolors[$c['mastery']] ?? '#757575';
    echo html_writer::start_div('', [
        'style' => "border:1px solid #ddd; border-left:6px solid $color; border-radius:4px; padding:14px 18px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center;"
    ]);

    echo html_writer::start_div();
    echo html_writer::tag('strong', s($c['competency_name']), ['style' => 'font-size:1.05em;']);
    echo html_writer::tag('div', ($c['cm'] !== null ? $c['cm'] . '% mastery' : 'Not yet attempted'), ['style' => 'color:#666; font-size:0.9em;']);
    echo html_writer::end_div();

    echo html_writer::start_div();
    echo html_writer::tag('span', s($c['mastery']), ['style' => "background:$color; color:white; padding:4px 10px; border-radius:12px; font-size:0.85em; margin-right:8px;"]);
    echo html_writer::tag('span', s($c['lp_label']), ['style' => 'color:#333; font-size:0.9em;']);
    echo html_writer::end_div();

    echo html_writer::end_div();
}

echo html_writer::end_div();

echo $OUTPUT->footer();
