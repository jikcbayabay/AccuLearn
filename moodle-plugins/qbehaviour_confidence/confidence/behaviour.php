<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/behaviour/deferredfeedback/behaviour.php');

class qbehaviour_confidence extends qbehaviour_deferredfeedback {

    const IS_ARCHETYPAL = true;

    public function get_expected_qt_data() {
        $expected = parent::get_expected_qt_data();
        if (is_array($expected)) {
            $expected['confidence'] = PARAM_INT;
        }
        return $expected;
    }

    public function is_complete_response(question_attempt_step $response): bool {
        if (!parent::is_complete_response($response)) {
            return false;
        }
        $confidence = $response->get_qt_var('confidence');
        return in_array((int)$confidence, [1, 2, 3], true);
    }

    /**
     * Whether the newly submitted response is unchanged from the stored one.
     *
     * This MUST account for the confidence rating, and the inherited version
     * does not. question_behaviour_with_save::process_save() discards the
     * entire pending step when this returns true, and the inherited
     * implementation delegates to the QUESTION TYPE's is_same_response(),
     * which only ever compares the question type's own fields (for
     * multichoice, just 'answer'). Confidence is a behaviour-level field the
     * question type knows nothing about.
     *
     * The consequence was a silent data-loss bug: a student who picked an
     * answer but no confidence, moved to another page, came back and then set
     * only the confidence submitted a response whose 'answer' was unchanged,
     * so the step was discarded and the confidence was never stored. The
     * radio simply appeared to do nothing. Since CM/CMI/GI all key off
     * confidence, those attempts silently produced skewed indices too.
     *
     * @param question_attempt_step $pendingstep the new response.
     * @return bool true if nothing the behaviour cares about changed.
     */
    protected function is_same_response(question_attempt_step $pendingstep) {
        if (!parent::is_same_response($pendingstep)) {
            return false;
        }

        $new = $pendingstep->get_qt_var('confidence');
        if ($new === null) {
            // Confidence was not part of this submission at all (e.g. a
            // readonly re-render, or a page that did not carry the field).
            // Absence is not a change - treating it as one would save a step
            // that drops a confidence the student had already given.
            return true;
        }

        // Compare against get_last_qt_var() rather than the last step's data:
        // it walks back through the steps for the most recent value, which is
        // the same value the renderer shows as checked. Comparing only the
        // immediately preceding step would report "changed" whenever that step
        // happened not to carry a confidence value.
        return (string)$new === (string)$this->qa->get_last_qt_var('confidence', '');
    }
}
