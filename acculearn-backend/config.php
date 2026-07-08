<?php
function load_env($path) {
    $env = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $env[$key] = $value;
    }
    return $env;
}

$env = load_env(__DIR__ . '/.env');

define('OPENAI_API_KEY', $env['OPENAI_API_KEY']);
define('MOODLE_URL', rtrim($env['MOODLE_URL'], '/'));
define('MOODLE_TOKEN', $env['MOODLE_TOKEN']);
define('DB_HOST', $env['DB_HOST']);
define('DB_NAME', $env['DB_NAME']);
define('DB_USER', $env['DB_USER']);
define('DB_PASS', $env['DB_PASS']);
