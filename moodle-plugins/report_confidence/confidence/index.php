<?php
require_once('../../config.php');
require_login();

$context = context_system::instance();
require_capability('report/confidence:view', $context);

$PAGE->set_url('/report/confidence/index.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('confidencereport', 'report_confidence'));
$PAGE->set_heading(get_string('confidencereport', 'report_confidence'));
$PAGE->set_pagelayout('report');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('confidencereport', 'report_confidence'));

$exporturl = new moodle_url('/report/confidence/export.php');
echo html_writer::link($exporturl, get_string('exportcsv', 'report_confidence'),
    ['class' => 'btn btn-primary']);

echo $OUTPUT->footer();
