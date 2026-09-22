<?php
/**
 * Demonstrates that the AI model - not the Chapter 3 formula - is deciding a
 * student's mastery, pathway and ordering.
 *
 *   sudo -u www-data php verify_ai_decides.php [userid]      (default 3, teststudent)
 *
 * Three tests:
 *   1. DIFFERENTIAL  the model's decision and the formula's, on identical
 *                    evidence, side by side. Every disagreement is a decision
 *                    the formula did not make. (Stores nothing.)
 *   2. PROVENANCE    what the stored track says about who decided it.
 *   3. ABLATION      the model is made unreachable and the track regenerated:
 *                    it must fall back to the formula AND record that it did.
 *                    Regenerated again normally, it must flip back to the
 *                    model. This REWRITES the student's stored track (twice),
 *                    so it only runs on a track the model already decided -
 *                    never on a rule-era study track.
 *
 * --ablate is internal: test 3 runs itself in a subprocess with that flag,
 * because the override has to be defined before the engine is loaded.
 */

$userid = 3;
$ablate = false;
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--ablate') {
        $ablate = true;
    } else if (ctype_digit($arg)) {
        $userid = (int) $arg;
    }
}

if ($ablate) {
    // Nonexistent model -> OpenAI returns 4xx -> DecisionEngine throws ->
    // TrackEngine falls back to the formula.
    define('ACCULEARN_DECISION_MODEL', 'acculearn-ablation-no-such-model');
    require_once __DIR__ . '/track_engine.php';
    $engine = new TrackEngine();
    $result = $engine->generateTrack($userid, '[STUDENT]', 'quiz', true);
    echo json_encode($result['decision']) . "\n";
    exit(0);
}

require_once __DIR__ . '/track_engine.php';

function say($s) { echo $s . "\n"; }
function lp($v) { return $v === null ? 'null' : (string) $v; }
function verdict($ok, $label) { say(($ok ? '  PASS  ' : '  FAIL  ') . $label); return $ok; }

$allok = true;
$mastery = new MasteryEngine();
say("AccuLearn: is the AI deciding? user $userid, DECISION_MODE = " . MasteryEngine::DECISION_MODE);
say(str_repeat('=', 78));

// ---- 1. differential ---------------------------------------------------
say("\n1. DIFFERENTIAL - same evidence, formula vs model (nothing stored)");
$evidence = $mastery->computeFullEvidence($userid, 'quiz');
$rule = [];
foreach ($mastery->computeFullTrack($userid, 'quiz') as $r) {
    $rule[$r['competency_id']] = $r;
}
try {
    $t0 = microtime(true);
    $ai = (new DecisionEngine())->decide($evidence, '[STUDENT]', 'quiz');
    $secs = microtime(true) - $t0;
} catch (Exception $e) {
    say("  model call failed: " . $e->getMessage());
    say("  (the system would fall back to the formula here - fix the model access before reading anything else)");
    exit(1);
}
printf("  %-46s %-26s %-26s\n", 'competency', 'FORMULA', 'MODEL');
$differs = 0;
foreach ($ai['competencies'] as $c) {
    $r = $rule[$c['competency_id']];
    $same = ($r['lp'] === $c['lp'] && $r['mastery'] === $c['mastery']);
    if (!$same) $differs++;
    printf("  %-46s %-26s %-26s %s\n",
        mb_substr($c['competency_name'], 0, 45),
        "{$r['mastery']} / LP " . lp($r['lp']) . " [{$r['priority_group']}]",
        "{$c['mastery']} / LP " . lp($c['lp']) . " [{$c['priority_group']}]",
        $same ? '' : '<- differs');
}
printf("  model answered in %.1fs; disagrees with the formula on %d of %d competencies\n", $secs, $differs, count($ai['competencies']));
say("  a rationale only a model writes: \"" . $ai['competencies'][0]['rationale'] . "\"");
if ($differs === 0) {
    say("  note: agreement is not disproof - the model can reach the same verdict as the formula.");
    say("        Tests 2 and 3 are the ones that show who actually decided.");
}

// ---- 2. provenance -----------------------------------------------------
say("\n2. PROVENANCE - what the stored track says");
$stored = $mastery->storedTrack($userid);
if ($stored === null) {
    say("  no stored track for user $userid - the gate is using the formula until one is generated.");
    $allok = false;
} else {
    $d = $stored['decision'] ?? null;
    if ($d === null) {
        say("  stored track has NO decision block: a rule-era track (study data); not re-decided until this student submits.");
        $allok = verdict(false, 'stored track decided by the model') && $allok;
    } else {
        say("  decision.by = {$d['by']}   model = " . ($d['model'] ?? 'none') . "   decided_at = " . date('Y-m-d H:i:s', $d['decided_at'])
            . ($d['fallback_reason'] ? "\n  fallback_reason = {$d['fallback_reason']}" : ''));
        $allok = verdict($d['by'] === 'ai', 'stored track decided by the model') && $allok;
        $gate = $mastery->currentPathway($userid, $stored['competencies'][0]['competency_id']);
        $allok = verdict($gate['decided_by'] === 'ai', "quiz gate reads the stored model decision (decided_by = {$gate['decided_by']})") && $allok;
    }
}

// ---- 3. ablation -------------------------------------------------------
say("\n3. ABLATION - model made unreachable, then restored");
if ($stored === null || (($stored['decision']['by'] ?? null) === null)) {
    say("  skipped: only runs on a track the model has already decided (never rewrites a study track).");
} else {
    $cmd = 'php ' . escapeshellarg(__FILE__) . ' ' . $userid . ' --ablate 2>/dev/null';
    $out = trim((string) shell_exec($cmd));
    $abl = json_decode($out, true);
    if (!is_array($abl)) {
        say("  ablation subprocess produced no result: $out");
        $allok = false;
    } else {
        say("  with the model unreachable: decision.by = {$abl['by']}");
        say("    fallback_reason = " . mb_substr((string) $abl['fallback_reason'], 0, 110) . '...');
        $allok = verdict($abl['by'] === 'rule' && !empty($abl['fallback_reason']),
            'without the model, the formula decides and the track says so') && $allok;

        $fresh = new MasteryEngine();
        $gate = $fresh->currentPathway($userid, $evidence[0]['competency_id']);
        $allok = verdict($gate['decided_by'] === 'rule', "gate now reads a rule decision (decided_by = {$gate['decided_by']})") && $allok;

        // Not forced: a fallback track is retried by design, so this
        // regeneration must reach the model on its own.
        $engine = new TrackEngine();
        $back = $engine->generateTrack($userid, '[STUDENT]', 'quiz');
        say("  model restored, regenerated: decision.by = {$back['decision']['by']}, stored = " . var_export($engine->lastRunStoredTrack(), true));
        $allok = verdict($back['decision']['by'] === 'ai' && $engine->lastRunStoredTrack(),
            'with the model back, it decides again (a fallback track is always retried)') && $allok;
    }
}

say("\n" . str_repeat('=', 78));
say($allok ? "RESULT: the AI model is deciding for user $userid." : "RESULT: at least one check failed - see above.");
exit($allok ? 0 : 1);
