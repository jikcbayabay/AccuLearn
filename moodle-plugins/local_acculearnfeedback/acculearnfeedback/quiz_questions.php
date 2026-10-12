<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_login();

use local_acculearnfeedback\question_pools;

global $DB, $OUTPUT, $PAGE;

$context = context_system::instance();
// Teachers only - admin accounts are turned away even though a site admin
// passes every capability check (see local_acculearnfeedback_can_manage_quiz_questions).
// The add/edit links lead into Moodle's own question bank, which checks
// moodle/question:* there - see db/upgrade.php 2026100500 for what the
// AccuLearn teacher role was given.
require_capability('local/acculearnfeedback:managequizquestions', $context);
if (!local_acculearnfeedback_can_manage_quiz_questions()) {
    throw new required_capability_exception($context, 'local/acculearnfeedback:managequizquestions',
        'nopermissions', '');
}

$competencies = $DB->get_records_sql(
    "SELECT id, sequence, name, courseid FROM acculearn_competencies
      WHERE courseid IS NOT NULL ORDER BY sequence");
$courseid = optional_param('courseid', 0, PARAM_INT);
$bycourse = [];
foreach ($competencies as $c) {
    $bycourse[(int) $c->courseid] = $c;
}
if (!isset($bycourse[$courseid])) {
    $courseid = (int) reset($competencies)->courseid;
}
$competency = $bycourse[$courseid];

$PAGE->set_url('/local/acculearnfeedback/quiz_questions.php', ['courseid' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_title('Quiz Questions');
$PAGE->set_heading($SITE->fullname);

// The quizzes of this competency course, in course order.
$quizzes = $DB->get_records_sql("
    SELECT q.id, q.name, q.course, q.timelimit, cm.id AS cmid
      FROM {quiz} q
      JOIN {course_modules} cm ON cm.instance = q.id
      JOIN {modules} m ON m.id = cm.module AND m.name = 'quiz'
      JOIN {course_sections} cs ON cs.id = cm.section
     WHERE q.course = ? AND cm.deletioninprogress = 0
  ORDER BY cs.section, cm.id", [$courseid]);

// Self-heal before showing anything: the observer normally keeps the slots in
// step with the pools, but a bank edited while the observer was failing, or
// questions changed directly in the database, would otherwise be shown here
// with a plan that is not what students actually get.
$states = [];
foreach ($quizzes as $quiz) {
    question_pools::rebalance_quiz($quiz);
    $states[$quiz->id] = question_pools::quiz_state($quiz);
}

echo $OUTPUT->header();
echo html_writer::start_div('al-page');
echo html_writer::tag('h2', 'Quiz Questions', ['class' => 'al-page__title']);
echo html_writer::tag('p',
    'Every quiz picks its questions at random from three pools: Easy, Medium and Hard.',
    ['class' => 'al-page__subtitle']);

echo html_writer::div(
    '<strong>How a quiz is built.</strong> Each attempt draws <strong>2 Easy, 2 Medium and 1 Hard</strong> '
    . 'question for a formative quiz, and <strong>10 of each</strong> for a summative quiz, which has a '
    . '<strong>50-minute</strong> time limit. Anything you add to a pool can come up from the student\'s next '
    . 'attempt; attempts already started keep their questions. Editing a question does not change answers '
    . 'students have already given.<br><br>'
    . 'If a pool has fewer questions than it should draw, the missing questions are taken from the next pool '
    . '(a missing Hard question becomes an extra Medium one) until you add more. To move a question to another '
    . 'difficulty, open its pool, tick it and use <em>Move to</em>.',
    'al-card al-card--accent'
);

// One button per competency course.
$pills = [];
foreach ($competencies as $c) {
    $pills[] = html_writer::link(
        new moodle_url('/local/acculearnfeedback/quiz_questions.php', ['courseid' => $c->courseid]),
        'CO' . (int) $c->sequence, [
            'class' => 'al-btn al-btn-sm ' . ($c->courseid == $courseid ? 'al-btn-primary' : 'al-btn-secondary'),
            'title' => format_string($c->name),
        ]);
}
echo html_writer::div(implode('', $pills), '',
    ['style' => 'margin-bottom:16px; display:flex; flex-wrap:wrap; gap:6px;']);

echo html_writer::tag('h3', 'CO' . (int) $competency->sequence . ': ' . format_string($competency->name),
    ['style' => 'margin-bottom:4px;']);

// Totals for the whole competency, so the size of the question bank can be
// checked at a glance against the source documents.
$totals = array_fill_keys(array_keys(question_pools::LEVELS), 0);
foreach ($states as $state) {
    if ($state) {
        foreach ($state->sizes as $level => $n) {
            $totals[$level] += $n;
        }
    }
}
$parts = [];
foreach (question_pools::LEVELS as $level => $label) {
    $parts[] = $totals[$level] . ' ' . $label;
}
echo html_writer::tag('p',
    '<strong>' . array_sum($totals) . ' questions</strong> across ' . count($quizzes) . ' quizzes ('
    . implode(' &middot; ', $parts) . ')',
    ['style' => 'margin:0 0 12px; color:var(--al-ink-500);']);

$returnurl = (new moodle_url('/local/acculearnfeedback/quiz_questions.php', ['courseid' => $courseid]))
    ->out_as_local_url(false);

foreach ($quizzes as $quiz) {
    $state = $states[$quiz->id];

    echo html_writer::start_div('al-card');
    $limit = $quiz->timelimit ? ' &middot; ' . format_time($quiz->timelimit) . ' time limit' : '';
    echo html_writer::div(
        html_writer::tag('h4', format_string($quiz->name), ['style' => 'margin:0;']) .
        html_writer::link(new moodle_url('/mod/quiz/view.php', ['id' => $quiz->cmid]), 'Open quiz',
            ['class' => 'al-btn al-btn-sm al-btn-ghost']),
        '', ['style' => 'display:flex; justify-content:space-between; align-items:center; gap:8px;']);

    if (!$state) {
        echo html_writer::tag('p',
            'This quiz is not set up with Easy/Medium/Hard pools, so it is not managed here.',
            ['style' => 'margin:8px 0 0; color:var(--al-ink-500);']);
        echo html_writer::end_div();
        continue;
    }

    echo html_writer::tag('p',
        ($state->kind === 'summative' ? 'Summative' : 'Formative') . ' &middot; '
        . '<strong>' . array_sum($state->sizes) . ' questions in the bank</strong> &middot; '
        . count($state->slots) . ' per attempt' . $limit,
        ['style' => 'margin:4px 0 10px; color:var(--al-ink-500); font-size:13px;']);

    $qbankcmid = context::instance_by_id($state->bank->contextid)->instanceid;
    $rows = '';
    foreach (question_pools::LEVELS as $level => $label) {
        $pool = $state->pools[$level];
        $size = $state->sizes[$level];
        $target = $state->targets[$level];
        $draws = $state->alloc[$level];

        if ($draws < $target) {
            $note = html_writer::tag('span',
                'Needs ' . ($target - $draws) . ' more', ['class' => 'al-badge al-badge--flagged']);
        } else if ($draws > $target) {
            $note = html_writer::tag('span',
                '+' . ($draws - $target) . ' covering a smaller pool', ['class' => 'al-badge al-badge--pending']);
        } else {
            $note = html_writer::tag('span', 'OK', ['class' => 'al-badge al-badge--approved']);
        }

        $actions =
            html_writer::link(new moodle_url('/question/edit.php',
                ['cmid' => $qbankcmid, 'cat' => $pool->id . ',' . $pool->contextid]),
                '<i class="fa fa-pen" aria-hidden="true"></i> View &amp; edit',
                ['class' => 'al-btn al-btn-sm al-btn-secondary']) . ' ' .
            html_writer::link(new moodle_url('/question/bank/editquestion/question.php', [
                    'qtype' => 'multichoice', 'category' => $pool->id, 'cmid' => $qbankcmid,
                    'returnurl' => $returnurl,
                ]),
                '<i class="fa fa-plus" aria-hidden="true"></i> Add question',
                ['class' => 'al-btn al-btn-sm al-btn-primary']);

        $rows .= html_writer::tag('tr',
            html_writer::tag('th', $label, ['scope' => 'row']) .
            html_writer::tag('td', $size) .
            html_writer::tag('td', $draws . ' of ' . $target) .
            html_writer::tag('td', $note) .
            html_writer::tag('td', $actions, ['style' => 'white-space:nowrap;']));
    }
    $head = html_writer::tag('tr',
        html_writer::tag('th', 'Pool') .
        html_writer::tag('th', 'Questions in pool') .
        html_writer::tag('th', 'Drawn per attempt') .
        html_writer::tag('th', '') .
        html_writer::tag('th', ''));
    echo html_writer::div(
        html_writer::tag('table',
            html_writer::tag('thead', $head) . html_writer::tag('tbody', $rows),
            ['class' => 'generaltable', 'style' => 'width:100%; margin:0;']),
        'al-table-wrap');

    if (array_sum($state->alloc) < count($state->slots)) {
        echo html_writer::div(
            '<strong>Students cannot start this quiz.</strong> Its pools hold fewer questions than one attempt '
            . 'needs. Add questions to any pool.',
            'al-card al-card--flag-flagged', ['style' => 'margin:10px 0 0;']);
    }
    echo html_writer::end_div();
}

echo html_writer::end_div();
echo $OUTPUT->footer();
