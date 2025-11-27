<?php

/**
 * Ensure storage directories exist before Laravel compiles views
 * This script MUST run before any Laravel code executes
 */

$dirs = [
    'storage/framework/views',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/logs',
    'bootstrap/cache',
];

$paths = ['/var/app/staging', getcwd()];

$created = 0;
foreach ($paths as $base) {
    if (!is_dir($base)) {
        continue;
    }
    
    foreach ($dirs as $dir) {
        $path = $base . '/' . $dir;
        
        if (!is_dir($path)) {
            if (@mkdir($path, 0777, true)) {
                @chmod($path, 0777);
                $created++;
            } else {
                error_log("ERROR: Failed to create directory: $path");
                exit(1);
            }
        } else {
            // Ensure writable
            @chmod($path, 0777);
        }
        
        // Verify it exists and is writable
        if (!is_dir($path)) {
            error_log("ERROR: Directory does not exist: $path");
            exit(1);
        }
        if (!is_writable($path)) {
            error_log("ERROR: Directory not writable: $path");
            @chmod($path, 0777);
        }
    }
}

if ($created > 0) {
    echo "Created $created storage directories\n";
} else {
    echo "Storage directories verified\n";
}
