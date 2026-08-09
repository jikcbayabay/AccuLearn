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

];
