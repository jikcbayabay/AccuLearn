<?php
/**
 * Dry run of the AI decision layer against a real student, side by side
 * with what the Chapter 3 formula would have decided on the same evidence.
 * Stores nothing. Run as: php test_decision.php [userid]
 */
require_once __DIR__ . '/mastery_engine.php';
require_once __DIR__ . '/decision_engine.php';

$userid = isset($argv[1]) ? (int) $argv[1] : 3; // teststudent
$source = isset($argv[2]) ? $argv[2] : 'quiz';

$mastery = new MasteryEngine();
$evidence = $mastery->computeFullEvidence($userid, $source);
$rule = $mastery->computeFullTrack($userid, $source);
$rulebyid = [];
foreach ($rule as $r) {
    $rulebyid[$r['competency_id']] = $r;
}

echo "User $userid, source $source, evidence signature "
    . MasteryEngine::evidenceSignature($evidence) . "\n\n";

$t0 = microtime(true);
try {
    $decided = (new DecisionEngine())->decide($evidence, '[STUDENT]', $source);
} catch (Exception $e) {
    echo "AI DECISION FAILED (the rule would be used): " . $e->getMessage() . "\n";
    exit(1);
}
printf("Model answered in %.1fs\n\n", microtime(true) - $t0);

foreach ($decided['competencies'] as $c) {
    $r = $rulebyid[$c['competency_id']];
    $agree = ($r['lp'] === $c['lp'] && $r['mastery'] === $c['mastery']) ? 'same as rule' : 'DIFFERS from rule';
    echo "[{$c['priority_group']}] {$c['competency_name']}\n";
    echo "  evidence: CM " . ($c['cm'] ?? 'null') . "  CMI " . ($c['cmi'] ?? 'null') . "  GI " . ($c['gi'] ?? 'null')
        . "  items {$c['attempted']}  quizzes {$c['quizzes_passed']}/{$c['quizzes_total']} passed\n";
    echo "  AI:   {$c['mastery']} / LP " . ($c['lp'] ?? 'null') . " ({$c['lp_label']})   <- $agree\n";
    echo "  rule: {$r['mastery']} / LP " . ($r['lp'] ?? 'null') . " ({$r['lp_label']}) [{$r['priority_group']}]\n";
    echo "  why:  {$c['rationale']}\n";
    echo "  to student: {$c['priority_reason']}\n\n";
}

echo "=== Narrative ===\n";
print_r($decided['narrative']);
