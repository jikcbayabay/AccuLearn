<?php
require_once(__DIR__ . '/../../config.php');
require_login();

global $DB, $OUTPUT, $PAGE;

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url('/local/acculearnfeedback/pretest_entry.php');
$PAGE->set_context($context);
$PAGE->set_title('Enter Pre-Test Results');
$PAGE->set_heading('Enter Pre-Test Results');

$selecteduserid = optional_param('userid', 0, PARAM_INT);
$competencyid = optional_param('competencyid', 0, PARAM_INT);

$pretestdb = new PDO(
    "mysql:host=localhost;dbname=moodle;charset=utf8mb4",
    'moodleuser',
    file_get_contents('/var/www/html/acculearn-backend/.dbpass'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ]
);

// Handle form submission
if (data_submitted() && confirm_sesskey() && $selecteduserid && $competencyid) {
    $questionids = required_param_array('questionid', PARAM_INT);
    $now = time();

    $stmt = $pretestdb->prepare("
        INSERT INTO acculearn_pretest_responses (userid, questionid, competency_id, confidence, is_correct, entered_at)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE confidence = VALUES(confidence), is_correct = VALUES(is_correct), entered_at = VALUES(entered_at)
    ");

    $saved = 0;
    foreach ($questionids as $qid) {
        $confidence = optional_param("confidence_$qid", 0, PARAM_INT);
        $correct = optional_param("correct_$qid", -1, PARAM_INT);
        if ($confidence > 0 && $correct >= 0) {
            $stmt->execute([$selecteduserid, $qid, $competencyid, $confidence, $correct, $now]);
            $saved++;
        }
    }
    redirect(
        new moodle_url('/local/acculearnfeedback/pretest_entry.php', ['userid' => $selecteduserid, 'competencyid' => $competencyid]),
        "Saved $saved responses.",
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading('Enter Pre-Test Results');

// Student selector
$students = $DB->get_records_sql("
    SELECT DISTINCT u.id, u.firstname, u.lastname
    FROM {user} u
    JOIN {role_assignments} ra ON ra.userid = u.id
    JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
    WHERE u.deleted = 0
    ORDER BY u.lastname, u.firstname
");

echo html_writer::start_tag('form', ['method' => 'get', 'action' => $PAGE->url, 'style' => 'margin-bottom:20px;']);
echo 'Student: ';
echo html_writer::start_tag('select', ['name' => 'userid', 'onchange' => 'this.form.submit()']);
echo html_writer::tag('option', '-- select student --', ['value' => 0]);
foreach ($students as $s) {
    $sel = ($s->id == $selecteduserid) ? ['selected' => 'selected'] : [];
    echo html_writer::tag('option', $s->lastname . ', ' . $s->firstname, array_merge(['value' => $s->id], $sel));
}
echo html_writer::end_tag('select');

echo ' &nbsp; Competency: ';
$competencies = $pretestdb->query("SELECT * FROM acculearn_competencies ORDER BY sequence")->fetchAll();
echo html_writer::start_tag('select', ['name' => 'competencyid', 'onchange' => 'this.form.submit()']);
echo html_writer::tag('option', '-- select competency --', ['value' => 0]);
foreach ($competencies as $c) {
    $sel = ($c->id == $competencyid) ? ['selected' => 'selected'] : [];
    echo html_writer::tag('option', $c->name, array_merge(['value' => $c->id], $sel));
}
echo html_writer::end_tag('select');
echo html_writer::end_tag('form');

if ($selecteduserid && $competencyid) {
    $questions = $pretestdb->prepare("
        SELECT q.id, q.name, q.questiontext
        FROM mdl_question q
        JOIN acculearn_question_competency qc ON qc.questionid = q.id
        WHERE qc.competency_id = ?
        ORDER BY q.id
    ");
    $questions->execute([$competencyid]);
    $questionrows = $questions->fetchAll();

    $existing = $pretestdb->prepare("SELECT questionid, confidence, is_correct FROM acculearn_pretest_responses WHERE userid = ? AND competency_id = ?");
    $existing->execute([$selecteduserid, $competencyid]);
    $existingmap = [];
    foreach ($existing->fetchAll() as $e) {
        $existingmap[$e->questionid] = $e;
    }

    echo html_writer::tag('p', count($questionrows) . ' questions available for this competency. Fill in ONLY the ones actually used on the paper pre-test; leave the rest blank.'); echo html_writer::link(new moodle_url('/local/acculearnfeedback/generate_pretest_track.php', ['userid' => $selecteduserid]), 'Generate Initial Track for this Student', ['class' => 'btn btn-success', 'style' => 'margin-bottom:16px;']);

    echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url . '?userid=' . $selecteduserid . '&competencyid=' . $competencyid]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

    echo html_writer::start_tag('table', ['class' => 'generaltable', 'style' => 'width:100%']);
    echo html_writer::tag('tr',
        html_writer::tag('th', 'Question') .
        html_writer::tag('th', 'Text') .
        html_writer::tag('th', 'Correct?') .
        html_writer::tag('th', 'Confidence')
    );

    foreach ($questionrows as $q) {
        $prevconf = $existingmap[$q->id]->confidence ?? 0;
        $prevcorrect = isset($existingmap[$q->id]) ? $existingmap[$q->id]->is_correct : -1;

        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', s($q->name) .
            html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'questionid[]', 'value' => $q->id])
        );
        echo html_writer::tag('td', strip_tags($q->questiontext), ['style' => 'max-width:350px;']);

        echo html_writer::start_tag('td');
        foreach (['1' => 'Correct', '0' => 'Incorrect'] as $val => $label) {
            $checked = ($prevcorrect == $val) ? ['checked' => 'checked'] : [];
            echo html_writer::empty_tag('input', array_merge(
                ['type' => 'radio', 'name' => "correct_{$q->id}", 'value' => $val], $checked
            ));
            echo ' ' . $label . ' ';
        }
        echo html_writer::end_tag('td');

        echo html_writer::start_tag('td');
        foreach (['1' => 'Low', '2' => 'Medium', '3' => 'High'] as $val => $label) {
            $checked = ($prevconf == $val) ? ['checked' => 'checked'] : [];
            echo html_writer::empty_tag('input', array_merge(
                ['type' => 'radio', 'name' => "confidence_{$q->id}", 'value' => $val], $checked
            ));
            echo ' ' . $label . ' ';
        }
        echo html_writer::end_tag('td');

        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('table');
    echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => 'Save Responses', 'class' => 'btn btn-primary', 'style' => 'margin-top:16px;']);
    echo html_writer::end_tag('form');
}

echo $OUTPUT->footer();
