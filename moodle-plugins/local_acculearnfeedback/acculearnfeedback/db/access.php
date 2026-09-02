<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Custom capabilities for local_acculearnfeedback.
 *
 * Before this file existed, teacher_review.php / admin_sequence.php gated
 * access on unrelated borrowed capabilities (moodle/course:update,
 * moodle/site:config, moodle/role:assign) - functional, but imprecise:
 * granting "reveal real student names" required the same capability used
 * for assigning roles site-wide, and there was no way to give someone
 * "review AI output" access without also granting course:update.
 *
 * Site administrators are unaffected by this change - admins pass every
 * capability check regardless of role definitions.
 */
$capabilities = [

    'local/acculearnfeedback:reviewaioutput' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],

    'local/acculearnfeedback:revealstudentnames' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],

    'local/acculearnfeedback:managesequence' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],

    'local/acculearnfeedback:viewusagelogs' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],

    /*
     * The student-facing track view. Granted to the authenticated user role so
     * every logged-in student has it at system context (student roles are
     * assigned per-course, so a system-context check against 'student' would
     * never pass). The AccuLearn teacher role PROHIBITs it. PREVENT is not
     * enough here: has_capability_in_accessdata() treats CAP_ALLOW in any one
     * of the user's roles as sufficient, so only CAP_PROHIBIT overrides the
     * grant the authenticated user role hands out at the same context.
     */
    'local/acculearnfeedback:viewowntrack' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_ALLOW,
        ],
    ],

];
