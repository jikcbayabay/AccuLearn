<?php
require_once __DIR__ . '/track_engine.php';

$userid = 3; // teststudent

$engine = new TrackEngine();

$mastery = new MasteryEngine();
$track = $mastery->computeFullTrack($userid, 'pretest');

foreach ($track as $t) {
    echo "{$t['competency_name']}: CM=" . ($t['cm'] ?? 'N/A') . "  Mastery={$t['mastery']}  LP={$t['lp_label']}\n";
}
