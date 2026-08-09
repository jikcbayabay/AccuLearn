<?php
require_once('../../config.php');
require_login();

$context = context_system::instance();
require_capability('report/confidence:view', $context);

$PAGE->set_url('/report/confidence/index.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('confidencereport', 'report_confidence'));
// Boost paints \$PAGE->set_heading() in the full-width page-header region,
// which is a different element from the \$OUTPUT->heading() printed at the top
// of the content column - so passing the page's own title to both rendered it
// twice, one above the other. The page heading is meant to name the SITE.
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('report');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('confidencereport', 'report_confidence'));

$canreveal = has_capability('local/acculearnfeedback:revealstudentnames', $context);

echo html_writer::start_div('al-page');
echo html_writer::start_div('al-card');
echo html_writer::tag('p',
    'Export every recorded confidence rating alongside answer correctness, for every attempt on this site.',
    ['style' => 'color:var(--al-ink-500); margin-top:0;']
);

echo html_writer::tag('p',
    'Students are exported under their stable AccuLearn pseudonym (the same one shown in AI Output Review), '
    . 'not their real names.',
    ['style' => 'color:var(--al-ink-500);']
);

$exporturl = new moodle_url('/report/confidence/export.php', ['sesskey' => sesskey()]);
echo html_writer::link($exporturl, get_string('exportcsv', 'report_confidence'), [
    'class' => 'al-btn al-btn-primary',
]);

// Identified export is a separate, deliberate action gated on the same
// capability as the reveal toggle in AI Output Review - so a plain teacher
// cannot obtain a name-to-answers mapping this way either.
if ($canreveal) {
    $identifiedurl = new moodle_url('/report/confidence/export.php',
        ['sesskey' => sesskey(), 'reveal' => 1]);
    echo html_writer::link($identifiedurl, 'Export with real names', [
        'class' => 'al-btn al-btn-secondary',
        'style' => 'margin-left:8px;',
    ]);
}

echo html_writer::end_div();
echo html_writer::end_div();

echo $OUTPUT->footer();
