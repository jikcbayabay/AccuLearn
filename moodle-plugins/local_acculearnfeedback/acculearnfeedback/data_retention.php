<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

global $DB, $OUTPUT, $PAGE, $USER;

$context = context_system::instance();
// Reuses the sequence-management capability rather than introducing a new
// one - this page is squarely an administrative data-governance action,
// same audience as admin_sequence.php.
require_capability('local/acculearnfeedback:managesequence', $context);

// Deliberately NOT a scheduled/automatic purge (see CLAUDE.md guardrail on
// destructive DB operations without explicit confirmation). This page only
// ever reports what is past the retention window; deleting a category
// requires an explicit, per-category form submission with sesskey + an
// on-page JS confirm(), triggered by a human, every time.
define('AL_RETENTION_DAYS', 180);
$cutoff = time() - (AL_RETENTION_DAYS * 86400);

// 'fromclause' is what actually appears after FROM/DELETE FROM in raw SQL.
// acculearn_pretest_responses/acculearn_feedback/acculearn_learning_tracks
// are legacy tables created outside Moodle's schema management and are
// queried by their literal unprefixed name everywhere in this plugin (see
// db/install.xml). acculearn_usage_log is a normal Moodle-managed table
// (created via db/install.xml through the standard DB manager), so it DOES
// carry the site prefix and needs the {tablename} placeholder instead -
// mixing the two conventions up here caused a real write failure caught
// during deployment verification.
$categories = [
    'pretest' => [
        'label' => 'Pre-test responses',
        'fromclause' => 'acculearn_pretest_responses',
        'timefield' => 'entered_at',
    ],
    'feedback' => [
        'label' => 'AI feedback records',
        'fromclause' => 'acculearn_feedback',
        'timefield' => 'timecreated',
    ],
    'tracks' => [
        'label' => 'Learning tracks',
        'fromclause' => 'acculearn_learning_tracks',
        'timefield' => 'generated_at',
    ],
    'usagelog' => [
        'label' => 'Usage log entries',
        'fromclause' => '{acculearn_usage_log}',
        'timefield' => 'timecreated',
    ],
];

$notice = null;
$noticetype = \core\output\notification::NOTIFY_SUCCESS;

if (data_submitted() && confirm_sesskey()) {
    $category = required_param('category', PARAM_ALPHA);
    $formaction = required_param('formaction', PARAM_ALPHA);

    if ($formaction === 'purge' && isset($categories[$category])) {
        $cat = $categories[$category];
        $deleted = $DB->get_field_sql(
            "SELECT COUNT(*) FROM {$cat['fromclause']} WHERE {$cat['timefield']} < ?", [$cutoff]);
        $DB->execute("DELETE FROM {$cat['fromclause']} WHERE {$cat['timefield']} < ?", [$cutoff]);
        $notice = "Deleted $deleted record(s) from {$cat['label']} older than " . AL_RETENTION_DAYS . " days.";
        redirect($PAGE->url, $notice, null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

$PAGE->set_url('/local/acculearnfeedback/data_retention.php');
$PAGE->set_context($context);
$PAGE->set_title('Data Retention Review');
// Boost paints $PAGE->set_heading() in the full-width page-header region,
// which is a different element from the $OUTPUT->heading() printed at the top
// of the content column - so passing the page's own title to both rendered it
// twice, one above the other. The page heading is meant to name the SITE.
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading('Data Retention Review');

echo html_writer::start_div('al-page');

echo html_writer::div(
    'AccuLearn keeps this data solely within the scope of the research study (thesis Section 3.6.7). ' .
    'This page only reports what is older than ' . AL_RETENTION_DAYS . ' days - nothing is deleted ' .
    'automatically. Review with your research team before purging anything; once deleted, there is ' .
    'no automated backup to recover it from.',
    'al-card al-card--accent'
);

echo html_writer::start_div('al-table-wrap');
echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%;']);
echo html_writer::tag('tr',
    html_writer::tag('th', 'Category') .
    html_writer::tag('th', 'Total records') .
    html_writer::tag('th', "Older than " . AL_RETENTION_DAYS . " days") .
    html_writer::tag('th', 'Action')
);

foreach ($categories as $key => $cat) {
    $total = $DB->get_field_sql("SELECT COUNT(*) FROM {$cat['fromclause']}");
    $stale = $DB->get_field_sql("SELECT COUNT(*) FROM {$cat['fromclause']} WHERE {$cat['timefield']} < ?", [$cutoff]);

    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', html_writer::tag('strong', $cat['label']));
    echo html_writer::tag('td', html_writer::tag('span', $total, ['class' => 'tnum']));
    echo html_writer::tag('td',
        html_writer::tag('span', $stale, ['class' => 'tnum']) .
        ($stale > 0 ? html_writer::tag('span', ' flagged', ['class' => 'al-badge al-badge--flagged', 'style' => 'margin-left:6px;']) : '')
    );

    echo html_writer::start_tag('td');
    if ($stale > 0) {
        echo html_writer::start_tag('form', [
            'method' => 'post', 'action' => $PAGE->url,
            'onsubmit' => "return confirm('Permanently delete $stale record(s) from {$cat['label']}? " .
                "There is no automated backup to recover this.');",
        ]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'formaction', 'value' => 'purge']);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'category', 'value' => $key]);
        echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => 'Delete these records', 'class' => 'al-btn al-btn-sm al-btn-danger']);
        echo html_writer::end_tag('form');
    } else {
        echo html_writer::tag('span', 'Nothing to purge', ['style' => 'color:var(--al-ink-500); font-size:0.85em;']);
    }
    echo html_writer::end_tag('td');
    echo html_writer::end_tag('tr');
}

echo html_writer::end_tag('table');
echo html_writer::end_div();

echo html_writer::end_div();

echo $OUTPUT->footer();
