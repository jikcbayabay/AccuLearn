<?php
defined('MOODLE_INTERNAL') || die();

class qbehaviour_confidence_type extends question_behaviour_type {
    public function is_archetypal() {
        return true;
    }
    public function get_unused_display_options() {
        return array('correctness', 'marks', 'specificfeedback', 'generalfeedback',
                'rightanswer');
    }
}	
