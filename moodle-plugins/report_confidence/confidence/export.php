<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/local/acculearnfeedback/lib.php');
require_login();

$context = context_system::instance();
require_capability('report/confidence:view', $context);

// This export is a bulk dump of every confidence rating and answer on the
// site, so it is protected the same way any other state-revealing bulk
// action is: an explicit sesskey, so it cannot be triggered simply by
// getting a logged-in teacher's browser to load a URL.
require_sesskey();

// Anonymisation, matching teacher_review.php exactly. Previously this
// export unconditionally wrote every student's real first and last name
// into the CSV for anyone holding report/confidence:view - which the
// access.php archetypes grant to editingteacher. That meant a teacher who
// is deliberately NOT allowed to un-anonymise students in the AI review
// screen could obtain the identical mapping just by downloading this file,
// silently defeating the pseudonymisation the rest of the system enforces
// (and the anonymisation commitment in the study's ethics section).
// Real names now require the same dedicated capability as the reveal
// toggle, and the default export is pseudonymous.
$canreveal = has_capability('local/acculearnfeedback:revealstudentnames', $context);
$revealnames = $canreveal ? optional_param('reveal', 0, PARAM_BOOL) : 0;

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
        qcm.competency_id,
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
    -- Legacy UNPREFIXED table, hence the literal name. Carrying competency on
    -- every row is what makes the per-competency confidence/correctness
    -- cross-tabulation possible for quiz and pre-test data alike.
    LEFT JOIN acculearn_question_competency qcm ON qcm.questionid = que.id
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

// The paper pre-test is the BASELINE instrument for the whole quasi-experiment,
// and until now it had no export path anywhere in the system - this file read
// {qbehaviour_confidence} only, i.e. quiz attempts. That left the required
// "frequency distributions for each confidence level" and "cross-tabulation of
// confidence and correctness" producible for post-intervention data but not for
// baseline, which would have had to be read out of the database by hand.
//
// Kept as a separate query rather than a UNION: acculearn_pretest_responses is
// one of the LEGACY UNPREFIXED tables, so it is referenced by its literal name
// while the Moodle tables go through {...} expansion, and the two row shapes
// only partly overlap (a transcribed paper response has no attempt number, no
// slot, and no stored answer text - only correctness and confidence).
$pretestsql = "
    SELECT
        p.id           AS confidence_id,
        u.id           AS student_id,
        u.firstname,
        u.lastname,
        que.id         AS question_id,
        que.name       AS question_name,
        p.competency_id,
        CASE WHEN p.is_correct = 1 THEN 'Correct' ELSE 'Incorrect' END AS result,
        CASE p.confidence
            WHEN 1 THEN 'Low'
            WHEN 2 THEN 'Medium'
            WHEN 3 THEN 'High'
        END AS confidence_level,
        FROM_UNIXTIME(p.entered_at) AS submitted_at
    FROM acculearn_pretest_responses p
    JOIN {user} u ON u.id = p.userid
    LEFT JOIN {question} que ON que.id = p.questionid
    ORDER BY u.id, p.competency_id, p.questionid
";

$recordset = $DB->get_recordset_sql($sql);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="confidence_export'
    . ($revealnames ? '_identified' : '_anonymised') . '.csv"');

$out = fopen('php://output', 'w');

// "Source" distinguishes baseline from post-intervention rows, which is the
// whole reason both are in one file. The timestamp column is deliberately
// labelled for both meanings: for a quiz row it is when the student answered,
// but for a pre-test row it is when a staff member TRANSCRIBED the paper
// response, which can be days later. Reading the latter as a response time
// would be wrong, so the header says so rather than leaving it to be assumed.
if ($revealnames) {
    fputcsv($out, ['Source', 'Student ID', 'First Name', 'Last Name', 'Quiz', 'Attempt',
                   'Q#', 'Question ID', 'Question Name', 'Competency', 'Answer', 'Result',
                   'Confidence', 'Submitted / Entered At'], ',', '"', '\\');
} else {
    fputcsv($out, ['Source', 'Pseudonym', 'Quiz', 'Attempt',
                   'Q#', 'Question ID', 'Question Name', 'Competency', 'Answer', 'Result',
                   'Confidence', 'Submitted / Entered At'], ',', '"', '\\');
}

/**
 * Emit one CSV row under whichever identification mode is active, so the
 * pseudonymisation and CSV-injection sanitisation cannot drift apart between
 * the quiz and pre-test loops.
 */
$writerow = function($source, $r, $quizname, $attempt, $slot, $answer)
        use ($out, $revealnames) {
    $ident = $revealnames
        ? [$r->student_id, $r->firstname, $r->lastname]
        // No student_id either: the numeric id is itself a re-identifier
        // when combined with any other export or the Moodle UI.
        : [local_acculearnfeedback_pseudonym($r->student_id)];

    $row = array_merge([$source], $ident, [
        $quizname, $attempt, $slot, $r->question_id, $r->question_name,
        $r->competency_id ?? '', $answer, $r->result, $r->confidence_level,
        $r->submitted_at,
    ]);

    fputcsv($out, local_acculearnfeedback_csv_row($row), ',', '"', '\\');
};

foreach ($recordset as $r) {
    $writerow('quiz', $r, $r->quiz_name, $r->attempt, $r->question_number,
        $r->selected_answer);
}
$recordset->close();

// Baseline rows last, so an analyst reading the file top-down sees the
// familiar quiz export unchanged before the newly added pre-test block.
$pretestset = $DB->get_recordset_sql($pretestsql);
foreach ($pretestset as $r) {
    // A transcribed paper response carries no attempt, no slot and no stored
    // answer text - only correctness and confidence were ever recorded.
    $writerow('pretest', $r, 'Pre-test (paper)', '', '', '');
}
$pretestset->close();

fclose($out);
