<?php
namespace local_acculearnfeedback;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->dirroot . '/local/acculearnfeedback/lib.php');

/**
 * Writes AccuLearn usage-log entries in response to Moodle's own events.
 *
 * Design rules for everything in here:
 *  - Never throw. These run inside student page loads and quiz submissions;
 *    a logging problem must not become a student-visible failure.
 *  - Only log real participants. Teachers and managers browsing the courses
 *    would otherwise pollute the engagement dataset the study reports on.
 *  - Resolve names at event time, not at display time, so the log remains a
 *    faithful record even after an activity is renamed or deleted.
 */
class observer {

    /**
     * Staff activity is not engagement data. Checked per-user rather than
     * against the current session, because events can be raised on behalf of
     * another user (e.g. during a regrade).
     */
    private static function is_participant($userid) {
        if (empty($userid) || isguestuser($userid)) {
            return false;
        }
        return !has_capability('local/acculearnfeedback:reviewaioutput',
            \context_system::instance(), $userid);
    }

    /** Course shortname/fullname for the log detail, or '' if unavailable. */
    private static function course_label($courseid) {
        global $DB;
        if (empty($courseid) || $courseid == SITEID) {
            return '';
        }
        $c = $DB->get_record('course', ['id' => $courseid], 'id, shortname, fullname');
        return $c ? $c->fullname : '';
    }

    /**
     * Which AccuLearn competency a course maps to, so engagement can be
     * reported per competency rather than per Moodle course id.
     */
    private static function competency_label($courseid) {
        global $DB;
        if (empty($courseid)) {
            return '';
        }
        try {
            $rec = $DB->get_record_sql(
                "SELECT name, sequence FROM acculearn_competencies WHERE courseid = ?", [$courseid]);
        } catch (\Throwable $e) {
            return '';
        }
        return $rec ? "CO{$rec->sequence}: {$rec->name}" : '';
    }

    private static function log(\core\event\base $event, $action, $targettype, $targetid, $detail) {
        try {
            if (!self::is_participant($event->userid)) {
                return;
            }
            local_acculearnfeedback_log_usage(
                $event->userid, $action, $targettype, $targetid, $detail, $event->courseid);
        } catch (\Throwable $e) {
            debugging('AccuLearn observer failed for ' . $action . ': ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    /** Activity name captured from the event's own context. */
    private static function module_name(\core\event\base $event) {
        global $DB;
        try {
            $cmid = $event->contextinstanceid;
            $cm = $DB->get_record_sql(
                "SELECT m.name AS modname, cm.instance
                   FROM {course_modules} cm
                   JOIN {modules} m ON m.id = cm.module
                  WHERE cm.id = ?", [$cmid]);
            if (!$cm) {
                return '';
            }
            $inst = $DB->get_record($cm->modname, ['id' => $cm->instance], 'id, name');
            return $inst ? $inst->name : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    public static function user_loggedin(\core\event\user_loggedin $event) {
        // $event->objectid is the user id for this event; ->userid is too, but
        // objectid is the documented target.
        try {
            if (!self::is_participant($event->objectid)) {
                return;
            }
            local_acculearnfeedback_log_usage($event->objectid, 'session_start', 'session', null,
                'Signed in to AccuLearn', null);
        } catch (\Throwable $e) {
            debugging('AccuLearn observer failed for session_start: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    public static function lesson_viewed(\core\event\base $event) {
        $name = self::module_name($event);
        $comp = self::competency_label($event->courseid);
        self::log($event, 'view_lesson', 'scorm', $event->contextinstanceid,
            trim("Lesson: {$name}" . ($comp ? " ({$comp})" : '')));
    }

    public static function sco_launched(\core\event\base $event) {
        $name = self::module_name($event);
        self::log($event, 'launch_lesson_page', 'scorm', $event->contextinstanceid,
            trim("Opened content inside lesson: {$name}"));
    }

    public static function quiz_viewed(\core\event\base $event) {
        $name = self::module_name($event);
        $comp = self::competency_label($event->courseid);
        self::log($event, 'view_quiz', 'quiz', $event->contextinstanceid,
            trim("Quiz: {$name}" . ($comp ? " ({$comp})" : '')));
    }

    public static function quiz_attempt_started(\core\event\base $event) {
        $name = self::module_name($event);
        self::log($event, 'quiz_started', 'attempt', $event->objectid,
            trim("Started: {$name}"));
    }

    public static function quiz_attempt_submitted(\core\event\base $event) {
        global $DB;
        $name = self::module_name($event);
        $extra = '';
        try {
            // Attempt number is what makes the retake pattern visible in the
            // log - the thing the three-retake design is built around.
            $a = $DB->get_record('quiz_attempts', ['id' => $event->objectid], 'attempt, sumgrades');
            if ($a) {
                $extra = " (attempt {$a->attempt}"
                    . ($a->sumgrades === null ? '' : ", raw score {$a->sumgrades}") . ')';
            }
        } catch (\Throwable $e) {
            $extra = '';
        }
        self::log($event, 'quiz_submitted', 'attempt', $event->objectid,
            trim("Submitted: {$name}{$extra}"));
    }

    public static function course_viewed(\core\event\course_viewed $event) {
        if (empty($event->courseid) || $event->courseid == SITEID) {
            return;
        }
        $comp = self::competency_label($event->courseid);
        // Only log the AccuLearn competency courses - other course views are
        // not part of this study's engagement measure.
        if ($comp === '') {
            return;
        }
        self::log($event, 'view_course', 'course', $event->courseid, $comp);
    }
}
