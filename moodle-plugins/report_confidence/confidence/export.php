<?php
require_once('../../config.php');
require_login();

$context = context_system::instance();
require_capability('report/confidence:view', $context);

global $DB;

$sql = "
    SELECT
        qbc.id         AS confidence_id,
        u.id           AS student_id,
        u.firstname,
        u.lastname,
        q.name         AS quiz_name,
        qa.attempt,
        qas.slot       AS question_number,
        que.id         AS question_id,
        que.name       AS question_name,
        qasdata.value  AS selected_answer,
        CASE laststep.state
            WHEN 'gradedright' THEN 'Correct'
            WHEN 'gradedpartial' THEN 'Partial'
            WHEN 'gradedwrong' THEN 'Incorrect'
            ELSE laststep.state
        END AS result,
        CASE qbc.confidence
            WHEN 1 THEN 'Low'
            WHEN 2 THEN 'Medium'
            WHEN 3 THEN 'High'
        END AS confidence_level,
        FROM_UNIXTIME(qbc.timecreated) AS submitted_at
    FROM {qbehaviour_confidence} qbc
    JOIN {quiz_attempts} qa ON qa.id = qbc.attemptid
    JOIN {quiz} q ON q.id = qa.quiz
    JOIN {user} u ON u.id = qbc.userid
    JOIN {question_attempts} qas ON qas.id = qbc.questionattemptid
    JOIN {question} que ON que.id = qas.questionid
    LEFT JOIN {question_attempt_steps} laststep
        ON laststep.id = (
            SELECT id FROM {question_attempt_steps}
            WHERE questionattemptid = qbc.questionattemptid
            ORDER BY sequencenumber DESC LIMIT 1
        )
    LEFT JOIN {question_attempt_step_data} qasdata
        ON qasdata.attemptstepid = (
            SELECT qas2.id
            FROM {question_attempt_steps} qas2
            JOIN {question_attempt_step_data} qasd2 ON qasd2.attemptstepid = qas2.id
            WHERE qas2.questionattemptid = qbc.questionattemptid
              AND qasd2.name = 'answer'
            ORDER BY qas2.sequencenumber DESC LIMIT 1
        )
        AND qasdata.name = 'answer'
    ORDER BY u.id, qa.attempt, qas.slot
";

$recordset = $DB->get_recordset_sql($sql);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="confidence_export.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Student ID', 'First Name', 'Last Name', 'Quiz', 'Attempt',
               'Q#', 'Question ID', 'Question Name', 'Answer', 'Result',
               'Confidence', 'Submitted At'], ',', '"', '\\');

foreach ($recordset as $r) {
    fputcsv($out, [
        $r->student_id, $r->firstname, $r->lastname, $r->quiz_name,
        $r->attempt, $r->question_number, $r->question_id, $r->question_name,
        $r->selected_answer, $r->result, $r->confidence_level, $r->submitted_at
    ], ',', '"', '\\');
}

$recordset->close();
fclose($out);
