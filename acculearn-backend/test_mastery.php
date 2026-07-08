<?php
require_once __DIR__ . '/mastery_engine.php';

$userid = 3; // teststudent

$engine = new MasteryEngine();
$track = $engine->computeFullTrack($userid);

foreach ($track as $t) {
    echo "Competency: {$t['competency_name']}\n";
    echo "  CM: " . ($t['cm'] ?? 'N/A') . "%  CMI: " . ($t['cmi'] ?? 'N/A') . "  GI: " . ($t['gi'] ?? 'N/A') . "\n";
    echo "  PR: {$t['pr']}  Mastery: {$t['mastery']}\n";
    echo "  LP: " . ($t['lp'] ?? 'N/A') . " ({$t['lp_label']})\n\n";
}
