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
        // Unique per question on the page, so the group label can be tied to
        // the radiogroup without colliding with the other questions.
        $labelid = $inputname . '_label';

        $html  = html_writer::start_div('qbehaviour_confidence_box');
        $html .= html_writer::tag('p',
            get_string('confidencelevel', 'qbehaviour_confidence'),
            ['class' => 'qbehaviour_confidence_label', 'id' => $labelid]
        );

        // role/aria-labelledby rather than fieldset+legend: this markup is
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

            // The radio sits INSIDE its label, matching the .al-radio-pills
            // pattern already used by the pre-test entry screen. This keeps a
            // radio and its text together as one pill when the row wraps, and
            // makes the whole pill a click target. An earlier revision put the
            // radio and label side by side inside a wrapper div, which silently
            // broke the "selected pill" highlight, because the brand
            // stylesheet's rule matches the label as the radio's next sibling.
            $html .= html_writer::tag('label',
                html_writer::empty_tag('input', $attrs) .
                html_writer::tag('span', $label, ['class' => 'qbehaviour_confidence_optiontext']),
                ['class' => 'qbehaviour_confidence_option', 'for' => $inputid]
            );
        }

        $html .= html_writer::end_div();
        $html .= html_writer::end_div();

        return parent::controls($qa, $options) . $html;
    }
}
