<?php
defined('MOODLE_INTERNAL') || die();

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
function local_acculearnfeedback_grade_context($feedbackid) {
    global $DB, $CFG;

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
    return $ctx;
}

/**
 * Injects the Inter webfont and the AccuLearn stylesheet into <head> on
 * every page for logged-in users. Needed site-wide (not just our own
 * pages) because the toolbar below renders via before_footer on every
 * Moodle page, not just AccuLearn's.
 */
function local_acculearnfeedback_before_standardhtmlhead() {
    if (!isloggedin() || isguestuser()) {
        return '';
    }

    $cssurl = new moodle_url('/local/acculearnfeedback/styles.css');
    return '
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    $isteacher = has_capability('moodle/course:update', $context);

    $items = [];
    $items[] = [
        'url' => $CFG->wwwroot . '/local/acculearnfeedback/my_track.php',
        'icon' => 'fa-route',
        'label' => 'My Learning Track',
    ];

    if ($isteacher) {
        $items[] = [
            'url' => $CFG->wwwroot . '/local/acculearnfeedback/teacher_review.php',
            'icon' => 'fa-clipboard-check',
            'label' => 'AI Output Review',
        ];
        $items[] = [
            'url' => $CFG->wwwroot . '/local/acculearnfeedback/pretest_entry.php',
            'icon' => 'fa-pencil',
            'label' => 'Pre-Test Entry',
        ];
    }

    $itemshtml = '';
    foreach ($items as $item) {
        $itemshtml .= '
            <a href="' . $item['url'] . '" class="al-toolbar__link">
                <i class="fa ' . $item['icon'] . '" style="font-size:1em; opacity:0.85;"></i>
                <span>' . $item['label'] . '</span>
            </a>';
    }

    echo '
        <div class="al-toolbar">
            <div class="al-toolbar__brand">
                <span class="al-toolbar__mark">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <path d="M5 19 12 5l7 14" stroke="white" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8.2 14h7.6" stroke="#72b579" stroke-width="2.6" stroke-linecap="round"/>
                    </svg>
                </span>
                AccuLearn
            </div>
            ' . $itemshtml . '
        </div>
        <div style="height:56px;"></div>
    ';
}
