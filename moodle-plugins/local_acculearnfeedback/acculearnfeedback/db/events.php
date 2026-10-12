<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Event observers feeding the AccuLearn usage log.
 *
 * The usage-log page always claimed to record "every module view, quiz
 * submission, and AI feedback/track generation event" - but nothing was
 * registered to observe any of that. In practice only two things were ever
 * logged, both from explicit calls inside AccuLearn's own pages
 * (view_track, view_feedback), so the engagement dataset the study depends
 * on (thesis Sections 3.5.3 and 3.8.1: module access, frequency of use,
 * duration of use, number of quizzes attempted) did not exist.
 *
 * Observing Moodle's own events is the right way to capture this: it catches
 * the activity wherever it happens, including the SCORM lessons and quizzes
 * that live outside this plugin's pages entirely.
 *
 * All observers are non-blocking (internal => false is the default; each
 * callback swallows its own errors) so logging can never interrupt a
 * student mid-attempt.
 */
$observers = [

    // Frequency of use / duration of use - the session boundary markers.
    [
        'eventname' => '\core\event\user_loggedin',
        'callback'  => '\local_acculearnfeedback\observer::user_loggedin',
    ],

    // Module access. Registered per module because Moodle dispatches the
    // concrete subclass, not the \core\event\course_module_viewed base.
    [
        'eventname' => '\mod_scorm\event\course_module_viewed',
        'callback'  => '\local_acculearnfeedback\observer::lesson_viewed',
    ],
    [
        'eventname' => '\mod_scorm\event\sco_launched',
        'callback'  => '\local_acculearnfeedback\observer::sco_launched',
    ],
    [
        'eventname' => '\mod_quiz\event\course_module_viewed',
        'callback'  => '\local_acculearnfeedback\observer::quiz_viewed',
    ],

    // Number of quizzes attempted, and completion of an attempt.
    [
        'eventname' => '\mod_quiz\event\attempt_started',
        'callback'  => '\local_acculearnfeedback\observer::quiz_attempt_started',
    ],
    [
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback'  => '\local_acculearnfeedback\observer::quiz_attempt_submitted',
    ],

    // Which competency course a student actually opened.
    [
        'eventname' => '\core\event\course_viewed',
        'callback'  => '\local_acculearnfeedback\observer::course_viewed',
    ],

    // Question bank changes. Re-tag the bank's questions to their competency
    // (an edit in Moodle 5 is a new question id, which the mastery engine
    // would otherwise not count) and re-point the quizzes' random slots at
    // the difficulty pools - see classes/question_pools.php. internal => false
    // so these run after the question's own transaction has committed.
    [
        'eventname' => '\core\event\question_created',
        'callback'  => '\local_acculearnfeedback\observer::question_bank_changed',
        'internal'  => false,
    ],
    [
        'eventname' => '\core\event\question_updated',
        'callback'  => '\local_acculearnfeedback\observer::question_bank_changed',
        'internal'  => false,
    ],
    [
        'eventname' => '\core\event\question_moved',
        'callback'  => '\local_acculearnfeedback\observer::question_bank_changed',
        'internal'  => false,
    ],
    [
        'eventname' => '\core\event\question_deleted',
        'callback'  => '\local_acculearnfeedback\observer::question_bank_changed',
        'internal'  => false,
    ],
    [
        'eventname' => '\core\event\questions_imported',
        'callback'  => '\local_acculearnfeedback\observer::question_bank_changed',
        'internal'  => false,
    ],
];
