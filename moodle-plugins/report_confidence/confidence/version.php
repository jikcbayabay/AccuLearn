<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component    = 'report_confidence';
$plugin->version       = 2026072902;
$plugin->requires      = 2022041900;
$plugin->maturity      = MATURITY_STABLE;
$plugin->release       = '1.0.0';
// export.php now reuses local_acculearnfeedback's pseudonym and CSV-escaping
// helpers so this report anonymises students the same way the rest of
// AccuLearn does - declared so Moodle enforces install/upgrade ordering
// rather than leaving it to a require_once that could fail at runtime.
$plugin->dependencies  = [
    'local_acculearnfeedback' => 2026072901,
];

