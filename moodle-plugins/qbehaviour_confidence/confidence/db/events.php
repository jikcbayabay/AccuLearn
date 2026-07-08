<?php
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback'  => '\qbehaviour_confidence\observer::quiz_attempt_submitted',
    ],
];

