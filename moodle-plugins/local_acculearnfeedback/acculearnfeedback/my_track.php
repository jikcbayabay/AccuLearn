<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
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

echo html_writer::start_div('al-page');

// Anonymization badge: shows the student their own pseudonym so they can
// recognize their row when the teacher discusses results anonymously.
echo html_writer::div(
    'You are ' . html_writer::tag('strong', local_acculearnfeedback_pseudonym($USER->id)),
    'al-badge al-badge--pill',
    ['style' => 'margin-bottom:16px;']
);

// Narrative card
echo html_writer::start_div('al-card al-card--accent', ['style' => 'margin-bottom:24px;']);
echo html_writer::tag('h4', 'Overview', ['style' => 'margin-top:0;']);
echo html_writer::tag('p', s($narrative['overview']));
echo html_writer::tag('h5', 'What to focus on next');
echo html_writer::tag('p', s($narrative['next_focus']));
echo html_writer::tag('p', s($narrative['encouragement']), ['style' => 'font-style:italic; color:var(--al-blue);']);
echo html_writer::end_div();

// Competency track cards
echo html_writer::tag('h4', 'Your Progress by Competency');

$masterytones = [
    'Mastered' => 'mastered',
    'Developing' => 'developing',
    'Needs Improvement' => 'needs',
    'Not Attempted' => 'neutral',
];
$masteryflags = [
    'Mastered' => 'mastered',
    'Developing' => 'developing',
    'Needs Improvement' => 'needs',
    'Not Attempted' => 'neutral',
];

foreach ($competencies as $c) {
    $tone = $masterytones[$c['mastery']] ?? 'neutral';
    $flag = $masteryflags[$c['mastery']] ?? 'neutral';
    echo html_writer::start_div("al-card al-card--flag-$flag", [
        'style' => 'display:flex; justify-content:space-between; align-items:center;'
    ]);

    echo html_writer::start_div();
    echo html_writer::tag('strong', s($c['competency_name']), ['style' => 'font-size:1.05em;']);
    echo html_writer::tag('div', ($c['cm'] !== null ? $c['cm'] . '% mastery' : 'Not yet attempted'), ['style' => 'color:var(--al-ink-500); font-size:0.9em;']);
    echo html_writer::end_div();

    echo html_writer::start_div();
    echo html_writer::tag('span', s($c['mastery']), ['class' => "al-badge al-badge--$tone", 'style' => 'margin-right:8px;']);
    echo html_writer::tag('span', s($c['lp_label']), ['style' => 'color:var(--al-ink-700); font-size:0.9em;']);
    echo html_writer::end_div();

    echo html_writer::end_div();
}

echo html_writer::end_div();

echo $OUTPUT->footer();
