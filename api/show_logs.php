<?php
/**
 * Show PHP Error Log
 * Display the last 50 lines of the PHP error log
 */

header('Content-Type: text/plain');

echo "=== PHP Error Log (Last 50 Lines) ===\n\n";

// Try different possible log locations
$log_locations = [
    '/var/log/apache2/error.log',
    '/var/log/php_errors.log',
    '/tmp/php_errors.log',
    ini_get('error_log')
];

foreach ($log_locations as $log_file) {
    if ($log_file && file_exists($log_file)) {
        echo "Found log file: {$log_file}\n\n";
        $lines = file($log_file);
        $last_lines = array_slice($lines, -50);
        echo implode('', $last_lines);
        break;
    }
}

if (!isset($lines)) {
    echo "Could not find error log file.\n";
    echo "Tried locations:\n";
    foreach ($log_locations as $loc) {
        echo "- {$loc}\n";
    }
}
