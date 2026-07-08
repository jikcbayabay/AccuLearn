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

        // Queue AI feedback generation as an ad-hoc task, guaranteed to run
        // AFTER this transaction commits (avoids cross-connection visibility issues
        // with our separate AccuLearn backend database connection).
        $task = new \qbehaviour_confidence\task\generate_feedback_task();
        $task->set_custom_data(['attemptid' => $attemptid]);
        \core\task\manager::queue_adhoc_task($task);
    }
}
