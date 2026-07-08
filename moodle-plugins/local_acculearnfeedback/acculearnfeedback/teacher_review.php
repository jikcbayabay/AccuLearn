<?php
require_once(__DIR__ . '/../../config.php');
require_login();

global $DB, $OUTPUT, $PAGE, $USER;

$context = context_system::instance();
require_capability('moodle/course:update', $context);

$PAGE->set_url('/local/acculearnfeedback/teacher_review.php');
$PAGE->set_context($context);
$PAGE->set_title('AI Output Review');
$PAGE->set_heading('AI Output Review');

// Handle review actions
if (data_submitted() && confirm_sesskey()) {
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
    'Post-Quiz Feedback', ['class' => 'btn ' . ($tab === 'feedback' ? 'btn-primary' : 'btn-outline-primary'), 'style' => 'margin-right:8px;']);
echo html_writer::link(new moodle_url('/local/acculearnfeedback/teacher_review.php', ['tab' => 'tracks']),
    'Learning Tracks', ['class' => 'btn ' . ($tab === 'tracks' ? 'btn-primary' : 'btn-outline-primary')]);
echo html_writer::end_tag('div');

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
        $statuscolor = $r->flagged ? '#c62828' : ($r->reviewed ? '#2e7d32' : '#999');
        $statuslabel = $r->flagged ? 'Flagged' : ($r->reviewed ? 'Approved' : 'Pending review');

        echo html_writer::start_div('', ['style' => "border:1px solid #ddd; border-left:5px solid $statuscolor; border-radius:4px; padding:16px; margin-bottom:16px;"]);

        echo html_writer::tag('div',
            html_writer::tag('strong', s($r->firstname . ' ' . $r->lastname)) . ' — ' . s($r->quizname) .
            html_writer::tag('span', $statuslabel, ['style' => "float:right; background:$statuscolor; color:white; padding:2px 10px; border-radius:10px; font-size:0.8em;"])
        );
        echo html_writer::tag('p', 'CM: ' . $r->cm . '%  CMI: ' . $r->cmi . '  GI: ' . $r->gi, ['style' => 'color:#666; font-size:0.9em;']);

        echo html_writer::tag('p', s($feedback['summary'] ?? ''), ['style' => 'margin-top:8px;']);

        if (!empty($feedback['misconceptions'])) {
            echo html_writer::tag('strong', 'Misconceptions:');
            echo html_writer::start_tag('ul');
            foreach ($feedback['misconceptions'] as $m) echo html_writer::tag('li', s($m));
            echo html_writer::end_tag('ul');
        }

        if ($r->teacher_note) {
            echo html_writer::div('Teacher note: ' . s($r->teacher_note), 'alert alert-secondary');
        }

        if (!$r->reviewed) {
            echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url]);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'type', 'value' => 'feedback']);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $r->id]);
            echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'teachernote', 'placeholder' => 'Optional note...', 'style' => 'width:60%; margin-right:8px;']);
            echo html_writer::empty_tag('input', ['type' => 'submit', 'name' => 'reviewaction', 'value' => 'approve', 'class' => 'btn btn-sm btn-success', 'style' => 'margin-right:4px;']);
            echo html_writer::empty_tag('input', ['type' => 'submit', 'name' => 'reviewaction', 'value' => 'flag', 'class' => 'btn btn-sm btn-danger']);
            echo html_writer::end_tag('form');
        }

        echo html_writer::end_div();
    }

    if (empty($rows)) {
        echo html_writer::div('No AI feedback generated yet.', 'alert alert-info');
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
        $statuscolor = $r->flagged ? '#c62828' : ($r->reviewed ? '#2e7d32' : '#999');
        $statuslabel = $r->flagged ? 'Flagged' : ($r->reviewed ? 'Approved' : 'Pending review');

        echo html_writer::start_div('', ['style' => "border:1px solid #ddd; border-left:5px solid $statuscolor; border-radius:4px; padding:16px; margin-bottom:16px;"]);

        echo html_writer::tag('div',
            html_writer::tag('strong', s($r->firstname . ' ' . $r->lastname)) .
            html_writer::tag('span', $statuslabel, ['style' => "float:right; background:$statuscolor; color:white; padding:2px 10px; border-radius:10px; font-size:0.8em;"])
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
            echo html_writer::div('Teacher note: ' . s($r->teacher_note), 'alert alert-secondary', );
        }

        if (!$r->reviewed) {
            echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url]);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'type', 'value' => 'track']);
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $r->id]);
            echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'teachernote', 'placeholder' => 'Optional note...', 'style' => 'width:60%; margin-right:8px;']);
            echo html_writer::empty_tag('input', ['type' => 'submit', 'name' => 'reviewaction', 'value' => 'approve', 'class' => 'btn btn-sm btn-success', 'style' => 'margin-right:4px;']);
            echo html_writer::empty_tag('input', ['type' => 'submit', 'name' => 'reviewaction', 'value' => 'flag', 'class' => 'btn btn-sm btn-danger']);
            echo html_writer::end_tag('form');
        }

        echo html_writer::end_div();
    }

    if (empty($rows)) {
        echo html_writer::div('No learning tracks generated yet.', 'alert alert-info');
    }
}

echo $OUTPUT->footer();
