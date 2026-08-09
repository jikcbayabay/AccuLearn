<?php
namespace qbehaviour_confidence;

defined('MOODLE_INTERNAL') || die();

class observer {
    public static function quiz_attempt_submitted($event): void {
        global $DB;

        $attemptid = $event->objectid;
        $attempt   = $DB->get_record('quiz_attempts', ['id' => $attemptid]);

        $quba = \question_engine::load_questions_usage_by_activity($attempt->uniqueid);

        foreach ($quba->get_slots() as $slot) {
            $qa         = $quba->get_question_attempt($slot);
            $laststep   = $qa->get_last_step();
            $confidence = $laststep->get_qt_var('confidence');

            if ($confidence !== null && in_array((int)$confidence, [1, 2, 3], true)) {
                $record = (object)[
                    'attemptid'         => $attemptid,
                    'questionattemptid' => $qa->get_database_id(),
                    'userid'            => $event->userid,
                    'slot'              => $slot,
                    'confidence'        => (int)$confidence,
                    'timecreated'       => time(),
                ];
                $DB->insert_record('qbehaviour_confidence', $record);
            }
        }

        // NOTE: quiz-submission usage logging deliberately does NOT happen here
        // any more. local_acculearnfeedback now registers its own observer on
        // this same event (see local/acculearnfeedback/db/events.php), and it
        // records richer detail - the quiz name, attempt number, raw score and
        // course. Keeping the call here as well meant a single submission wrote
        // TWO usage-log rows under two different action slugs, inflating the
        // engagement totals the study reports. This class keeps its real job:
        // queueing feedback generation.

        // Queue AI feedback generation as an ad-hoc task, guaranteed to run
        // AFTER this transaction commits (avoids cross-connection visibility issues
        // with our separate AccuLearn backend database connection).
        $task = new \qbehaviour_confidence\task\generate_feedback_task();
        $task->set_custom_data(['attemptid' => $attemptid]);
        \core\task\manager::queue_adhoc_task($task);
    }
}
