<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

global $DB, $OUTPUT, $PAGE, $USER;

$context = context_system::instance();
require_capability('local/acculearnfeedback:reviewaioutput', $context);

// Anonymization: names render as pseudonyms by default. Only users with the
// dedicated reveal capability (managers by default, not plain teachers) may
// de-anonymize, and the check is enforced server-side, not just hidden.
$canreveal = has_capability('local/acculearnfeedback:revealstudentnames', $context);
$revealnames = $canreveal ? optional_param('reveal', 0, PARAM_BOOL) : 0;

$PAGE->set_url('/local/acculearnfeedback/teacher_review.php');
$PAGE->set_context($context);
$PAGE->set_title('AI Output Review');
// Boost paints $PAGE->set_heading() in the full-width page-header region,
// which is a different element from the $OUTPUT->heading() printed at the top
// of the content column - so passing the page's own title to both rendered it
// twice, one above the other. The page heading is meant to name the SITE.
$PAGE->set_heading($SITE->fullname);

// Handle POST actions. Dispatched on 'formaction' since the review form and
// the grade-override form carry different, non-overlapping fields.
if (data_submitted() && confirm_sesskey()) {
    $formaction = required_param('formaction', PARAM_ALPHA); // 'review' or 'gradeoverride'

    if ($formaction === 'gradeoverride') {
        $feedbackid = required_param('feedbackid', PARAM_INT);
        $newgrade = required_param('newgrade', PARAM_FLOAT);
        $gradenote = optional_param('gradenote', '', PARAM_TEXT);

        $gc = local_acculearnfeedback_grade_context($feedbackid);
        if (!$gc) {
            redirect($PAGE->url, 'Could not resolve the quiz grade for this feedback entry.',
                null, \core\output\notification::NOTIFY_ERROR);
        }

        // Grade-edit capability is course-scoped: having page access (a
        // system-level capability) does not imply the right to edit grades
        // in every course, since this deployment spans multiple courses
        // (one per competency).
        $coursecontext = context_course::instance($gc->courseid);
        require_capability('moodle/grade:edit', $coursecontext);

        if ($newgrade < $gc->gradeitem->grademin || $newgrade > $gc->gradeitem->grademax) {
            redirect($PAGE->url,
                "Grade must be between {$gc->gradeitem->grademin} and {$gc->gradeitem->grademax}.",
                null, \core\output\notification::NOTIFY_ERROR);
        }

        $gc->gradeitem->update_final_grade($gc->attempt->userid, $newgrade, 'editgrade', $gradenote, FORMAT_PLAIN);

        // Mark as overridden so the quiz module's own regrade logic (e.g. if
        // the student's attempt is ever reopened/regraded) does not silently
        // clobber the teacher's manual grade.
        $gradegrade = new grade_grade(['userid' => $gc->attempt->userid, 'itemid' => $gc->gradeitem->id], true);
        $gradegrade->grade_item =& $gc->gradeitem;
        $gradegrade->set_overridden(true);

        local_acculearnfeedback_log_review('feedback', $feedbackid, $gc->attempt->userid, $USER->id,
            'gradeoverride', trim($gradenote . ' (new grade: ' . $newgrade . ')'));

        redirect($PAGE->url, 'Grade overridden successfully.', null, \core\output\notification::NOTIFY_SUCCESS);
    }

    // Default: AI-output review action. Approve is the only one.
    $type = required_param('type', PARAM_ALPHA); // 'feedback' or 'track'
    $id = required_param('id', PARAM_INT);
    $action = required_param('reviewaction', PARAM_ALPHA); // 'approve'
    $note = optional_param('teachernote', '', PARAM_TEXT);

    // Both values must be validated against an explicit allowlist, not
    // funnelled through a two-branch ternary. Previously ANY unrecognised
    // $type silently fell through to the 'track' branch (so type=xyz&id=5
    // would mark LEARNING TRACK 5 reviewed, even though the caller asked
    // about feedback 5 - a wrong-record write), and any unrecognised
    // $action silently meant "approve", so a malformed request could
    // approve AI output that nobody actually reviewed.
    if (!in_array($type, ['feedback', 'track'], true)) {
        redirect($PAGE->url, 'Unrecognised review target.', null, \core\output\notification::NOTIFY_ERROR);
    }
    // Approve is the only review action. Flagging was removed: it withheld the
    // AI output from the student but changed nothing they could act on - the
    // teacher's note never reached them, and the mastery and pathway the AI had
    // decided stayed exactly as they were. A note plus a grade override plus
    // approve does everything flagging did, visibly, and without leaving a
    // student staring at a withheld page they cannot resolve themselves.
    if ($action !== 'approve') {
        redirect($PAGE->url, 'Unrecognised review action.', null, \core\output\notification::NOTIFY_ERROR);
    }

    $table = ($type === 'feedback') ? 'acculearn_feedback' : 'acculearn_learning_tracks';
    $targetuserid = $DB->get_field_sql("SELECT userid FROM $table WHERE id = ?", [$id]);

    // flagged is written as 0 rather than left alone, so approving a record
    // that carried a legacy flag also clears it. The column itself is kept:
    // dropping it would need a schema upgrade, and acculearn_feedback /
    // acculearn_learning_tracks are the unprefixed legacy tables Moodle's
    // upgrade machinery does not manage.
    $DB->execute(
        "UPDATE $table SET reviewed = 1, reviewed_by = ?, reviewed_at = ?, teacher_note = ?, flagged = 0 WHERE id = ?",
        [$USER->id, time(), $note, $id]
    );

    if ($targetuserid) {
        local_acculearnfeedback_log_review($type, $id, $targetuserid, $USER->id, $action, $note);
    }

    redirect($PAGE->url, 'Review saved.', null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading('AI Output Review');

$tab = optional_param('tab', 'feedback', PARAM_ALPHA);

// Both queues were hard-capped at "LIMIT 50" with no way to reach anything
// older, so once a class had generated more than 50 items the rest of the
// queue was simply unreachable - including anything still pending review.
$page = optional_param('page', 0, PARAM_INT);
$perpage = 20;
$pagingbaseurl = new moodle_url('/local/acculearnfeedback/teacher_review.php',
    ['tab' => $tab] + ($revealnames ? ['reveal' => 1] : []));

// ---- Students stuck on a quiz ----
//
// Now that mastery requires PASSING every quiz, and each quiz allows only
// three attempts, a student who cannot clear one 5-question formative would
// be frozen out of the summative, the competency, and every competency after
// it - permanently, with no in-system recovery. The agreed escape hatch is a
// teacher granting extra attempts (Moodle user override), which only works if
// the teacher can see who is stuck. This is that list.
$stuck = $DB->get_records_sql("
    SELECT qa.userid, qa.quiz, u.firstname, u.lastname, q.name AS quizname,
           c.fullname AS coursename, q.attempts AS maxattempts,
           COUNT(*) AS attemptsused,
           MAX(gg.finalgrade) AS bestgrade, MAX(gi.gradepass) AS gradepass
      FROM {quiz_attempts} qa
      JOIN {quiz} q  ON q.id = qa.quiz
      JOIN {user} u  ON u.id = qa.userid
      JOIN {course} c ON c.id = q.course
      JOIN {grade_items} gi ON gi.iteminstance = q.id AND gi.itemmodule = 'quiz'
       AND gi.itemtype = 'mod' AND gi.courseid = q.course
 LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = qa.userid
     WHERE qa.state = 'finished' AND qa.preview = 0 AND q.attempts > 0
  GROUP BY qa.userid, qa.quiz, u.firstname, u.lastname, q.name, c.fullname, q.attempts
    HAVING COUNT(*) >= q.attempts
       AND (MAX(gg.finalgrade) IS NULL OR MAX(gg.finalgrade) < MAX(gi.gradepass))
  ORDER BY c.fullname, q.name");

if ($stuck) {
    echo html_writer::start_div('al-card al-card--flag-needs', ['style' => 'margin-bottom:20px;']);
    echo html_writer::tag('h4',
        '<i class="fa fa-triangle-exclamation" aria-hidden="true"></i>Students out of attempts ('
        . count($stuck) . ')', ['style' => 'margin-top:0;']);
    echo html_writer::tag('p',
        'These students have used every attempt on a quiz without reaching its passing mark, so the '
        . 'competency - and everything after it - stays locked for them. Grant extra attempts from the '
        . 'quiz\'s <em>User overrides</em> page once you are satisfied they have restudied.',
        ['style' => 'color:var(--al-ink-500); font-size:0.9em;']);
    echo html_writer::start_div('al-table-wrap');
    echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%;']);
    echo html_writer::tag('tr',
        html_writer::tag('th', 'Student') . html_writer::tag('th', 'Competency course') .
        html_writer::tag('th', 'Quiz') . html_writer::tag('th', 'Attempts') .
        html_writer::tag('th', 'Best / needed') . html_writer::tag('th', ''));
    // One query for every quiz in the table, instead of calling
    // get_coursemodule_from_instance() once per row inside the loop.
    $stuckquizids = array_unique(array_map(function($s) { return $s->quiz; }, $stuck));
    list($qinsql, $qinparams) = $DB->get_in_or_equal($stuckquizids);
    $cmids = $DB->get_records_sql_menu("
        SELECT cm.instance, cm.id
          FROM {course_modules} cm
          JOIN {modules} m ON m.id = cm.module AND m.name = 'quiz'
         WHERE cm.instance $qinsql", $qinparams);

    foreach ($stuck as $s) {
        $who = $revealnames
            ? s($s->firstname . ' ' . $s->lastname)
            : local_acculearnfeedback_pseudonym($s->userid);
        $overrideurl = isset($cmids[$s->quiz])
            ? new moodle_url('/mod/quiz/overrides.php', ['cmid' => $cmids[$s->quiz], 'mode' => 'user'])
            : null;
        echo html_writer::tag('tr',
            html_writer::tag('td', $who) .
            html_writer::tag('td', s($s->coursename)) .
            html_writer::tag('td', s($s->quizname)) .
            html_writer::tag('td', html_writer::tag('span', $s->attemptsused . '/' . $s->maxattempts,
                ['class' => 'tnum'])) .
            html_writer::tag('td', html_writer::tag('span',
                ($s->bestgrade === null ? '-' : round($s->bestgrade, 2)) . ' / ' . round($s->gradepass, 2),
                ['class' => 'tnum'])) .
            html_writer::tag('td', $overrideurl
                ? html_writer::link($overrideurl, 'Grant attempts',
                    ['class' => 'al-btn al-btn-sm al-btn-secondary'])
                : '')
        );
    }
    echo html_writer::end_tag('table');
    echo html_writer::end_div();
    echo html_writer::end_div();
}

echo html_writer::start_tag('div', ['style' => 'margin-bottom:20px;']);
echo html_writer::link(new moodle_url('/local/acculearnfeedback/teacher_review.php', ['tab' => 'feedback']),
    'Post-Quiz Feedback', ['class' => 'al-btn ' . ($tab === 'feedback' ? 'al-btn-primary' : 'al-btn-outline'), 'style' => 'margin-right:8px;']);
echo html_writer::link(new moodle_url('/local/acculearnfeedback/teacher_review.php', ['tab' => 'tracks']),
    'Learning Tracks', ['class' => 'al-btn ' . ($tab === 'tracks' ? 'al-btn-primary' : 'al-btn-outline')]);
echo html_writer::end_tag('div');

// Reveal / hide toggle (admins & managers only).
if ($canreveal) {
    $toggleurl = new moodle_url('/local/acculearnfeedback/teacher_review.php',
        ['tab' => $tab, 'reveal' => $revealnames ? 0 : 1]);
    // flex-wrap: the button label plus the status text runs to roughly 390px,
    // which overflowed a phone viewport and forced the whole page to scroll
    // sideways. Every other flex row on this page already wraps.
    echo html_writer::start_div('', ['style' => 'margin-bottom:16px; display:flex; flex-wrap:wrap; align-items:center; gap:10px;']);
    echo html_writer::link($toggleurl,
        ($revealnames ? 'Hide names (show pseudonyms)' : 'Reveal real names'),
        ['class' => 'al-btn al-btn-sm al-btn-secondary']);
    echo html_writer::tag('span',
        ($revealnames ? 'Showing real names' : 'Student names are anonymized'),
        ['style' => 'color:var(--al-ink-500); font-size:0.85em;']);
    echo html_writer::end_div();
}

if ($tab === 'feedback') {
    // Unreviewed first: the queue exists to be worked through, and burying
    // pending items under already-approved ones made that harder as it grew.
    $total = $DB->count_records_sql(
        "SELECT COUNT(*) FROM acculearn_feedback f JOIN {user} u ON u.id = f.userid");
    $rows = $DB->get_records_sql("
        SELECT f.*, u.firstname, u.lastname
        FROM acculearn_feedback f
        JOIN {user} u ON u.id = f.userid
        ORDER BY f.reviewed ASC, f.timecreated DESC
    ", null, $page * $perpage, $perpage);

    // Resolve every grade context for this page in a fixed number of queries
    // rather than one lookup per card.
    local_acculearnfeedback_preload_grade_contexts(array_keys($rows));

    echo $OUTPUT->paging_bar($total, $page, $perpage, $pagingbaseurl);

    foreach ($rows as $r) {
        $feedback = json_decode($r->feedback_json, true);
        $statustone = $r->reviewed ? 'approved' : 'pending';
        $statuslabel = $r->reviewed ? 'Approved' : 'Pending review';

        $displayname = $revealnames
            ? s($r->firstname . ' ' . $r->lastname)
            : local_acculearnfeedback_pseudonym($r->userid);

        echo html_writer::start_div("al-card al-card--flag-$statustone");

        echo html_writer::start_div('', ['style' => 'display:flex; flex-wrap:wrap; justify-content:space-between; align-items:baseline; gap:8px;']);
        echo html_writer::tag('div', html_writer::tag('strong', $displayname) . ' — ' . s($r->quizname));
        echo html_writer::tag('span', $statuslabel, ['class' => "al-badge al-badge--$statustone"]);
        echo html_writer::end_div();
        echo html_writer::tag('p',
            'CM: ' . html_writer::tag('span', $r->cm, ['class' => 'tnum']) . '%  ' .
            'CMI: ' . html_writer::tag('span', $r->cmi, ['class' => 'tnum']) . '  ' .
            'GI: ' . html_writer::tag('span', $r->gi, ['class' => 'tnum']),
            ['style' => 'color:var(--al-ink-500); font-size:0.9em;']);

        // The AI writes to the student by name, so the prose must be scrubbed
        // too - anonymising only the heading left "Hi Kristine!" sitting under
        // a "Student-A4E0" label.
        // Two eras of stored content. Records written since September 2026
        // carry the [STUDENT] token and only need substituting; older ones
        // carry the real name and still need scrubbing. Doing both in order
        // handles either without having to know which is which.
        $deid = function($text) use ($revealnames, $r, $displayname) {
            $text = local_acculearnfeedback_personalise((string)$text, $displayname);
            return $revealnames ? $text
                : local_acculearnfeedback_deidentify($text, $r, $displayname);
        };

        echo html_writer::tag('p', s($deid($feedback['summary'] ?? '')), ['style' => 'margin-top:8px;']);

        if (!empty($feedback['misconceptions'])) {
            echo html_writer::tag('strong', 'Misconceptions:');
            echo html_writer::start_tag('ul');
            foreach ($feedback['misconceptions'] as $m) echo html_writer::tag('li', s($deid($m)));
            echo html_writer::end_tag('ul');
        }

        if ($r->teacher_note) {
            echo html_writer::div('Teacher note: ' . s($r->teacher_note), '',
                ['style' => 'background:var(--al-ink-50); border:1px solid var(--al-ink-200); border-radius:8px; padding:10px 14px; margin-top:8px; font-size:0.9em; color:var(--al-ink-700);']);
        }

        // Grade override: only shown when the teacher can edit grades in the
        // specific course this quiz belongs to (grade capabilities are
        // course-scoped, unlike this page's system-level access gate).
        $gc = local_acculearnfeedback_grade_context($r->id);
        if ($gc && has_capability('moodle/grade:edit', context_course::instance($gc->courseid))) {
            $currentgrade = $gc->gradegrade->finalgrade;
            $overridden = $gc->gradegrade->overridden > 0;

            echo html_writer::start_div('', ['style' => 'margin-top:12px; padding-top:12px; border-top:1px solid #eee;']);
            echo html_writer::tag('strong', 'Quiz grade: ') .
                html_writer::tag('span',
                    ($currentgrade !== null ? number_format($currentgrade, 2) : 'Not graded') .
                    ' / ' . number_format($gc->gradeitem->grademax, 2),
                    ['class' => 'tnum']) .
                ($overridden ? html_writer::tag('span', ' Overridden',
                    ['style' => 'color:var(--al-red-ink); font-size:0.85em; font-weight:600;']) : '');

            echo html_writer::start_tag('form', [
                'method' => 'post', 'action' => $PAGE->url,
                'style' => 'margin-top:8px; display:flex; flex-wrap:wrap; gap:8px; align-items:center;',
                'onsubmit' => "return confirm('Override this student\\'s quiz grade? This directly changes their gradebook grade.');",
            ]);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'formaction', 'value' => 'gradeoverride']);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'feedbackid', 'value' => $r->id]);
            echo html_writer::empty_tag('input', [
                'type' => 'number', 'step' => '0.01',
                'min' => $gc->gradeitem->grademin, 'max' => $gc->gradeitem->grademax,
                'name' => 'newgrade', 'value' => $currentgrade !== null ? round($currentgrade, 2) : '',
                'style' => 'width:90px;',
            ]);
            echo html_writer::empty_tag('input', [
                'type' => 'text', 'name' => 'gradenote', 'placeholder' => 'Reason (optional, shown to student)',
                'style' => 'flex:1; max-width:300px;',
            ]);
            echo html_writer::empty_tag('input', [
                'type' => 'submit', 'value' => 'Update Grade', 'class' => 'al-btn al-btn-sm al-btn-warning',
            ]);
            echo html_writer::end_tag('form');
            echo html_writer::end_div();
        }

        if (!$r->reviewed) {
            echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url, 'style' => 'margin-top:12px;']);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'formaction', 'value' => 'review']);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'type', 'value' => 'feedback']);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $r->id]);
            echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'teachernote', 'placeholder' => 'Optional note...', 'style' => 'width:60%; margin-right:8px;']);
            echo html_writer::empty_tag('input', ['type' => 'submit', 'name' => 'reviewaction', 'value' => 'approve', 'class' => 'al-btn al-btn-sm al-btn-success']);
            echo html_writer::end_tag('form');
        }

        echo html_writer::end_div();
    }

    echo $OUTPUT->paging_bar($total, $page, $perpage, $pagingbaseurl);

    if (empty($rows)) {
        echo html_writer::div(
            html_writer::div('<i class="fa fa-comment-dots" aria-hidden="true"></i>', 'al-empty__icon') .
            html_writer::div('No AI feedback yet', 'al-empty__title') .
            html_writer::div('Feedback appears here once a student submits a quiz.', 'al-empty__sub'),
            'al-card al-empty'
        );
    }

} else {
    $total = $DB->count_records_sql(
        "SELECT COUNT(*) FROM acculearn_learning_tracks t JOIN {user} u ON u.id = t.userid");
    $rows = $DB->get_records_sql("
        SELECT t.*, u.firstname, u.lastname
        FROM acculearn_learning_tracks t
        JOIN {user} u ON u.id = t.userid
        ORDER BY t.reviewed ASC, t.generated_at DESC
    ", null, $page * $perpage, $perpage);

    echo $OUTPUT->paging_bar($total, $page, $perpage, $pagingbaseurl);

    foreach ($rows as $r) {
        // Same defensive decode as my_track.php: one malformed row must not
        // take down the whole review queue for the teacher.
        $track = json_decode($r->track_json, true);
        if (!is_array($track)) {
            continue;
        }
        $narrative = $track['narrative'] ?? [];
        $statustone = $r->reviewed ? 'approved' : 'pending';
        $statuslabel = $r->reviewed ? 'Approved' : 'Pending review';

        $displayname = $revealnames
            ? s($r->firstname . ' ' . $r->lastname)
            : local_acculearnfeedback_pseudonym($r->userid);

        echo html_writer::start_div("al-card al-card--flag-$statustone");

        echo html_writer::start_div('', ['style' => 'display:flex; flex-wrap:wrap; justify-content:space-between; align-items:baseline; gap:8px;']);
        echo html_writer::tag('strong', $displayname);
        echo html_writer::tag('span', $statuslabel, ['class' => "al-badge al-badge--$statustone"]);
        echo html_writer::end_div();

        $overview = local_acculearnfeedback_personalise($narrative['overview'] ?? '', $displayname);
        if (!$revealnames) {
            $overview = local_acculearnfeedback_deidentify($overview, $r, $displayname);
        }
        echo html_writer::tag('p', s($overview), ['style' => 'margin-top:8px;']);

        // Who decided this track. An AI-decided track carries the model's
        // own reasoning per competency, shown in the last column so the
        // teacher can check a pathway against the evidence it cites.
        $decidedbyai = (($track['decision']['by'] ?? null) === 'ai');
        $decisionnote = $decidedbyai
            ? 'Mastery, pathway and order decided by the AI model ('
                . s($track['decision']['model'] ?? 'model') . ') on '
                . userdate($track['decision']['decided_at'] ?? $r->generated_at, get_string('strftimedatetimeshort'))
            : 'Mastery and pathway decided by the rule-based formula'
                . (!empty($track['decision']['fallback_reason'])
                    ? ' (AI decision unavailable at the time)' : '');
        echo html_writer::tag('div', $decisionnote,
            ['style' => 'color:var(--al-ink-500); font-size:0.8em; margin-top:4px;']);

        echo html_writer::start_div('al-table-wrap', ['style' => 'margin-top:8px;']);
        echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%;']);
        echo html_writer::tag('tr', html_writer::tag('th', 'Competency') . html_writer::tag('th', 'CM')
            . html_writer::tag('th', 'Mastery') . html_writer::tag('th', 'Pathway')
            . ($decidedbyai ? html_writer::tag('th', 'AI rationale') : ''));
        foreach (($track['competencies'] ?? []) as $c) {
            echo html_writer::tag('tr',
                html_writer::tag('td', s($c['competency_name'])) .
                html_writer::tag('td',
                    // Was "$c['cm'] ?? 'N/A'" followed by a literal '%',
                    // which rendered the nonsense string "N/A%" for any
                    // competency the student has not attempted yet.
                    ($c['cm'] !== null
                        ? html_writer::tag('span', $c['cm'], ['class' => 'tnum']) . '%'
                        : html_writer::tag('span', 'Not attempted',
                            ['style' => 'color:var(--al-ink-500); font-size:0.9em;'])) .
                    html_writer::div(local_acculearnfeedback_progress_bar($c['cm'] ?? null, $c['mastery']), '', ['style' => 'min-width:90px;'])
                ) .
                html_writer::tag('td', s($c['mastery'])) .
                html_writer::tag('td', local_acculearnfeedback_lp_badge($c['lp'] ?? null, $c['lp_label'])) .
                ($decidedbyai
                    ? html_writer::tag('td', s($c['rationale'] ?? ''),
                        ['style' => 'font-size:0.85em; color:var(--al-ink-700);'])
                    : '')
            );
        }
        echo html_writer::end_tag('table');
        echo html_writer::end_div();

        if ($r->teacher_note) {
            echo html_writer::div('Teacher note: ' . s($r->teacher_note), '',
                ['style' => 'background:var(--al-ink-50); border:1px solid var(--al-ink-200); border-radius:8px; padding:10px 14px; margin-top:8px; font-size:0.9em; color:var(--al-ink-700);']);
        }

        if (!$r->reviewed) {
            echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url, 'style' => 'margin-top:12px;']);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'formaction', 'value' => 'review']);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'type', 'value' => 'track']);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $r->id]);
            echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'teachernote', 'placeholder' => 'Optional note...', 'style' => 'width:60%; margin-right:8px;']);
            echo html_writer::empty_tag('input', ['type' => 'submit', 'name' => 'reviewaction', 'value' => 'approve', 'class' => 'al-btn al-btn-sm al-btn-success']);
            echo html_writer::end_tag('form');
        }

        echo html_writer::end_div();
    }

    echo $OUTPUT->paging_bar($total, $page, $perpage, $pagingbaseurl);

    if (empty($rows)) {
        echo html_writer::div(
            html_writer::div('<i class="fa fa-route" aria-hidden="true"></i>', 'al-empty__icon') .
            html_writer::div('No learning tracks yet', 'al-empty__title') .
            html_writer::div('Tracks appear here after pre-test entry or a student\'s first quiz.', 'al-empty__sub'),
            'al-card al-empty'
        );
    }
}

echo $OUTPUT->footer();
