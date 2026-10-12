<?php
/**
 * Student-facing pre-test.
 *
 * The pre-test used to exist only on paper: staff transcribed each student's
 * answers through pretest_entry.php. This page lets a student sit the same
 * instrument themselves, which is what the system diagram always described.
 *
 * The item set is deliberately NOT chosen here - it comes from
 * local_acculearnfeedback_pretest_items(), the same helper the staff entry
 * screen uses, so the two can never drift apart.
 *
 * Which competency an item belongs to is never rendered, and never travels
 * through the form. It is looked up server-side from acculearn_question_competency
 * at save time, so a student cannot see - or tamper with - the mapping that
 * their placement is calculated from.
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

global $DB, $OUTPUT, $PAGE, $USER;

$context = context_system::instance();
require_capability('local/acculearnfeedback:viewowntrack', $context);

$PAGE->set_url('/local/acculearnfeedback/pretest_take.php');
$PAGE->set_context($context);
$PAGE->set_title('Pre-Test');
$PAGE->set_heading($SITE->fullname);

$trackurl = new moodle_url('/local/acculearnfeedback/my_track.php');

// Staff hold viewowntrack implicitly (a site admin passes every capability
// check) but have no placement to establish, so they are sent straight back.
if (local_acculearnfeedback_is_staff_account()) {
    redirect($trackurl, 'The pre-test is taken by students only.', null,
        \core\output\notification::NOTIFY_INFO);
}

// The pre-test is a baseline: it is sat once and never retaken. Re-sitting it
// after instruction would overwrite the very measurement the track is built on.
$already = $DB->get_field_sql(
    "SELECT COUNT(*) FROM acculearn_pretest_responses WHERE userid = ?", [$USER->id]);
if ($already) {
    redirect($trackurl, 'You have already completed the pre-test.', null,
        \core\output\notification::NOTIFY_INFO);
}

// ---------------------------------------------------------------- item set
// Competency order fixes the sequence so every student sees the same paper,
// but nothing about the competency is shown or submitted.
$items = [];
foreach ($DB->get_records_sql("SELECT * FROM acculearn_competencies ORDER BY sequence") as $comp) {
    foreach (local_acculearnfeedback_pretest_items($comp->id) as $q) {
        $items[$q->id] = $q;
    }
}

if (empty($items)) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading('Pre-Test');
    echo $OUTPUT->notification('No pre-test items are configured. Please tell your teacher.',
        \core\output\notification::NOTIFY_ERROR);
    echo $OUTPUT->footer();
    exit;
}

$confidencelabels = [
    1 => 'Unsure',
    2 => 'Fairly sure',
    3 => 'Quite sure',
];

// ---------------------------------------------------------------- submission
$errors = [];
$submitted = [];

if (optional_param('submitpretest', 0, PARAM_INT) && confirm_sesskey()) {

    foreach ($items as $qid => $q) {
        $submitted[$qid] = [
            'answerid'   => optional_param('answer_' . $qid, 0, PARAM_INT),
            'confidence' => optional_param('confidence_' . $qid, 0, PARAM_INT),
        ];
        if (empty($submitted[$qid]['answerid'])) {
            $errors[$qid]['answer'] = true;
        }
        if (!isset($confidencelabels[$submitted[$qid]['confidence']])) {
            $errors[$qid]['confidence'] = true;
        }
    }

    if (empty($errors)) {
        $now = time();
        $saved = 0;

        foreach ($items as $qid => $q) {
            // Grade from the question bank, never from anything the browser sent.
            $fraction = $DB->get_field('question_answers', 'fraction',
                ['id' => $submitted[$qid]['answerid'], 'question' => $qid]);
            if ($fraction === false) {
                // The option does not belong to this question - treat as wrong
                // rather than trusting it or failing the whole submission.
                $fraction = 0;
            }
            $iscorrect = ($fraction > 0) ? 1 : 0;

            // Authoritative mapping, looked up here rather than carried in the form.
            $competencyid = $DB->get_field_sql(
                "SELECT competency_id FROM acculearn_question_competency WHERE questionid = ?", [$qid]);
            if (!$competencyid) {
                continue;   // Unmapped item contributes to no competency.
            }

            $DB->execute(
                "INSERT INTO acculearn_pretest_responses
                     (userid, questionid, competency_id, confidence, is_correct, entered_at)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$USER->id, $qid, $competencyid, $submitted[$qid]['confidence'], $iscorrect, $now]);
            $saved++;
        }

        local_acculearnfeedback_log_usage($USER->id, 'pretest_submitted', 'pretest', null,
            "Completed the pre-test online ($saved items)");

        // Queued, not called inline - see generate_track_task for why.
        $task = new \local_acculearnfeedback\task\generate_track_task();
        $task->set_custom_data(['userid' => $USER->id]);
        \core\task\manager::queue_adhoc_task($task);

        redirect($trackurl,
            'Pre-test submitted. Your learning track is being prepared and will appear here shortly.',
            null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

// ---------------------------------------------------------------- the form
echo $OUTPUT->header();
echo $OUTPUT->heading('Pre-Test');

if ($errors) {
    echo $OUTPUT->notification(
        'Every question needs both an answer and a confidence rating. ' .
        count($errors) . ' of ' . count($items) . ' are still incomplete - they are marked below.',
        \core\output\notification::NOTIFY_ERROR);
}

echo html_writer::start_div('al-page');

echo html_writer::div(
    html_writer::tag('p', 'This short test finds out what you already know, so the system can '
        . 'build a learning path that fits you. It is not graded and it does not affect your marks.')
    . html_writer::tag('p', 'For each question, choose your answer and then say how sure you are. '
        . 'Being honest about "Unsure" is genuinely useful - a lucky guess and a confident '
        . 'answer mean very different things, and saying so helps the system tell them apart.')
    . html_writer::tag('p', html_writer::tag('strong',
        'Answer all ' . count($items) . ' questions, then submit once at the bottom. '
        . 'You take this only once.')),
    'al-card', ['style' => 'padding:16px 20px;']);

echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'submitpretest', 'value' => 1]);

$position = 0;
foreach ($items as $qid => $q) {
    $position++;
    $haserror = isset($errors[$qid]);

    $style = 'padding:16px 20px; margin-top:14px;'
        . ($haserror ? ' border-left:4px solid var(--al-danger, #b3261e);' : '');
    echo html_writer::start_div('al-card', ['style' => $style]);

    echo html_writer::tag('div', 'Question ' . $position . ' of ' . count($items),
        ['style' => 'font-size:0.82em; letter-spacing:.06em; text-transform:uppercase;'
            . ' color:var(--al-ink-500); margin-bottom:6px;']);

    echo html_writer::div(format_text($q->questiontext, FORMAT_HTML),
        '', ['style' => 'margin-bottom:12px;']);

    // Answer options, in stored order, keyed by the real answer id.
    echo html_writer::start_div('', ['style' => 'margin-bottom:14px;']);
    foreach ($DB->get_records('question_answers', ['question' => $qid], 'id') as $a) {
        $inputid = 'a_' . $qid . '_' . $a->id;
        $checked = (($submitted[$qid]['answerid'] ?? 0) == $a->id) ? ['checked' => 'checked'] : [];
        echo html_writer::start_tag('div', ['style' => 'margin:5px 0;']);
        echo html_writer::empty_tag('input', array_merge([
            'type' => 'radio', 'name' => 'answer_' . $qid, 'value' => $a->id, 'id' => $inputid,
        ], $checked));
        echo html_writer::tag('label', ' ' . format_text($a->answer, FORMAT_HTML,
            ['para' => false]), ['for' => $inputid, 'style' => 'margin-left:6px;']);
        echo html_writer::end_tag('div');
    }
    echo html_writer::end_div();

    echo html_writer::tag('div', 'How sure are you?',
        ['style' => 'font-size:0.88em; color:var(--al-ink-700); margin-bottom:4px;']);
    echo html_writer::start_div('', ['style' => 'display:flex; gap:18px; flex-wrap:wrap;']);
    foreach ($confidencelabels as $level => $label) {
        $inputid = 'c_' . $qid . '_' . $level;
        $checked = (($submitted[$qid]['confidence'] ?? 0) == $level) ? ['checked' => 'checked'] : [];
        echo html_writer::start_tag('span');
        echo html_writer::empty_tag('input', array_merge([
            'type' => 'radio', 'name' => 'confidence_' . $qid, 'value' => $level, 'id' => $inputid,
        ], $checked));
        echo html_writer::tag('label', ' ' . $label, ['for' => $inputid, 'style' => 'margin-left:5px;']);
        echo html_writer::end_tag('span');
    }
    echo html_writer::end_div();

    echo html_writer::end_div();
}

echo html_writer::div(
    html_writer::empty_tag('input', [
        'type' => 'submit', 'value' => 'Submit pre-test', 'class' => 'al-btn al-btn-success']),
    '', ['style' => 'margin:22px 0 40px;']);

echo html_writer::end_tag('form');
echo html_writer::end_div();

echo $OUTPUT->footer();
