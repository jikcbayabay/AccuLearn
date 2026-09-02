<?php
namespace qbehaviour_confidence\task;

defined('MOODLE_INTERNAL') || die();

class generate_feedback_task extends \core\task\adhoc_task {

    public function execute() {
        global $DB, $CFG;

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

        require_once($CFG->dirroot . '/local/acculearnfeedback/lib.php');

        require_once("$backendpath/generate_feedback.php");
        if (function_exists('acculearn_generate_feedback_for_attempt')) {
            mtrace("AccuLearn: generating feedback for attempt $attemptid");
            acculearn_generate_feedback_for_attempt($attemptid);
            mtrace("AccuLearn: feedback done for attempt $attemptid");

            $attemptrow = $DB->get_record('quiz_attempts', ['id' => $attemptid]);
            if ($attemptrow) {
                $quizname = $DB->get_field('quiz', 'name', ['id' => $attemptrow->quiz]);
                $quizcourse = $DB->get_field('quiz', 'course', ['id' => $attemptrow->quiz]);
                local_acculearnfeedback_log_usage($attemptrow->userid, 'feedback_generated',
                    'attempt', $attemptid,
                    "AI feedback generated for {$quizname} (attempt {$attemptrow->attempt})",
                    $quizcourse);
            }
        }

        // Also refresh the student's overall learning track, since new quiz
        // data may have changed their mastery/PR/LP status for one or more competencies.
        $attempt = $DB->get_record('quiz_attempts', ['id' => $attemptid]);
        if ($attempt) {
            $user = $DB->get_record('user', ['id' => $attempt->userid]);
            if ($user && file_exists("$backendpath/track_engine.php")) {
                require_once("$backendpath/track_engine.php");
                $trackEngine = new \TrackEngine();
                // Never send the real name to OpenAI. The engine is given a
                // placeholder token and the plugin substitutes the reader's own
                // name at display time - see FeedbackEngine::STUDENT_PLACEHOLDER.
                $studentName = '[STUDENT]';
                mtrace("AccuLearn: refreshing learning track for user {$attempt->userid}");
                try {
                    $trackEngine->generateTrack($attempt->userid, $studentName);
                    // Only record a recalculation that actually happened. The
                    // engine returns the existing track unchanged when the new
                    // evidence is identical, and logging that as a
                    // recalculation inflated the engagement figures the study
                    // reports with events that never occurred.
                    if ($trackEngine->lastRunStoredTrack()) {
                        mtrace("AccuLearn: track refresh done");
                        local_acculearnfeedback_log_usage($attempt->userid, 'track_generated',
                            'track', null,
                            'Learning track recalculated from quiz performance');
                    } else {
                        mtrace("AccuLearn: track unchanged for user {$attempt->userid}, not rewritten");
                    }
                } catch (\Exception $e) {
                    // E.g. the OpenAI rate limit was hit. Swallowed rather than
                    // rethrown - this student's NEXT quiz submission will
                    // trigger the same regeneration and most likely succeed
                    // once the rate-limit window resets, so it's not worth
                    // failing this whole ad-hoc task (which would also skip
                    // the unlock-group sync below) over a stale track.
                    mtrace("AccuLearn: track refresh failed for user {$attempt->userid}: " . $e->getMessage());
                }
            }

            // New mastery data may have unlocked (or re-locked) a competency's
            // course beyond just its quiz - keep the "AccuLearn Unlocked"
            // group membership in the mapped course(s) current immediately,
            // not just at the next scheduled resync.
            local_acculearnfeedback_sync_unlock_groups_for_user($attempt->userid);
            mtrace("AccuLearn: unlock groups synced for user {$attempt->userid}");
        }
    }
}
