<?php
/**
 * Cron Job Script: Cache Cleanup
 * Deletes files in api/cache/ older than 48 hours.
 * Usage: php api/cleanup.php
 */

header('Content-Type: text/plain');

// Configuration
define('CACHE_DIR', __DIR__ . '/cache/');
define('MAX_AGE', 48 * 3600); // 48 Hours in seconds

// Check existence
if (!is_dir(CACHE_DIR)) {
    die("Cache directory does not exist: " . CACHE_DIR);
}

// Stats
$count = 0;
$deleted = 0;
$errors = 0;
$now = time();

// Iterate files
$files = glob(CACHE_DIR . '*'); 

foreach ($files as $file) {
    if (is_file($file)) {
        $count++;
        // Check age
        if (($now - filemtime($file)) >= MAX_AGE) {
            if (unlink($file)) {
                $deleted++;
            } else {
                $errors++;
            }
        }
    }
}

// Output Report
echo "--- Cache Cleanup Report ---\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "Scanned: $count files\n";
echo "Deleted: $deleted files (Older than 48h)\n";
echo "Errors:  $errors\n";
echo "----------------------------\n";
?>
