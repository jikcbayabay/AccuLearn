<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

global $DB, $OUTPUT, $PAGE, $USER;

$context = context_system::instance();
require_capability('moodle/course:update', $context);

// Anonymization: names render as pseudonyms by default. Only admins/managers
// (moodle/role:assign at system context; plain teachers don't have it) may
// de-anonymize, and the check is enforced server-side, not just hidden.
$canreveal = has_capability('moodle/role:assign', $context);
$revealnames = $canreveal ? optional_param('reveal', 0, PARAM_BOOL) : 0;

$PAGE->set_url('/local/acculearnfeedback/teacher_review.php');
$PAGE->set_context($context);
$PAGE->set_title('AI Output Review');
$PAGE->set_heading('AI Output Review');

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

        redirect($PAGE->url, 'Grade overridden successfully.', null, \core\output\notification::NOTIFY_SUCCESS);
    }

    // Default: AI-output review action ('approve' or 'flag').
    $type = required_param('type', PARAM_ALPHA); // 'feedback' or 'track'
    $id = required_param('id', PARAM_INT);
    $action = required_param('reviewaction', PARAM_ALPHA); // 'approve' or 'flag'
    $note = optional_param('teachernote', '', PARAM_TEXT);

    $table = ($type === 'feedback') ? 'acculearn_feedback' : 'acculearn_learning_tracks';
    $flagged = ($action === 'flag') ? 1 : 0;

    $DB->execute(
        "UPDATE $table SET reviewed = 1, reviewed_by = ?, reviewed_at = ?, teacher_note = ?, flagged = ? WHERE id = ?",
        [$USER->id, time(), $note, $flagged, $id]
    );

    redirect($PAGE->url, 'Review saved.', null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading('AI Output Review');

$tab = optional_param('tab', 'feedback', PARAM_ALPHA);

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
    echo html_writer::start_div('', ['style' => 'margin-bottom:16px; display:flex; align-items:center; gap:10px;']);
    echo html_writer::link($toggleurl,
        ($revealnames ? 'Hide names (show pseudonyms)' : 'Reveal real names'),
        ['class' => 'al-btn al-btn-sm al-btn-secondary']);
    echo html_writer::tag('span',
        ($revealnames ? 'Showing real names' : 'Student names are anonymized'),
        ['style' => 'color:var(--al-ink-500); font-size:0.85em;']);
    echo html_writer::end_div();
}

if ($tab === 'feedback') {
    $rows = $DB->get_records_sql("
        SELECT f.*, u.firstname, u.lastname
        FROM acculearn_feedback f
        JOIN {user} u ON u.id = f.userid
        ORDER BY f.timecreated DESC
        LIMIT 50
    ");

    foreach ($rows as $r) {
        $feedback = json_decode($r->feedback_json, true);
        $statustone = $r->flagged ? 'flagged' : ($r->reviewed ? 'approved' : 'pending');
        $statuslabel = $r->flagged ? 'Flagged' : ($r->reviewed ? 'Approved' : 'Pending review');

        $displayname = $revealnames
            ? s($r->firstname . ' ' . $r->lastname)
            : local_acculearnfeedback_pseudonym($r->userid);

        echo html_writer::start_div("al-card al-card--flag-$statustone");

        echo html_writer::tag('div',
            html_writer::tag('strong', $displayname) . ' — ' . s($r->quizname) .
            html_writer::tag('span', $statuslabel, ['class' => "al-badge al-badge--$statustone", 'style' => 'float:right;'])
        );
        echo html_writer::tag('p', 'CM: ' . $r->cm . '%  CMI: ' . $r->cmi . '  GI: ' . $r->gi, ['style' => 'color:var(--al-ink-500); font-size:0.9em;']);

        echo html_writer::tag('p', s($feedback['summary'] ?? ''), ['style' => 'margin-top:8px;']);

        if (!empty($feedback['misconceptions'])) {
            echo html_writer::tag('strong', 'Misconceptions:');
            echo html_writer::start_tag('ul');
            foreach ($feedback['misconceptions'] as $m) echo html_writer::tag('li', s($m));
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
                ($currentgrade !== null ? number_format($currentgrade, 2) : 'Not graded') .
                ' / ' . number_format($gc->gradeitem->grademax, 2) .
                ($overridden ? html_writer::tag('span', ' Overridden',
                    ['style' => 'color:var(--al-red-ink); font-size:0.85em; font-weight:600;']) : '');

            echo html_writer::start_tag('form', [
                'method' => 'post', 'action' => $PAGE->url,
                'style' => 'margin-top:8px; display:flex; gap:8px; align-items:center;',
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
            echo html_writer::empty_tag('input', ['type' => 'submit', 'name' => 'reviewaction', 'value' => 'approve', 'class' => 'al-btn al-btn-sm al-btn-success', 'style' => 'margin-right:4px;']);
            echo html_writer::empty_tag('input', ['type' => 'submit', 'name' => 'reviewaction', 'value' => 'flag', 'class' => 'al-btn al-btn-sm al-btn-danger']);
            echo html_writer::end_tag('form');
        }

        echo html_writer::end_div();
    }

    if (empty($rows)) {
        echo html_writer::div('No AI feedback generated yet.', 'al-card', ['style' => 'color:var(--al-ink-500);']);
    }

} else {
    $rows = $DB->get_records_sql("
        SELECT t.*, u.firstname, u.lastname
        FROM acculearn_learning_tracks t
        JOIN {user} u ON u.id = t.userid
        ORDER BY t.generated_at DESC
        LIMIT 50
    ");

    foreach ($rows as $r) {
        $track = json_decode($r->track_json, true);
        $narrative = $track['narrative'];
        $statustone = $r->flagged ? 'flagged' : ($r->reviewed ? 'approved' : 'pending');
        $statuslabel = $r->flagged ? 'Flagged' : ($r->reviewed ? 'Approved' : 'Pending review');

        $displayname = $revealnames
            ? s($r->firstname . ' ' . $r->lastname)
            : local_acculearnfeedback_pseudonym($r->userid);

        echo html_writer::start_div("al-card al-card--flag-$statustone");

        echo html_writer::tag('div',
            html_writer::tag('strong', $displayname) .
            html_writer::tag('span', $statuslabel, ['class' => "al-badge al-badge--$statustone", 'style' => 'float:right;'])
        );

        echo html_writer::tag('p', s($narrative['overview']), ['style' => 'margin-top:8px;']);

        echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%; margin-top:8px;']);
        echo html_writer::tag('tr', html_writer::tag('th', 'Competency') . html_writer::tag('th', 'CM') . html_writer::tag('th', 'Mastery') . html_writer::tag('th', 'Pathway'));
        foreach ($track['competencies'] as $c) {
            echo html_writer::tag('tr',
                html_writer::tag('td', s($c['competency_name'])) .
                html_writer::tag('td', ($c['cm'] ?? 'N/A') . '%') .
                html_writer::tag('td', s($c['mastery'])) .
                html_writer::tag('td', s($c['lp_label']))
            );
        }
        echo html_writer::end_tag('table');

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
            echo html_writer::empty_tag('input', ['type' => 'submit', 'name' => 'reviewaction', 'value' => 'approve', 'class' => 'al-btn al-btn-sm al-btn-success', 'style' => 'margin-right:4px;']);
            echo html_writer::empty_tag('input', ['type' => 'submit', 'name' => 'reviewaction', 'value' => 'flag', 'class' => 'al-btn al-btn-sm al-btn-danger']);
            echo html_writer::end_tag('form');
        }

        echo html_writer::end_div();
    }

    if (empty($rows)) {
        echo html_writer::div('No learning tracks generated yet.', 'al-card', ['style' => 'color:var(--al-ink-500);']);
    }
}

echo $OUTPUT->footer();
