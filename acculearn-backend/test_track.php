<?php
require_once __DIR__ . '/track_engine.php';

$userid = 3; // teststudent

$engine = new TrackEngine();

try {
    $result = $engine->generateTrack($userid, 'Test Student');
    echo "TRACK GENERATED SUCCESSFULLY\n";
    echo "Decided by: {$result['decision']['by']}"
        . ($result['decision']['model'] ? " ({$result['decision']['model']})" : '')
        . ($result['decision']['fallback_reason'] ? " - fallback because: {$result['decision']['fallback_reason']}" : '')
        . "\n\n";
    echo "=== Narrative ===\n";
    print_r($result['narrative']);
    echo "\n=== Full Competency Track ===\n";
    foreach ($result['competencies'] as $c) {
        echo "[{$c['priority_group']}] {$c['competency_name']}: {$c['mastery']} -> LP "
            . ($c['lp'] ?? 'null') . " ({$c['lp_label']})\n";
        if (!empty($c['rationale'])) {
            echo "    rationale: {$c['rationale']}\n";
        }
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
