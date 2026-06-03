<?php

return [
    'base_url' => env('MOODLE_BASE_URL', ''),
    'token'    => env('MOODLE_TOKEN', ''),
    'format'   => env('MOODLE_FORMAT', 'json'),
    'timeout'  => env('MOODLE_TIMEOUT', 30),
    'retries'  => env('MOODLE_RETRIES', 2),
];
