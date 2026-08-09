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

        $inputname = $qa->get_qt_field_name('confidence');
        // The group needs its own id so the label can be associated with the
        // radiogroup; $inputname is unique per question on the page.
        $labelid = $inputname . '_label';

        $html  = html_writer::start_div('qbehaviour_confidence_box');
        $html .= html_writer::tag('p',
            get_string('confidencelevel', 'qbehaviour_confidence'),
            ['class' => 'qbehaviour_confidence_label', 'id' => $labelid]
        );

        // role/aria-labelledby rather than a fieldset+legend: this markup is
        // injected inside the question's existing form markup, where Boost's
        // fieldset styling would fight the layout.
        $html .= html_writer::start_div('qbehaviour_confidence_options', [
            'role' => 'radiogroup',
            'aria-labelledby' => $labelid,
        ]);

        foreach ($levels as $value => $label) {
            $inputid = $inputname . '_' . $value;
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
            // Without this the radios stayed live on the review page, so a
            // student looking back at a submitted attempt could click a
            // different confidence and believe it had been recorded.
            if (!empty($options->readonly)) {
                $attrs['disabled'] = 'disabled';
            }

            // Each radio and its label share a row wrapper. Previously they
            // were siblings separated by <br>, which left the label's text
            // baseline unaligned with the radio and produced uneven gaps that
            // varied with the label's length.
            $html .= html_writer::div(
                html_writer::empty_tag('input', $attrs) .
                html_writer::label($label, $inputid, false, ['class' => 'qbehaviour_confidence_optiontext']),
                'qbehaviour_confidence_option'
            );
        }

        $html .= html_writer::end_div();
        $html .= html_writer::end_div();

        return parent::controls($qa, $options) . $html;
    }
}
