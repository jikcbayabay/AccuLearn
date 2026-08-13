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
            $qa = $quba->get_question_attempt($slot);
            // get_last_qt_var() searches BACKWARDS through every step for the
            // most recent value; get_last_step()->get_qt_var() reads only the
            // final step, which is not where the rating lives.
            //
            // A submitted question ends up with three steps: 'todo', then
            // 'complete' carrying answer+confidence, then a grading step
            // ('gradedright'/'gradedwrong') whose only variable is '-finish'.
            // Reading the last step therefore returns null the moment that
            // grading step has been appended - and whether it has, by the time
            // this observer runs, is timing-dependent. It usually had not been,
            // so this usually worked; on 5 of the 139 attempts on this site it
            // had been, and every one of those students' confidence ratings was
            // silently dropped. Their answers still graded normally, so nothing
            // looked wrong: the attempt simply produced no CBM evidence, no
            // AI feedback, and contributed nothing to CM/CMI/GI.
            $confidence = $qa->get_last_qt_var('confidence');

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
