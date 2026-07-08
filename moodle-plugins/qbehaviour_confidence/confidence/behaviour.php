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
}
