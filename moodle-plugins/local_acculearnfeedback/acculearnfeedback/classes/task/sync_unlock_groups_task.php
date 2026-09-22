<?php
namespace local_acculearnfeedback\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Full periodic resync of AccuLearn's per-competency "Unlocked" groups.
 *
 * The main sync happens immediately after each quiz submission
 * (qbehaviour_confidence's generate_feedback_task) and after pre-test track
 * generation; this task is the safety net for anything that bypasses those -
 * e.g. a competency's gating_enabled/prerequisite_id/courseid being edited
 * via admin_sequence.php, a manual enrolment change, or a missed ad-hoc task.
 *
 * It first re-decides any AI-decided track whose evidence changed without a
 * submission (a regrade, an extra attempt), so that the group sync that
 * follows reads a decision made on current facts.
 */
class sync_unlock_groups_task extends \core\task\scheduled_task {

    public function get_name() {
        return 'Sync AccuLearn unlock groups';
    }

    public function execute() {
        global $CFG;

        require_once($CFG->dirroot . '/local/acculearnfeedback/lib.php');

        $refreshed = local_acculearnfeedback_refresh_stale_tracks();
        mtrace("AccuLearn: re-decided $refreshed stale learning track(s)");

        $count = local_acculearnfeedback_sync_all_unlock_groups();
        mtrace("AccuLearn: resynced unlock groups for $count enrolled users");
    }
}
