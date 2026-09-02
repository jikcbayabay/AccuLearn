<?php
/**
 * Third-party attribution and licence notices.
 *
 * Every component listed here is permissively or copyleft licensed, and all of
 * those licences require attribution. This page is that attribution.
 *
 * The evidence column quotes the licence header read out of the shipped file
 * itself rather than a filename or a package manifest, because that is what
 * actually governs the copy being served. The audit behind it is recorded in
 * docs/testing/test-evidence.md section 4.
 *
 * Reachable by every logged-in user, deliberately: an attribution notice that
 * only administrators can see does not discharge the obligation.
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

global $OUTPUT, $PAGE, $USER, $SITE;

$context = context_system::instance();
$PAGE->set_url('/local/acculearnfeedback/attribution.php');
$PAGE->set_context($context);
$PAGE->set_title('Credits and licences');
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading('Credits and licences');

local_acculearnfeedback_log_usage($USER->id, 'view_attribution', 'attribution', null,
    'Opened the credits and licences page');

echo html_writer::start_div('al-page');

echo html_writer::tag('p',
    'AccuLearn is built on open-source software. The components below are reproduced '
  . 'or redistributed under the licences named here, each of which requires that the '
  . 'original authors be credited. The evidence column quotes the licence notice found '
  . 'inside the file as shipped.',
    ['class' => 'al-page__subtitle', 'style' => 'margin-bottom:1.25rem;']);

// Platform and interface.
echo html_writer::tag('h4',
    '<i class="fa fa-cubes" aria-hidden="true"></i>Platform and interface',
    ['style' => 'margin-top:8px;']);

$platform = [
    [
        'Moodle LMS',
        'GNU GPL v3 or later',
        'The learning platform AccuLearn extends. Copyright Moodle Pty Ltd and '
      . 'contributors. AccuLearn\'s own plugins are distributed under the same licence.',
    ],
    [
        'Inter typeface',
        'SIL Open Font License 1.1',
        'Used for the AccuLearn interface and self-hosted rather than loaded from a '
      . 'third-party font service. Copyright the Inter Project Authors.',
    ],
    [
        'Font Awesome Free',
        'Icons CC BY 4.0, fonts SIL OFL 1.1, code MIT',
        'Toolbar and heading icons, shipped as part of Moodle core rather than added '
      . 'separately by AccuLearn. Copyright Fonticons, Inc.',
    ],
];

echo html_writer::start_div('al-table-wrap');
echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%;']);
echo html_writer::tag('tr',
    html_writer::tag('th', 'Component') .
    html_writer::tag('th', 'Licence') .
    html_writer::tag('th', 'Notes')
);
foreach ($platform as $row) {
    echo html_writer::tag('tr',
        html_writer::tag('td', s($row[0]), ['style' => 'font-weight:600;']) .
        html_writer::tag('td', s($row[1])) .
        html_writer::tag('td', s($row[2]))
    );
}
echo html_writer::end_tag('table');
echo html_writer::end_div();

// Lesson content runtime.
echo html_writer::tag('h4',
    '<i class="fa fa-book-open" aria-hidden="true"></i>Lesson content runtime',
    ['style' => 'margin-top:28px;']);

echo html_writer::tag('p',
    'The FABM 1 lessons are delivered as SCORM packages authored in eXeLearning. '
  . 'Each package carries the authoring tool\'s own runtime and template icon set. '
  . 'An audit of all 29 packages found no third-party photographs, diagrams or '
  . 'illustrations in the instructional content: every image is an eXeLearning '
  . 'template icon, and the lesson material itself is authored text.',
    ['style' => 'margin-bottom:14px;']);

$runtime = [
    [
        'eXeLearning',
        'GNU GPL v2 or later',
        'Authoring tool runtime and template icons.',
        '"Copyright 2004–2005, University of Auckland / Copyright 2004–2008 '
      . 'eXe Project ... free software ... GNU General Public License ... version 2" '
      . '(common.js)',
    ],
    [
        'jQuery 3.7.1',
        'MIT',
        'JavaScript library bundled inside the lesson packages.',
        '"(c) OpenJS Foundation and other contributors | jquery.org/license" '
      . '(jquery.min.js)',
    ],
    [
        'Bootstrap',
        'MIT',
        'Stylesheet framework bundled inside the lesson packages.',
        '"Copyright 2011–2025 The Bootstrap Authors / Licensed under MIT" '
      . '(bootstrap.min.css)',
    ],
];

echo html_writer::start_div('al-table-wrap');
echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%;']);
echo html_writer::tag('tr',
    html_writer::tag('th', 'Component') .
    html_writer::tag('th', 'Licence') .
    html_writer::tag('th', 'Used for') .
    html_writer::tag('th', 'Evidence, read from the shipped file')
);
foreach ($runtime as $row) {
    echo html_writer::tag('tr',
        html_writer::tag('td', s($row[0]), ['style' => 'font-weight:600;']) .
        html_writer::tag('td', s($row[1])) .
        html_writer::tag('td', s($row[2])) .
        html_writer::tag('td', s($row[3]), ['style' => 'font-size:.9em; color:var(--al-ink-500);'])
    );
}
echo html_writer::end_tag('table');
echo html_writer::end_div();

// External services.
echo html_writer::tag('h4',
    '<i class="fa fa-cloud" aria-hidden="true"></i>External services',
    ['style' => 'margin-top:28px;']);

echo html_writer::tag('p',
    'AccuLearn generates feedback and learning-track narratives using OpenAI\'s '
  . 'GPT-4o mini through the OpenAI API. That is a paid service rather than a '
  . 'redistributed component, so it carries no attribution obligation, but it is '
  . 'named here because the same page is where a reader will look for it. What is '
  . 'sent to it is described in the site privacy notice.',
    ['style' => 'margin-bottom:0;']);

echo html_writer::end_div();
echo $OUTPUT->footer();
