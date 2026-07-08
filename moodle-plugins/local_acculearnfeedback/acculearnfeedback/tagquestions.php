<?php
require_once(__DIR__ . '/../../config.php');
require_login();

global $DB, $OUTPUT, $PAGE;

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url('/local/acculearnfeedback/tagquestions.php');
$PAGE->set_context($context);
$PAGE->set_title('Tag Questions to Competencies');
$PAGE->set_heading('Tag Questions to Competencies');

// Handle form submission
if (data_submitted() && confirm_sesskey()) {
    $questionids = required_param_array('questionid', PARAM_INT);
    foreach ($questionids as $qid) {
        $competencyid = optional_param("competency_$qid", 0, PARAM_INT);
        if ($competencyid > 0) {
            $existing = $DB->get_record_sql(
                "SELECT id FROM acculearn_question_competency WHERE questionid = ?", [$qid]
            );
            if ($existing) {
                $DB->execute(
                    "UPDATE acculearn_question_competency SET competency_id = ? WHERE questionid = ?",
                    [$competencyid, $qid]
                );
            } else {
                $DB->execute(
                    "INSERT INTO acculearn_question_competency (questionid, competency_id) VALUES (?, ?)",
                    [$qid, $competencyid]
                );
            }
        }
    }
    redirect($PAGE->url, 'Tags saved successfully.', null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading('Tag Questions to Competencies');

$competencies = $DB->get_records_sql("SELECT * FROM acculearn_competencies ORDER BY sequence");

$questions = $DB->get_records_sql("
    SELECT q.id, q.name, q.questiontext
    FROM {question} q
    JOIN {question_versions} qv ON qv.questionid = q.id
    JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
    WHERE q.qtype = 'multichoice'
    ORDER BY q.id
");

$currentTags = $DB->get_records_sql("SELECT questionid, competency_id FROM acculearn_question_competency");
$tagMap = [];
foreach ($currentTags as $t) {
    $tagMap[$t->questionid] = $t->competency_id;
}

echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%']);
echo html_writer::start_tag('thead');
echo html_writer::tag('tr',
    html_writer::tag('th', 'Question') . html_writer::tag('th', 'Text') . html_writer::tag('th', 'Competency')
);
echo html_writer::end_tag('thead');
echo html_writer::start_tag('tbody');

foreach ($questions as $q) {
    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', s($q->name) .
        html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'questionid[]', 'value' => $q->id])
    );
    echo html_writer::tag('td', strip_tags($q->questiontext), ['style' => 'max-width:400px;']);

    echo html_writer::start_tag('td');
    echo html_writer::start_tag('select', ['name' => "competency_{$q->id}"]);
    echo html_writer::tag('option', '-- none --', ['value' => 0]);
    foreach ($competencies as $c) {
        $selected = (isset($tagMap[$q->id]) && $tagMap[$q->id] == $c->id) ? ['selected' => 'selected'] : [];
        echo html_writer::tag('option', $c->name, array_merge(['value' => $c->id], $selected));
    }
    echo html_writer::end_tag('select');
    echo html_writer::end_tag('td');

    echo html_writer::end_tag('tr');
}

echo html_writer::end_tag('tbody');
echo html_writer::end_tag('table');

echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => 'Save Tags', 'class' => 'btn btn-primary', 'style' => 'margin-top:16px;']);
echo html_writer::end_tag('form');

echo $OUTPUT->footer();
