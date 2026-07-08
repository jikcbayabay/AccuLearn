<?php
require_once __DIR__ . '/moodle_client.php';

$client = new MoodleClient();

try {
    $info = $client->getSiteInfo();
    echo "SUCCESS\n";
    print_r($info);
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}

