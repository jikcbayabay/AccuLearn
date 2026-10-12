<?php
namespace local_acculearnfeedback\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Builds a student's initial learning track from their pre-test responses.
 *
 * This exists as an ad-hoc task rather than a direct call inside the request
 * that submits the pre-test, for the same reason feedback generation is queued
 * (see qbehaviour_confidence\task\generate_feedback_task): the AccuLearn
 * backend opens its OWN database connection, so rows written by the still-open
 * Moodle transaction are not visible to it. Generating the track inline read an
 * empty pre-test and produced a track with every competency "Not Attempted".
 * Queuing guarantees the insert has committed before the engine looks.
 */
class generate_track_task extends \core\task\adhoc_task {

    public function execute() {
        global $DB, $CFG;

        $data = $this->get_custom_data();
        $userid = $data->userid ?? null;
        if (!$userid) {
            return;
        }

        $backendpath = '/var/www/html/acculearn-backend';
        if (!file_exists("$backendpath/track_engine.php")) {
            mtrace("AccuLearn: track engine not found at $backendpath");
            return;
        }

        require_once($CFG->dirroot . '/local/acculearnfeedback/lib.php');
        require_once("$backendpath/track_engine.php");

        $responses = $DB->get_field_sql(
            "SELECT COUNT(*) FROM acculearn_pretest_responses WHERE userid = ?", [$userid]);
        if (!$responses) {
            mtrace("AccuLearn: no pre-test responses for user $userid, nothing to build from");
            return;
        }

        mtrace("AccuLearn: building initial track for user $userid from $responses pre-test responses");
        try {
            $engine = new \TrackEngine();
            // The engine is given a placeholder, never the real name - no
            // student name reaches OpenAI. See FeedbackEngine::STUDENT_PLACEHOLDER.
            $engine->generateTrack($userid, '[STUDENT]', 'pretest');
            mtrace("AccuLearn: initial track built for user $userid");
            local_acculearnfeedback_log_usage($userid, 'track_generated', 'track', null,
                'Initial learning track generated from the student\'s own pre-test');
        } catch (\Exception $e) {
            // Swallowed rather than rethrown, matching generate_feedback_task:
            // a rate-limited or failed OpenAI call should not leave the task in
            // a permanently failing state. The deterministic fallback inside the
            // engine still writes a track, and the student's first quiz
            // submission queues a refresh anyway.
            mtrace("AccuLearn: track build failed for user $userid: " . $e->getMessage());
        }

        // Open whichever competency courses the resulting decision unlocks.
        local_acculearnfeedback_sync_unlock_groups_for_user($userid);
        mtrace("AccuLearn: unlock groups synced for user $userid");
    }
}
