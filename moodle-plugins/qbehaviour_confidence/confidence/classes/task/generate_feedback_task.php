<?php
namespace qbehaviour_confidence\task;

defined('MOODLE_INTERNAL') || die();

class generate_feedback_task extends \core\task\adhoc_task {

    public function execute() {
        global $DB;

        $data = $this->get_custom_data();
        $attemptid = $data->attemptid ?? null;

        if (!$attemptid) {
            return;
        }

        $backendpath = '/var/www/html/acculearn-backend';
        if (!file_exists("$backendpath/generate_feedback.php")) {
            mtrace("AccuLearn: backend not found at $backendpath");
            return;
        }

        require_once("$backendpath/generate_feedback.php");
        if (function_exists('acculearn_generate_feedback_for_attempt')) {
            mtrace("AccuLearn: generating feedback for attempt $attemptid");
            acculearn_generate_feedback_for_attempt($attemptid);
            mtrace("AccuLearn: feedback done for attempt $attemptid");
        }

        // Also refresh the student's overall learning track, since new quiz
        // data may have changed their mastery/PR/LP status for one or more competencies.
        $attempt = $DB->get_record('quiz_attempts', ['id' => $attemptid]);
        if ($attempt) {
            $user = $DB->get_record('user', ['id' => $attempt->userid]);
            if ($user && file_exists("$backendpath/track_engine.php")) {
                require_once("$backendpath/track_engine.php");
                $trackEngine = new \TrackEngine();
                $studentName = $user->firstname . ' ' . $user->lastname;
                mtrace("AccuLearn: refreshing learning track for user {$attempt->userid}");
                $trackEngine->generateTrack($attempt->userid, $studentName);
                mtrace("AccuLearn: track refresh done");
            }
        }
    }
}
