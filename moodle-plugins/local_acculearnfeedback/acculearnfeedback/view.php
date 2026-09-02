<?php
require_once(__DIR__ . '/../../config.php');

require_once(__DIR__ . '/lib.php');

// require_login() must come FIRST. With required_param() ahead of it, an
// anonymous request with no 'attempt' parameter got Moodle's "A required
// parameter (attempt) was missing" error page (HTTP 404) instead of being
// redirected to the login page - the only AccuLearn endpoint that answered
// an unauthenticated caller with an application error rather than a login
// redirect, which confirms to an unauthenticated prober that the endpoint
// exists and what parameter it expects. No data was exposed (the DB lookups
// below all sit after this point), but authentication should gate the
// endpoint before any request parsing happens.
require_login();

$attemptid = required_param('attempt', PARAM_INT);

global $DB, $USER, $OUTPUT, $PAGE;

// Verify this attempt belongs to the logged-in user (or they're a teacher who can grade it).
$attempt = $DB->get_record('quiz_attempts', ['id' => $attemptid], '*', MUST_EXIST);
$quiz = $DB->get_record('quiz', ['id' => $attempt->quiz], '*', MUST_EXIST);
$course = $DB->get_record('course', ['id' => $quiz->course], '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('quiz', $quiz->id, $quiz->course, false, MUST_EXIST);
$context = context_module::instance($cm->id);

$isowner = ($attempt->userid == $USER->id);
$canreview = has_capability('mod/quiz:viewreports', $context);

if (!$isowner && !$canreview) {
    // print_error() was REMOVED in Moodle 5.0 (deprecated, then deleted - see
    // lib/upgrade.txt: "The function print_error has been deprecated. Kindly
    // use moodle_exception."). Calling it here meant the access-denied path
    // died with a "call to undefined function" fatal instead of a clean
    // permission error - which, with developer debugging enabled, renders a
    // stack trace exposing server file paths to the blocked user.
    throw new moodle_exception('notyours', 'local_acculearnfeedback');
}

$PAGE->set_url('/local/acculearnfeedback/view.php', ['attempt' => $attemptid]);
$PAGE->set_cm($cm, $course);
$PAGE->set_context($context);
$PAGE->set_title(get_string('feedbacktitle', 'local_acculearnfeedback'));

// Boost renders $PAGE->set_heading() in the full-width page-header region,
// which sits OUTSIDE the (narrower) content column. Putting the feedback
// title there left it stranded off to the side of the content it belongs to,
// while the activity header separately printed the quiz name at the top of
// the column - two competing titles, neither of them where the reader looks.
// Moodle's own convention for the page heading is the course name; the page's
// real title belongs at the top of the content column, rendered below.
$PAGE->set_heading($course->fullname);

// Suppress the activity header so the quiz name is not printed a second time
// immediately above our own title. Guarded because $PAGE->activityheader is
// only populated once set_cm() has run.
if (isset($PAGE->activityheader)) {
    $PAGE->activityheader->disable();
}

echo $OUTPUT->header();

local_acculearnfeedback_log_usage($USER->id, 'view_feedback', 'attempt', $attemptid,
    'AI feedback for: ' . $quiz->name . ' (attempt ' . $attempt->attempt . ')', $course->id);

// This page is reached from the quiz review screen, so without links back out
// it is a dead end: a student who has just read what to work on has no route
// to the course that teaches it, the quiz they can retake, or their track.
// Course name and quiz name are both live links, and the same actions repeat
// as buttons at the foot of the page.
$coursename = format_string($course->fullname, true, ['context' => context_course::instance($course->id)]);
$quizname = format_string($quiz->name, true, ['context' => $context]);
$courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
$quizurl = new moodle_url('/mod/quiz/view.php', ['id' => $cm->id]);

// Rendered as the first thing inside the content column on both the
// "still generating" and the populated branch, so the page always leads with
// its own title rather than with the quiz name from the activity header.
$pagetitle = html_writer::tag('h2',
        get_string('feedbacktitle', 'local_acculearnfeedback'),
        ['class' => 'al-page__title']) .
    html_writer::tag('p',
        html_writer::link($courseurl, $coursename, ['class' => 'al-course-link']) .
        html_writer::tag('span', '/', ['style' => 'margin:0 8px; opacity:0.5;', 'aria-hidden' => 'true']) .
        html_writer::link($quizurl, $quizname, ['class' => 'al-course-link']),
        ['class' => 'al-page__subtitle']);

// Shown on both branches: a student whose feedback is still generating needs
// these links at least as much as one whose feedback is ready.
$pageactions = html_writer::div(
    html_writer::link($courseurl,
        '<i class="fa fa-book-open" aria-hidden="true"></i> Back to ' . $coursename,
        ['class' => 'al-btn al-btn-sm al-btn-primary']) .
    html_writer::link($quizurl,
        '<i class="fa fa-list-check" aria-hidden="true"></i> ' . $quizname,
        ['class' => 'al-btn al-btn-sm al-btn-outline']) .
    html_writer::link(new moodle_url('/local/acculearnfeedback/my_track.php'),
        '<i class="fa fa-route" aria-hidden="true"></i> My Learning Track',
        ['class' => 'al-btn al-btn-sm al-btn-secondary']),
    '',
    ['style' => 'display:flex; flex-wrap:wrap; gap:8px; margin-top:24px;']
);

$feedbackrow = $DB->get_record_sql(
    "SELECT * FROM acculearn_feedback WHERE attemptid = ?",
    [$attemptid]
);

if (!$feedbackrow) {
    echo html_writer::start_div('al-page', ['style' => 'max-width: 800px;']);
    echo $pagetitle;
    echo html_writer::div(
        html_writer::div('<span class="al-spinner"></span>', 'al-empty__icon', ['style' => 'background:var(--al-blue-50); color:var(--al-blue);']) .
        html_writer::div('Feedback is still generating', 'al-empty__title') .
        html_writer::div(get_string('notfound', 'local_acculearnfeedback'), 'al-empty__sub'),
        'al-card al-empty'
    );
    echo $pageactions;
    echo html_writer::end_div();
    echo $OUTPUT->footer();
    exit;
}

// A teacher who reviews this feedback and flags it as wrong has to actually
// change what the student sees, otherwise "all AI outputs remain subject to
// teacher review and override" is only half true - there was review, and
// there was flagging, and neither suppressed the output. Flagged feedback is
// withheld rather than shown with a warning: the whole point of the flag is
// that a teacher judged the content incorrect, and leaving incorrect
// remediation on screen next to a caveat still teaches it.
if (!empty($feedbackrow->flagged)) {
    echo html_writer::start_div('al-page', ['style' => 'max-width: 800px;']);
    echo $pagetitle;
    echo html_writer::div(
        html_writer::div('<i class="fa fa-user-clock" aria-hidden="true"></i>', 'al-empty__icon') .
        html_writer::div('This feedback is being reviewed by your teacher', 'al-empty__title') .
        html_writer::div('Your teacher flagged something in the automatically generated feedback '
            . 'for this attempt, so it is not being shown. Your quiz result and grade are '
            . 'unaffected. Ask your teacher if you need guidance on this attempt.', 'al-empty__sub'),
        'al-card al-empty'
    );
    echo $pageactions;
    echo html_writer::end_div();
    echo $OUTPUT->footer();
    exit;
}

$feedback = json_decode($feedbackrow->feedback_json, true);

// The AI is never told the student's name - it writes the [STUDENT] token
// instead - so put a name back for whoever is reading. The owner sees their
// own first name; anyone else (a teacher opening the attempt) sees the
// pseudonym, matching the review screen.
$feedback = local_acculearnfeedback_personalise(
    $feedback,
    $isowner ? $USER->firstname : local_acculearnfeedback_pseudonym($attempt->userid)
);

echo html_writer::start_div('al-page', ['style' => 'max-width: 800px;']);
echo $pagetitle;

// Summary
echo html_writer::tag('div',
    html_writer::tag('h4', '<i class="fa fa-compass" aria-hidden="true"></i>Summary', ['style' => 'margin-top:0;']) . html_writer::tag('p', s($feedback['summary'])),
    ['class' => 'al-card al-card--accent', 'style' => 'margin-bottom:16px;']
);

// Indices
echo html_writer::start_div('al-stat-row');
// CBM total is shown because the scheme's stated rationale is that it
// "encourages accurate reporting of confidence" - an incentive effect that
// cannot operate while the score is computed, stored in
// acculearn_feedback.cbm_total, and never seen by the student. It is
// deliberately presented as a diagnostic figure, not a grade: the confidence
// behaviour extends deferredfeedback and does not alter marks.
foreach ([
    'Mastery' => $feedbackrow->cm . '%',
    'Confidence Misconception Index' => $feedbackrow->cmi,
    'Guessing Index' => $feedbackrow->gi,
    'CBM Score' => (int) ($feedbackrow->cbm_total ?? 0),
] as $label => $value) {
    echo html_writer::div(
        html_writer::tag('strong', $value, ['class' => 'tnum']) . html_writer::tag('span', $label),
        'al-stat'
    );
}
echo html_writer::end_div();

// Enrichment hints, present only on the fast-learner path (everything correct,
// low guessing index). The corrective path never sets this key, so a missing
// value is normal rather than an error.
if (!empty($feedback['hints'])) {
    echo html_writer::tag('h4', '<i class="fa fa-lightbulb" aria-hidden="true"></i>Try this next');
    echo html_writer::start_tag('ul');
    foreach ($feedback['hints'] as $h) {
        echo html_writer::tag('li', s($h));
    }
    echo html_writer::end_tag('ul');
}

// Misconceptions
if (!empty($feedback['misconceptions'])) {
    echo html_writer::tag('h4', '<i class="fa fa-triangle-exclamation" aria-hidden="true"></i>What went wrong');
    echo html_writer::start_tag('ul');
    foreach ($feedback['misconceptions'] as $m) {
        echo html_writer::tag('li', s($m));
    }
    echo html_writer::end_tag('ul');
}

// Corrective guidance
if (!empty($feedback['corrective_guidance'])) {
    echo html_writer::tag('h4', '<i class="fa fa-bullseye" aria-hidden="true"></i>How to improve');
    echo html_writer::start_tag('ul');
    foreach ($feedback['corrective_guidance'] as $g) {
        echo html_writer::tag('li', s($g));
    }
    echo html_writer::end_tag('ul');
}

// Encouragement
echo html_writer::tag('p', s($feedback['encouragement']), ['style' => 'font-style:italic; color:var(--al-blue); margin-top:16px;']);

// ---- What you got wrong ----
//
// The indices above tell a student HOW they did; this is the part that tells
// them WHAT to actually correct. The questions, the options in the order they
// saw them, their own choice and the right answer all come live from Moodle's
// question engine, so this renders for every attempt ever made. The written
// explanation is the AI layer on top, matched back to its question by slot.
//
// Only the questions they got wrong appear. A 30-item summative answered
// mostly correctly would otherwise bury the handful of items worth reading
// under a wall of confirmations - and the score above already says how many
// were right.
$breakdown = array_values(array_filter(
    local_acculearnfeedback_attempt_breakdown($attempt),
    function($row) {
        return !$row->correct;
    }
));

if ($breakdown) {
    $notes = [];
    foreach (($feedback['question_notes'] ?? []) as $n) {
        if (!isset($n['slot'])) {
            continue;
        }
        // The note explains why the student's OWN answer was wrong. Earlier
        // iterations of this also carried a "why the correct answer is right"
        // paragraph; it is no longer generated or shown - the correct option is
        // already marked in the list above, and repeating it in prose made the
        // card long without telling the student anything new about their
        // mistake. 'explanation' is read as a fallback for the brief window
        // when that was the field name.
        $notes[(int) $n['slot']] = trim((string) ($n['why_wrong'] ?? $n['explanation'] ?? ''));
    }

    echo html_writer::tag('h4',
        '<i class="fa fa-list-check" aria-hidden="true"></i>The questions you missed ('
        . count($breakdown) . ')',
        ['style' => 'margin-top:28px;']);
    echo html_writer::tag('p',
        'The answer you chose, the correct one, and why yours was wrong. '
        . 'Questions you got right are not repeated here.',
        ['style' => 'color:var(--al-ink-500); font-size:0.9em; margin-top:-6px;']);

    // Old attempts were graded before explanations existed. Say so once, rather
    // than leaving a reader wondering why some questions have no note.
    if (!$notes) {
        echo html_writer::div(
            '<i class="fa fa-circle-info" aria-hidden="true"></i> '
            . 'This attempt was marked before written explanations were added, so it shows your answers '
            . 'without them. Newer quiz attempts include an explanation for every question.',
            'al-card',
            ['style' => 'font-size:0.9em; color:var(--al-ink-700); padding:12px 16px;']
        );
    }

    foreach ($breakdown as $row) {
        // Only not-fully-correct questions reach here, so the two cases are
        // "partly credited" and "wrong".
        $flag = $row->partial ? 'developing' : 'needs';
        echo html_writer::start_div("al-card al-card--flag-$flag", ['style' => 'padding:16px;']);

        echo html_writer::start_div('', [
            'style' => 'display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin-bottom:8px;'
        ]);
        echo html_writer::tag('strong', 'Question ' . $row->slot,
            ['style' => 'color:var(--al-ink-500); font-size:0.85em;']);
        echo html_writer::tag('span',
            $row->partial
                ? '<i class="fa fa-circle-half-stroke" aria-hidden="true"></i> Partly correct'
                : '<i class="fa fa-circle-xmark" aria-hidden="true"></i> Incorrect',
            ['class' => "al-badge al-badge--$flag"]);
        // The confidence rating is the whole point of this study's instrument -
        // "certain and wrong" is a different problem from "guessed and wrong",
        // and the student should see which one they just did.
        list($conflabel, $conftone) = local_acculearnfeedback_confidence_label($row->confidence);
        if ($conflabel !== null) {
            echo html_writer::tag('span', 'You were: ' . $conflabel,
                ['class' => "al-badge al-badge--$conftone"]);
        }
        echo html_writer::end_div();

        echo html_writer::div(
            format_text($row->questiontext, $row->questiontextformat, ['context' => $context]),
            'al-question__text');

        if ($row->options) {
            echo html_writer::start_tag('ul', ['class' => 'al-options']);
            foreach ($row->options as $option) {
                $classes = ['al-option'];
                if ($option->iscorrect) {
                    $classes[] = 'al-option--correct';
                }
                if ($option->ischosen && !$option->iscorrect) {
                    $classes[] = 'al-option--wrong';
                }

                $marks = '';
                if ($option->iscorrect) {
                    $marks .= html_writer::tag('span', 'Correct answer', ['class' => 'al-option__tag']);
                }
                if ($option->ischosen) {
                    $marks .= html_writer::tag('span', 'Your answer', ['class' => 'al-option__tag']);
                }

                echo html_writer::tag('li',
                    html_writer::div(format_text($option->text, $option->format,
                        ['context' => $context]), 'al-option__text') . $marks,
                    ['class' => implode(' ', $classes)]);
            }
            echo html_writer::end_tag('ul');
        } else {
            // Not a plain multiple-choice item - fall back to the engine's own
            // summaries so the student still sees what they put and what was wanted.
            if ($row->responsesummary !== null && $row->responsesummary !== '') {
                echo html_writer::tag('p', html_writer::tag('strong', 'Your answer: ')
                    . s($row->responsesummary), ['style' => 'margin:6px 0;']);
            }
            if ($row->rightanswer !== null && $row->rightanswer !== '') {
                echo html_writer::tag('p', html_writer::tag('strong', 'Correct answer: ')
                    . s($row->rightanswer), ['style' => 'margin:6px 0;']);
            }
        }

        if (!empty($notes[$row->slot])) {
            echo html_writer::div(
                html_writer::tag('strong', 'Why your answer was wrong: ') . s($notes[$row->slot]),
                'al-question__why');
        }

        echo html_writer::end_div();
    }
}

echo $pageactions;

echo html_writer::end_div();

echo $OUTPUT->footer();
