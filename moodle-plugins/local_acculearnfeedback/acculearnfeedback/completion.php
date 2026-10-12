<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

global $DB, $OUTPUT, $PAGE;

$context = context_system::instance();
// Teachers only since 2026-10-08 - it used to be an admin page. Admin
// accounts are refused even though a site admin passes every capability
// check (see local_acculearnfeedback_can_view_completion()).
require_capability('local/acculearnfeedback:viewcompletion', $context);
if (!local_acculearnfeedback_can_view_completion()) {
    throw new required_capability_exception($context, 'local/acculearnfeedback:viewcompletion',
        'nopermissions', '');
}

$courseid = optional_param('courseid', 0, PARAM_INT); // 0 = overview of every course
$export = optional_param('export', 0, PARAM_BOOL);

// Same anonymisation model as usage_logs.php and teacher_review.php.
$canreveal = has_capability('local/acculearnfeedback:revealstudentnames', $context);
$revealnames = $canreveal ? optional_param('reveal', 0, PARAM_BOOL) : 0;

/*
 * WHERE "DONE" COMES FROM
 *
 * This deliberately does NOT read Moodle's activity-completion table. Every
 * lesson is set to manual completion (the student has to tick a box) and the
 * quizzes have no completion tracking at all, so course_modules_completion
 * holds four rows for the whole site - a report built on it would mark the
 * entire study cohort as having done nothing. Instead each activity is judged
 * on the evidence its own module records:
 *
 *   lesson (SCORM)  done when the package reported completed/passed for the
 *                   student (cmi.core.lesson_status in SCORM 1.2,
 *                   cmi.completion_status in 2004), or the student ticked the
 *                   manual completion box
 *   quiz            done when the student has at least one SUBMITTED attempt;
 *                   an attempt still in progress does not count
 *
 * Forums (Announcements) and anything else are not learning activities and
 * are left out.
 */

// The seven competency courses, in competency order.
$courses = [];
foreach ($DB->get_records_sql(
        "SELECT id, courseid, name FROM acculearn_competencies
          WHERE courseid IS NOT NULL ORDER BY id") as $comp) {
    $course = $DB->get_record('course', ['id' => $comp->courseid]);
    if (!$course) {
        continue;
    }
    $course->label = 'C' . (count($courses) + 1);
    $courses[(int) $course->id] = $course;
}
if ($courseid && !isset($courses[$courseid])) {
    $courseid = 0;
}

/**
 * Short column heading for an activity. The real names are long ("Lesson
 * Content A. Philippine Accounting Standards and Regulatory Framework") and
 * eleven of them side by side do not fit; the full name stays on hover.
 */
function local_acculearnfeedback_completion_short($cm) {
    $name = $cm->get_formatted_name();
    if ($cm->modname === 'scorm') {
        if (stripos($name, 'overview') !== false) {
            return 'Overview';
        }
        if (preg_match('/lesson content\W*([A-Z])\b/i', $name, $m)) {
            return 'Lesson ' . strtoupper($m[1]);
        }
    } else if ($cm->modname === 'quiz') {
        if (preg_match('/formative quiz\s*(\d+)/i', $name, $m)) {
            return 'Quiz ' . $m[1];
        }
        if (stripos($name, 'summative') !== false) {
            return 'Summative';
        }
    }
    return shorten_text($name, 18);
}

// Learning activities per course, in the order they appear on the course page.
$activities = [];
foreach ($courses as $cid => $course) {
    $activities[$cid] = [];
    foreach (get_fast_modinfo($cid)->get_cms() as $cm) {
        if (!in_array($cm->modname, ['scorm', 'quiz']) || !$cm->visible || $cm->deletioninprogress) {
            continue;
        }
        $activities[$cid][] = $cm;
    }
}

// Students: anyone holding the student role in at least one competency course,
// minus staff accounts (the site admin is enrolled as a student for testing).
$studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
$students = [];
$enrolledin = []; // userid => [courseid => true]
foreach ($courses as $cid => $course) {
    $users = get_role_users($studentrole->id, context_course::instance($cid), false,
        'u.id, u.username, u.firstname, u.lastname', 'u.lastname, u.firstname');
    foreach ($users as $u) {
        if (local_acculearnfeedback_is_staff_account($u->id)) {
            continue;
        }
        $students[$u->id] = $u;
        $enrolledin[$u->id][$cid] = true;
    }
}

// Evidence of completion, one query per kind, keyed "userid:cmid".
$done = [];
$started = [];
$cmbyinstance = ['scorm' => [], 'quiz' => []];
foreach ($activities as $cms) {
    foreach ($cms as $cm) {
        $cmbyinstance[$cm->modname][$cm->instance] = $cm->id;
    }
}

if ($cmbyinstance['scorm']) {
    [$insql, $inparams] = $DB->get_in_or_equal(array_keys($cmbyinstance['scorm']));
    $rs = $DB->get_recordset_sql(
        "SELECT DISTINCT a.userid, a.scormid
           FROM {scorm_attempt} a
           JOIN {scorm_scoes_value} v ON v.attemptid = a.id
           JOIN {scorm_element} e ON e.id = v.elementid
          WHERE a.scormid $insql
            AND e.element IN ('cmi.core.lesson_status', 'cmi.completion_status')
            AND " . $DB->sql_compare_text('v.value', 20) . " IN ('completed', 'passed')",
        $inparams);
    foreach ($rs as $r) {
        $done[$r->userid . ':' . $cmbyinstance['scorm'][$r->scormid]] = true;
    }
    $rs->close();
}

if ($cmbyinstance['quiz']) {
    [$insql, $inparams] = $DB->get_in_or_equal(array_keys($cmbyinstance['quiz']));
    $rs = $DB->get_recordset_sql(
        "SELECT userid, quiz, MAX(CASE WHEN state = 'finished' THEN 1 ELSE 0 END) AS finished
           FROM {quiz_attempts}
          WHERE quiz $insql AND preview = 0
       GROUP BY userid, quiz",
        $inparams);
    foreach ($rs as $r) {
        $key = $r->userid . ':' . $cmbyinstance['quiz'][$r->quiz];
        if ($r->finished) {
            $done[$key] = true;
        } else {
            $started[$key] = true;
        }
    }
    $rs->close();
}

// Manual ticks on the course page count too.
$allcmids = array_merge(array_values($cmbyinstance['scorm']), array_values($cmbyinstance['quiz']));
if ($allcmids) {
    [$insql, $inparams] = $DB->get_in_or_equal($allcmids);
    $rs = $DB->get_recordset_sql(
        "SELECT userid, coursemoduleid FROM {course_modules_completion}
          WHERE coursemoduleid $insql AND completionstate IN (1, 2)", $inparams);
    foreach ($rs as $r) {
        $done[$r->userid . ':' . $r->coursemoduleid] = true;
    }
    $rs->close();
}

$displayname = function($u) use ($revealnames) {
    return $revealnames ? fullname($u) : local_acculearnfeedback_pseudonym($u->id);
};

// Pseudonyms are hashes, so sorting by them is as good as any other order and
// keeps the list stable; real names sort by surname as get_role_users returned.
if (!$revealnames) {
    uasort($students, function($a, $b) {
        return strcmp(local_acculearnfeedback_pseudonym($a->id), local_acculearnfeedback_pseudonym($b->id));
    });
}

/**
 * Count of done activities for a student in one course.
 */
$coursecount = function($userid, $cid) use ($activities, $done) {
    $n = 0;
    foreach ($activities[$cid] as $cm) {
        if (isset($done[$userid . ':' . $cm->id])) {
            $n++;
        }
    }
    return $n;
};

// ---- CSV export of whatever is on screen ----
if ($export) {
    require_sesskey();
    $filename = 'acculearn_completion_' . ($courseid ? $courses[$courseid]->shortname : 'all_courses')
        . ($revealnames ? '_identified' : '_anonymised') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . clean_filename($filename) . '"');
    $out = fopen('php://output', 'w');

    if ($courseid) {
        $head = ['Student'];
        foreach ($activities[$courseid] as $cm) {
            $head[] = $cm->get_formatted_name();
        }
        $head[] = 'Done';
        fputcsv($out, local_acculearnfeedback_csv_row($head), ',', '"', '\\');
        foreach ($students as $u) {
            if (empty($enrolledin[$u->id][$courseid])) {
                continue;
            }
            $row = [$displayname($u)];
            foreach ($activities[$courseid] as $cm) {
                $row[] = isset($done[$u->id . ':' . $cm->id]) ? 'Done' : 'Not done';
            }
            $row[] = $coursecount($u->id, $courseid) . '/' . count($activities[$courseid]);
            fputcsv($out, local_acculearnfeedback_csv_row($row), ',', '"', '\\');
        }
    } else {
        $head = ['Student'];
        foreach ($courses as $course) {
            $head[] = $course->fullname;
        }
        $head[] = 'Overall';
        fputcsv($out, local_acculearnfeedback_csv_row($head), ',', '"', '\\');
        foreach ($students as $u) {
            $row = [$displayname($u)];
            $tdone = $ttotal = 0;
            foreach ($courses as $cid => $course) {
                if (empty($enrolledin[$u->id][$cid])) {
                    $row[] = 'Not enrolled';
                    continue;
                }
                $n = $coursecount($u->id, $cid);
                $tdone += $n;
                $ttotal += count($activities[$cid]);
                $row[] = $n . '/' . count($activities[$cid]);
            }
            $row[] = $tdone . '/' . $ttotal;
            fputcsv($out, local_acculearnfeedback_csv_row($row), ',', '"', '\\');
        }
    }
    fclose($out);
    exit;
}

$PAGE->set_url('/local/acculearnfeedback/completion.php',
    ['courseid' => $courseid] + ($revealnames ? ['reveal' => 1] : []));
$PAGE->set_context($context);
$PAGE->set_title('Activity Completion');
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading('Activity Completion');

echo '<style>
.al-cmpl th, .al-cmpl td { text-align: center; white-space: nowrap; }
.al-cmpl th:first-child, .al-cmpl td:first-child { text-align: left; }
.al-cmpl__yes { color: var(--al-green-700); font-weight: 700; font-size: 1.1em; }
.al-cmpl__no  { color: var(--al-red-ink); font-weight: 700; font-size: 1.1em; }
.al-cmpl__na  { color: var(--al-ink-500); }
.al-cmpl__count { font-variant-numeric: tabular-nums; }
.al-cmpl__count--full { color: var(--al-green-700); font-weight: 700; }
.al-cmpl__count--none { color: var(--al-red-ink); }
.al-cmpl__type { display: block; font-weight: 400; font-size: .75em; color: var(--al-ink-500); }
</style>';

echo html_writer::start_div('al-page');

echo html_writer::div(
    'Shows which learning activities each student has completed (&#10003;) and which they have not (&#10007;). '
    . 'A lesson counts as done once the student has finished it; a quiz counts as done once the student '
    . 'has submitted at least one attempt.',
    'al-card al-card--accent'
);

// Course picker: overview plus one tab per competency course.
$baseparams = $revealnames ? ['reveal' => 1] : [];
$pills = [html_writer::link(new moodle_url('/local/acculearnfeedback/completion.php', $baseparams),
    'All courses', ['class' => 'al-btn al-btn-sm ' . ($courseid ? 'al-btn-secondary' : 'al-btn-primary')])];
foreach ($courses as $cid => $course) {
    $pills[] = html_writer::link(
        new moodle_url('/local/acculearnfeedback/completion.php', $baseparams + ['courseid' => $cid]),
        s($course->label), [
            'class' => 'al-btn al-btn-sm ' . ($courseid == $cid ? 'al-btn-primary' : 'al-btn-secondary'),
            'title' => format_string($course->fullname),
        ]);
}
echo html_writer::div(implode('', $pills), '',
    ['style' => 'margin-bottom:12px; display:flex; flex-wrap:wrap; gap:6px;']);

// Reveal toggle and export, as on Usage Logs.
echo html_writer::start_div('', ['style' => 'margin-bottom:16px; display:flex; flex-wrap:wrap; align-items:center; gap:10px;']);
if ($canreveal) {
    echo html_writer::link(
        new moodle_url('/local/acculearnfeedback/completion.php',
            ['courseid' => $courseid] + ($revealnames ? [] : ['reveal' => 1])),
        $revealnames ? 'Hide names (show pseudonyms)' : 'Reveal real names',
        ['class' => 'al-btn al-btn-sm al-btn-secondary']);
}
echo html_writer::link(
    new moodle_url('/local/acculearnfeedback/completion.php',
        ['courseid' => $courseid, 'export' => 1, 'sesskey' => sesskey()] + $baseparams),
    'Download CSV', ['class' => 'al-btn al-btn-sm al-btn-secondary']);
echo html_writer::tag('span',
    $revealnames ? 'Showing real names' : 'Student names are anonymized',
    ['style' => 'color:var(--al-ink-500); font-size:0.85em;']);
echo html_writer::end_div();

$yes = html_writer::tag('span', '&#10003;', ['class' => 'al-cmpl__yes', 'title' => 'Done']) .
    html_writer::tag('span', 'Done', ['class' => 'sr-only']);
$no = function($title) {
    return html_writer::tag('span', '&#10007;', ['class' => 'al-cmpl__no', 'title' => $title]) .
        html_writer::tag('span', $title, ['class' => 'sr-only']);
};

if (!$students) {
    echo html_writer::div('No students are enrolled in the competency courses.', 'al-card');
} else if ($courseid) {
    // ---- One course: a check or cross per activity ----
    $course = $courses[$courseid];
    $cms = $activities[$courseid];
    echo html_writer::tag('h4', '<i class="fa fa-list-check" aria-hidden="true"></i>'
        . s($course->label) . ' &middot; ' . format_string($course->fullname));

    $head = html_writer::tag('th', 'Student');
    foreach ($cms as $cm) {
        $head .= html_writer::tag('th',
            s(local_acculearnfeedback_completion_short($cm))
            . html_writer::tag('span', $cm->modname === 'quiz' ? 'quiz' : 'lesson', ['class' => 'al-cmpl__type']),
            ['title' => $cm->get_formatted_name(), 'scope' => 'col']);
    }
    $head .= html_writer::tag('th', 'Done', ['scope' => 'col']);

    $body = '';
    $peractivity = array_fill(0, count($cms), 0);
    $shown = 0;
    foreach ($students as $u) {
        if (empty($enrolledin[$u->id][$courseid])) {
            continue;
        }
        $shown++;
        $row = html_writer::tag('td', s($displayname($u)));
        foreach ($cms as $i => $cm) {
            $key = $u->id . ':' . $cm->id;
            if (isset($done[$key])) {
                $peractivity[$i]++;
                $row .= html_writer::tag('td', $yes);
            } else {
                $row .= html_writer::tag('td', $no(isset($started[$key]) ? 'Started, not submitted' : 'Not done'));
            }
        }
        $n = $coursecount($u->id, $courseid);
        $cls = $n == count($cms) ? ' al-cmpl__count--full' : ($n == 0 ? ' al-cmpl__count--none' : '');
        $row .= html_writer::tag('td', $n . '/' . count($cms), ['class' => 'al-cmpl__count' . $cls]);
        $body .= html_writer::tag('tr', $row);
    }

    // Footer: how many students finished each activity.
    $foot = html_writer::tag('th', 'Students done');
    foreach ($peractivity as $c) {
        $foot .= html_writer::tag('th', $c . '/' . $shown, ['class' => 'al-cmpl__count']);
    }
    $foot .= html_writer::tag('th', '');

    echo html_writer::div(
        html_writer::tag('table',
            html_writer::tag('thead', html_writer::tag('tr', $head)) .
            html_writer::tag('tbody', $body) .
            html_writer::tag('tfoot', html_writer::tag('tr', $foot)),
            ['class' => 'generaltable al-cmpl', 'style' => 'width:100%;']),
        'al-table-wrap');
} else {
    // ---- Overview: done/total per course ----
    echo html_writer::tag('h4', '<i class="fa fa-table" aria-hidden="true"></i>All competency courses');
    echo html_writer::tag('p',
        'Each cell is activities done out of the activities in that course; &#10003; means the student '
        . 'finished every one. Open a course above for the activity-by-activity view.',
        ['style' => 'color:var(--al-ink-500); font-size:0.85em; margin-top:-6px;']);

    $head = html_writer::tag('th', 'Student');
    foreach ($courses as $cid => $course) {
        $head .= html_writer::tag('th',
            html_writer::link(new moodle_url('/local/acculearnfeedback/completion.php',
                $baseparams + ['courseid' => $cid]), s($course->label)),
            ['title' => format_string($course->fullname), 'scope' => 'col']);
    }
    $head .= html_writer::tag('th', 'Overall', ['scope' => 'col']);

    $body = '';
    foreach ($students as $u) {
        $row = html_writer::tag('td', s($displayname($u)));
        $tdone = $ttotal = 0;
        foreach ($courses as $cid => $course) {
            if (empty($enrolledin[$u->id][$cid])) {
                $row .= html_writer::tag('td', '&mdash;', ['class' => 'al-cmpl__na', 'title' => 'Not enrolled']);
                continue;
            }
            $n = $coursecount($u->id, $cid);
            $total = count($activities[$cid]);
            $tdone += $n;
            $ttotal += $total;
            $cell = $n == $total ? $yes . ' ' : ($n == 0 ? $no('Nothing done') . ' ' : '');
            $cls = $n == $total ? ' al-cmpl__count--full' : ($n == 0 ? ' al-cmpl__count--none' : '');
            $row .= html_writer::tag('td', $cell . $n . '/' . $total, ['class' => 'al-cmpl__count' . $cls]);
        }
        $pct = $ttotal ? round(100 * $tdone / $ttotal) : 0;
        $row .= html_writer::tag('td', $tdone . '/' . $ttotal . ' (' . $pct . '%)', ['class' => 'al-cmpl__count']);
        $body .= html_writer::tag('tr', $row);
    }

    echo html_writer::div(
        html_writer::tag('table',
            html_writer::tag('thead', html_writer::tag('tr', $head)) .
            html_writer::tag('tbody', $body),
            ['class' => 'generaltable al-cmpl', 'style' => 'width:100%;']),
        'al-table-wrap');

    // Key to the course codes, since the headers are abbreviated.
    $key = '';
    foreach ($courses as $course) {
        $key .= html_writer::tag('li', html_writer::tag('strong', s($course->label)) . ' &ndash; '
            . format_string($course->fullname) . ' (' . count($activities[$course->id]) . ' activities)');
    }
    echo html_writer::tag('ul', $key, ['style' => 'color:var(--al-ink-500); font-size:0.85em; margin-top:12px;']);
}

echo html_writer::end_div();

echo $OUTPUT->footer();
