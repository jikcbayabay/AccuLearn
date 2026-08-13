<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

global $DB, $OUTPUT, $PAGE, $USER;

$context = context_system::instance();
require_capability('local/acculearnfeedback:viewusagelogs', $context);

$export = optional_param('export', '', PARAM_ALPHA); // '' | 'events' | 'summary'

// Same anonymisation model as teacher_review.php and the confidence export:
// pseudonymous by default, real names only for holders of the dedicated
// reveal capability.
$canreveal = has_capability('local/acculearnfeedback:revealstudentnames', $context);
$revealnames = $canreveal ? optional_param('reveal', 0, PARAM_BOOL) : 0;

// Filters. All optional; each narrows both the table and the CSV export so
// what you download is what you were looking at.
$filteruser   = optional_param('filteruser', 0, PARAM_INT);
$filteraction = optional_param('filteraction', '', PARAM_ALPHANUMEXT);
$filterdays   = optional_param('filterdays', 0, PARAM_INT);

$where = [];
$params = [];
if ($filteruser) {
    $where[] = 'l.userid = ?';
    $params[] = $filteruser;
}
if ($filteraction !== '') {
    $where[] = 'l.action = ?';
    $params[] = $filteraction;
}
if ($filterdays > 0) {
    $where[] = 'l.timecreated >= ?';
    $params[] = time() - ($filterdays * 86400);
}
$whersql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT l.*, u.firstname, u.lastname, c.fullname AS coursename
          FROM {acculearn_usage_log} l
          JOIN {user} u ON u.id = l.userid
     LEFT JOIN {course} c ON c.id = l.courseid
        $whersql
      ORDER BY l.timecreated DESC";

/**
 * Per-student engagement summary. These are precisely the metrics the study
 * commits to reporting (thesis 3.5.3 and 3.8.1): frequency of access,
 * duration of use, number of quizzes attempted, and lesson access. Previously
 * none of them could be produced from this log at all, because only two
 * event types were ever recorded.
 *
 * "Active days" is the honest stand-in for duration: it counts distinct
 * calendar days on which the student did anything, which is defensible from
 * event timestamps. A true time-on-task figure would need session end events
 * that Moodle does not reliably emit.
 *
 * TIMEZONE: the stored timestamps are correct (epoch seconds are absolute),
 * but the DAY BUCKETING was not. MySQL's FROM_UNIXTIME() uses the database
 * server's timezone, which here is UTC, while every timestamp shown on screen
 * is rendered in Moodle's timezone (UTC+8). Days were therefore being cut at
 * 08:00 local rather than midnight, so anything a student did before 8am was
 * counted against the previous day - on real data that misfiled roughly a
 * hundred events per day. The offset below comes from Moodle's own configured
 * timezone so the buckets match what the page displays. A fixed offset is
 * safe here because the study's timezone (Philippines) has no daylight saving.
 */
$tzoffset = (int) (new DateTimeZone(\core_date::get_server_timezone()))
    ->getOffset(new DateTime('@' . time()));
$summarysql = "
    SELECT l.userid,
           u.firstname, u.lastname,
           COUNT(*)                                                        AS events,
           COUNT(DISTINCT FROM_UNIXTIME(l.timecreated + $tzoffset, '%Y-%m-%d')) AS activedays,
           SUM(l.action = 'session_start')                                 AS logins,
           SUM(l.action = 'view_lesson')                                   AS lessonviews,
           SUM(l.action IN ('quiz_submitted', 'quiz_attempt_submitted'))   AS quizsubmits,
           SUM(l.action = 'view_feedback')                                 AS feedbackviews,
           SUM(l.action = 'view_track')                                    AS trackviews,
           MIN(l.timecreated)                                              AS firstseen,
           MAX(l.timecreated)                                              AS lastseen
      FROM {acculearn_usage_log} l
      JOIN {user} u ON u.id = l.userid
     $whersql
  GROUP BY l.userid, u.firstname, u.lastname
  ORDER BY events DESC";

if ($export !== '') {
    // Bulk export of per-student activity - protected with sesskey so it
    // cannot be triggered by simply causing a logged-in teacher's browser
    // to request the URL.
    require_sesskey();

    $issummary = ($export === 'summary');
    $recordset = $DB->get_recordset_sql($issummary ? $summarysql : $sql, $params);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="acculearn_usage_'
        . ($issummary ? 'summary' : 'events')
        . ($revealnames ? '_identified' : '_anonymised') . '.csv"');

    $out = fopen('php://output', 'w');
    $namecols = $revealnames ? ['User ID', 'First Name', 'Last Name'] : ['Pseudonym'];

    if ($issummary) {
        fputcsv($out, array_merge($namecols,
            ['Total Events', 'Active Days', 'Logins', 'Lessons Opened', 'Quizzes Submitted',
             'Feedback Views', 'Track Views', 'First Activity', 'Last Activity']), ',', '"', '\\');
    } else {
        fputcsv($out, array_merge(['Log ID'], $namecols,
            ['Action', 'What', 'Course', 'Target Type', 'Target ID', 'Logged At']), ',', '"', '\\');
    }

    foreach ($recordset as $r) {
        $name = $revealnames
            ? [$r->userid, $r->firstname, $r->lastname]
            : [local_acculearnfeedback_pseudonym($r->userid)];

        if ($issummary) {
            $row = array_merge($name, [
                $r->events, $r->activedays, $r->logins, $r->lessonviews, $r->quizsubmits,
                $r->feedbackviews, $r->trackviews,
                userdate($r->firstseen, '%Y-%m-%d %H:%M:%S'),
                userdate($r->lastseen, '%Y-%m-%d %H:%M:%S'),
            ]);
        } else {
            $row = array_merge([$r->id], $name, [
                local_acculearnfeedback_usage_action_label($r->action),
                $r->detail,
                $r->coursename,
                $r->target_type,
                $r->target_id,
                userdate($r->timecreated, '%Y-%m-%d %H:%M:%S'),
            ]);
        }
        fputcsv($out, local_acculearnfeedback_csv_row($row), ',', '"', '\\');
    }
    $recordset->close();
    fclose($out);
    exit;
}

$PAGE->set_url('/local/acculearnfeedback/usage_logs.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('usagelogstitle', 'local_acculearnfeedback'));
// Boost paints $PAGE->set_heading() in the full-width page-header region,
// which is a different element from the $OUTPUT->heading() printed at the top
// of the content column - so passing the page's own title to both rendered it
// twice, one above the other. The page heading is meant to name the SITE.
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('usagelogstitle', 'local_acculearnfeedback'));

echo html_writer::start_div('al-page');

echo html_writer::div(
    'Records logins, competency-course visits, lesson opens, quiz starts and submissions, and every ' .
    'AI feedback / learning-track generation and view - per student, with what was actually opened. ' .
    'This is the raw engagement dataset behind the study\'s frequency-of-use and completion metrics ' .
    '(thesis Sections 3.5.3 / 3.8.7). Anonymise before analysis if this data leaves the research team.',
    'al-card al-card--accent'
);

// Reveal / hide toggle, mirroring teacher_review.php.
if ($canreveal) {
    $toggleurl = new moodle_url('/local/acculearnfeedback/usage_logs.php',
        ['reveal' => $revealnames ? 0 : 1, 'filteruser' => $filteruser,
         'filteraction' => $filteraction, 'filterdays' => $filterdays]);
    echo html_writer::start_div('', ['style' => 'margin-bottom:16px; display:flex; align-items:center; gap:10px;']);
    echo html_writer::link($toggleurl,
        ($revealnames ? 'Hide names (show pseudonyms)' : 'Reveal real names'),
        ['class' => 'al-btn al-btn-sm al-btn-secondary']);
    echo html_writer::tag('span',
        ($revealnames ? 'Showing real names' : 'Student names are anonymized'),
        ['style' => 'color:var(--al-ink-500); font-size:0.85em;']);
    echo html_writer::end_div();
}

// ---- Filters ----
$students = $DB->get_records_sql(
    "SELECT DISTINCT u.id, u.firstname, u.lastname
       FROM {acculearn_usage_log} l JOIN {user} u ON u.id = l.userid
   ORDER BY u.lastname, u.firstname");
$actions = $DB->get_fieldset_sql("SELECT DISTINCT action FROM {acculearn_usage_log} ORDER BY action");

echo html_writer::start_div('al-card', ['style' => 'margin-bottom:16px;']);
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $PAGE->url,
    'style' => 'display:flex; flex-wrap:wrap; gap:14px; align-items:center;']);
if ($revealnames) {
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'reveal', 'value' => 1]);
}

echo html_writer::tag('span', 'Student:', ['style' => 'font-weight:600;']);
echo html_writer::start_tag('select', ['name' => 'filteruser']);
echo html_writer::tag('option', 'All students', ['value' => 0]);
foreach ($students as $s) {
    $label = $revealnames ? ($s->lastname . ', ' . $s->firstname)
                          : local_acculearnfeedback_pseudonym($s->id);
    $attrs = ['value' => $s->id] + ($filteruser == $s->id ? ['selected' => 'selected'] : []);
    echo html_writer::tag('option', s($label), $attrs);
}
echo html_writer::end_tag('select');

echo html_writer::tag('span', 'Activity:', ['style' => 'font-weight:600;']);
echo html_writer::start_tag('select', ['name' => 'filteraction']);
echo html_writer::tag('option', 'All activity', ['value' => '']);
foreach ($actions as $a) {
    $attrs = ['value' => $a] + ($filteraction === $a ? ['selected' => 'selected'] : []);
    echo html_writer::tag('option', s(local_acculearnfeedback_usage_action_label($a)), $attrs);
}
echo html_writer::end_tag('select');

echo html_writer::tag('span', 'Period:', ['style' => 'font-weight:600;']);
echo html_writer::start_tag('select', ['name' => 'filterdays']);
foreach ([0 => 'All time', 1 => 'Last 24 hours', 7 => 'Last 7 days', 30 => 'Last 30 days'] as $d => $lbl) {
    $attrs = ['value' => $d] + ($filterdays == $d ? ['selected' => 'selected'] : []);
    echo html_writer::tag('option', $lbl, $attrs);
}
echo html_writer::end_tag('select');

echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => 'Apply', 'class' => 'al-btn al-btn-sm al-btn-primary']);
echo html_writer::link($PAGE->url, 'Reset', ['class' => 'al-btn al-btn-sm al-btn-secondary']);
echo html_writer::end_tag('form');
echo html_writer::end_div();

// ---- Export buttons ----
$exportparams = ['sesskey' => sesskey(), 'filteruser' => $filteruser,
    'filteraction' => $filteraction, 'filterdays' => $filterdays];
echo html_writer::start_div('', ['style' => 'margin-bottom:16px; display:flex; flex-wrap:wrap; gap:8px;']);
echo html_writer::link(
    new moodle_url('/local/acculearnfeedback/usage_logs.php', $exportparams + ['export' => 'events']),
    'Download events CSV', ['class' => 'al-btn al-btn-primary']);
echo html_writer::link(
    new moodle_url('/local/acculearnfeedback/usage_logs.php', $exportparams + ['export' => 'summary']),
    'Download per-student summary CSV', ['class' => 'al-btn al-btn-secondary']);
if ($canreveal) {
    echo html_writer::link(
        new moodle_url('/local/acculearnfeedback/usage_logs.php',
            $exportparams + ['export' => 'events', 'reveal' => 1]),
        'Events CSV with real names', ['class' => 'al-btn al-btn-secondary']);
}
echo html_writer::end_div();

// ---- Per-student engagement summary ----
$summary = $DB->get_records_sql($summarysql, $params);

echo html_writer::tag('h4', '<i class="fa fa-users" aria-hidden="true"></i>Engagement summary per student');
echo html_writer::tag('p',
    '"Active days" counts distinct days with any recorded activity - the defensible stand-in for '
    . '"duration of use", since Moodle does not reliably emit session-end events.',
    ['style' => 'color:var(--al-ink-500); font-size:0.85em; margin-top:-6px;']);

if ($summary) {
    echo html_writer::start_div('al-table-wrap');
    echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%;']);
    echo html_writer::tag('tr',
        html_writer::tag('th', 'Student') .
        html_writer::tag('th', 'Events') .
        html_writer::tag('th', 'Active days') .
        html_writer::tag('th', 'Logins') .
        html_writer::tag('th', 'Lessons opened') .
        html_writer::tag('th', 'Quizzes submitted') .
        html_writer::tag('th', 'Feedback views') .
        html_writer::tag('th', 'Track views') .
        html_writer::tag('th', 'Last active')
    );
    foreach ($summary as $r) {
        $displayname = $revealnames
            ? s($r->firstname . ' ' . $r->lastname)
            : local_acculearnfeedback_pseudonym($r->userid);
        echo html_writer::tag('tr',
            html_writer::tag('td', $displayname) .
            html_writer::tag('td', html_writer::tag('span', $r->events, ['class' => 'tnum'])) .
            html_writer::tag('td', html_writer::tag('span', $r->activedays, ['class' => 'tnum'])) .
            html_writer::tag('td', html_writer::tag('span', $r->logins, ['class' => 'tnum'])) .
            html_writer::tag('td', html_writer::tag('span', $r->lessonviews, ['class' => 'tnum'])) .
            html_writer::tag('td', html_writer::tag('span', $r->quizsubmits, ['class' => 'tnum'])) .
            html_writer::tag('td', html_writer::tag('span', $r->feedbackviews, ['class' => 'tnum'])) .
            html_writer::tag('td', html_writer::tag('span', $r->trackviews, ['class' => 'tnum'])) .
            html_writer::tag('td', userdate($r->lastseen, '%Y-%m-%d %H:%M'))
        );
    }
    echo html_writer::end_tag('table');
    echo html_writer::end_div();
} else {
    echo html_writer::div('No activity matches the current filters.', 'al-card');
}

// ---- Event stream ----
$rows = $DB->get_records_sql($sql, $params, 0, 300);

echo html_writer::tag('h4', '<i class="fa fa-list" aria-hidden="true"></i>Activity detail',
    ['style' => 'margin-top:28px;']);

echo html_writer::start_div('al-table-wrap');
echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%;']);
echo html_writer::tag('tr',
    html_writer::tag('th', 'When') .
    html_writer::tag('th', 'Student') .
    html_writer::tag('th', 'Activity') .
    html_writer::tag('th', 'What') .
    html_writer::tag('th', 'Competency course')
);
foreach ($rows as $r) {
    $displayname = $revealnames
        ? s($r->firstname . ' ' . $r->lastname)
        : local_acculearnfeedback_pseudonym($r->userid);

    // Older rows predate the detail column; fall back to the bare target so
    // historical entries still say something rather than rendering blank.
    $what = $r->detail !== null && $r->detail !== ''
        ? s($r->detail)
        : ($r->target_type
            ? html_writer::tag('em', s($r->target_type) . ' #' . (int)$r->target_id)
            : html_writer::tag('em', 'no detail recorded', ['style' => 'opacity:0.6;']));

    echo html_writer::tag('tr',
        html_writer::tag('td', userdate($r->timecreated, '%Y-%m-%d %H:%M')) .
        html_writer::tag('td', $displayname) .
        html_writer::tag('td', s(local_acculearnfeedback_usage_action_label($r->action))) .
        html_writer::tag('td', $what) .
        html_writer::tag('td', $r->coursename ? s($r->coursename) : '—')
    );
}
echo html_writer::end_tag('table');
echo html_writer::end_div();

if (empty($rows)) {
    echo html_writer::div(
        html_writer::div('<i class="fa fa-chart-line" aria-hidden="true"></i>', 'al-empty__icon') .
        html_writer::div('No usage logged yet', 'al-empty__title') .
        html_writer::div('Entries appear here as students log in, open lessons, submit quizzes, or view their feedback and track.', 'al-empty__sub'),
        'al-card al-empty'
    );
} else {
    $total = $DB->count_records_sql("SELECT COUNT(*) FROM {acculearn_usage_log} l $whersql", $params);
    echo html_writer::div('Showing the ' . count($rows) . ' most recent of ' . $total . ' matching events.',
        '', ['style' => 'color:var(--al-ink-500); font-size:0.85em; margin-top:8px;']);
}

echo html_writer::end_div();

echo $OUTPUT->footer();
