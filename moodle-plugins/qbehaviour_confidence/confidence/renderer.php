<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/behaviour/deferredfeedback/renderer.php');

class qbehaviour_confidence_renderer extends qbehaviour_deferredfeedback_renderer {

    public function controls(question_attempt $qa, question_display_options $options): string {
        $currentconf = $qa->get_last_qt_var('confidence', '');
        $levels = [
            1 => get_string('confidence_low',    'qbehaviour_confidence'),
            2 => get_string('confidence_medium', 'qbehaviour_confidence'),
            3 => get_string('confidence_high',   'qbehaviour_confidence'),
        ];

        $html  = html_writer::start_div('qbehaviour_confidence_box');
        $html .= html_writer::tag('p',
            get_string('confidencelevel', 'qbehaviour_confidence'),
            ['class' => 'qbehaviour_confidence_label']
        );

        foreach ($levels as $value => $label) {
            $inputname = $qa->get_qt_field_name('confidence');
            $inputid   = $inputname . '_' . $value;
            $attrs = [
                'type'  => 'radio',
                'name'  => $inputname,
                'id'    => $inputid,
                'value' => $value,
                'class' => 'qbehaviour_confidence_radio',
            ];
            if ((string)$value === (string)$currentconf) {
                $attrs['checked'] = 'checked';
            }
            $html .= html_writer::empty_tag('input', $attrs);
            $html .= html_writer::label($label, $inputid, true, ['class' => 'qbehaviour_confidence_option']);
            $html .= html_writer::empty_tag('br');
        }

        $html .= html_writer::end_div();
        return parent::controls($qa, $options) . $html;
    }
}

