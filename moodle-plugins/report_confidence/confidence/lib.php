<?php
defined('MOODLE_INTERNAL') || die();

function report_confidence_extend_navigation_frontpage(navigation_node $navigation, stdClass $course, context $context) {
    if (has_capability('report/confidence:view', $context)) {
        $url = new moodle_url('/report/confidence/index.php');
        $navigation->add(get_string('pluginname', 'report_confidence'), $url,
            navigation_node::TYPE_SETTING, null, 'confidencereport');
    }
}

