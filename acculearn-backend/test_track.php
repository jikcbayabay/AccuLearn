<?php
require_once __DIR__ . '/track_engine.php';

$userid = 3; // teststudent

$engine = new TrackEngine();

try {
    $result = $engine->generateTrack($userid, 'Test Student');
    echo "TRACK GENERATED SUCCESSFULLY\n\n";
    echo "=== Narrative ===\n";
    print_r($result['narrative']);
    echo "\n=== Full Competency Track ===\n";
    foreach ($result['competencies'] as $c) {
        echo "{$c['competency_name']}: {$c['mastery']} -> {$c['lp_label']}\n";
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
