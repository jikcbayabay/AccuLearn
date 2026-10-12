<?php
/**
 * Reads a .env-style file into an array.
 *
 * Deliberately defensive, because this file is pulled in (via
 * mastery_engine.php) by quizaccess_acculearn's rule.php, which runs on
 * EVERY quiz page view for every student. The previous version called
 * file() with no readability check and destructured every line
 * unconditionally, so an unreadable or slightly malformed .env produced a
 * burst of PHP warnings rendered into the middle of a student's quiz page
 * - defeating the deliberate "fail open rather than block everyone"
 * design in rule.php, and leaking absolute server paths into the page
 * whenever display_errors is on. Missing config must degrade quietly to
 * empty values so the callers' existing guards can do their job.
 */
function load_env($path) {
    $env = [];

    if (!is_readable($path)) {
        error_log("AccuLearn: env file not readable at $path - continuing with empty config.");
        return $env;
    }

    $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        error_log("AccuLearn: failed to read env file at $path - continuing with empty config.");
        return $env;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments and any line without a KEY=VALUE separator, rather
        // than destructuring blindly (which warned on every such line).
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '') {
            continue;
        }
        // Tolerate quoted values - otherwise the quotes silently become part
        // of the secret and produce confusing downstream auth failures.
        $len = strlen($value);
        if ($len >= 2 && (($value[0] === '"' && $value[$len - 1] === '"')
                || ($value[0] === "'" && $value[$len - 1] === "'"))) {
            $value = substr($value, 1, -1);
        }
        $env[$key] = $value;
    }

    return $env;
}

$env = load_env(__DIR__ . '/.env');

// Every constant falls back to '' rather than emitting an "undefined array
// key" warning mid-page. Callers already treat empty credentials as a
// failure (the OpenAI/Moodle calls return an error that is caught and
// logged), so an incomplete .env now degrades to "AI features unavailable"
// instead of "warnings printed into the page".
foreach ([
    'OPENAI_API_KEY',
    // Separate from OPENAI_API_KEY and optional. OpenAI's usage and cost
    // endpoints live under /v1/organization/ and reject project keys
    // (sk-proj-*) with 403 - they require an organization ADMIN key
    // (sk-admin-*), which only an org owner can mint. Absent, the AI usage
    // page degrades to AccuLearn's own request counts instead of failing.
    'OPENAI_ADMIN_KEY',
    'MOODLE_TOKEN',
    'DB_HOST',
    'DB_NAME',
    'DB_USER',
    'DB_PASS',
] as $key) {
    define($key, $env[$key] ?? '');
}
define('MOODLE_URL', rtrim($env['MOODLE_URL'] ?? '', '/'));

/**
 * True when the credentials the AI middleware actually needs are present.
 * Lets callers skip work (and log something actionable) instead of making
 * a doomed API call.
 */
function acculearn_config_is_complete() {
    return OPENAI_API_KEY !== '' && DB_NAME !== '' && DB_USER !== '';
}

// Ceiling on OpenAI calls per rolling hour, across all students combined.
// Protects against a scripted burst of quiz submissions (or a bug causing
// repeated regeneration) burning API budget with no limit. Sized well
// above normal pilot usage (each quiz submission costs at most 2 calls:
// one feedback + one track regeneration) - override by adding
// OPENAI_RATE_LIMIT_PER_HOUR to .env if this deployment ever needs a
// different ceiling; no .env change is required to pick up this default.
define('OPENAI_RATE_LIMIT_PER_HOUR', isset($env['OPENAI_RATE_LIMIT_PER_HOUR']) ? (int)$env['OPENAI_RATE_LIMIT_PER_HOUR'] : 200);
