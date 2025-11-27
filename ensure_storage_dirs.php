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
                // Log error but don't exit - let composer continue
                error_log("WARNING: Failed to create directory: $path");
            }
        } else {
            // Ensure writable
            @chmod($path, 0777);
        }
        
        // Verify it exists and is writable
        if (!is_dir($path)) {
            error_log("WARNING: Directory does not exist: $path");
        } else if (!is_writable($path)) {
            error_log("WARNING: Directory not writable: $path");
            @chmod($path, 0777);
        }
    }
}

if ($created > 0) {
    echo "Created $created storage directories\n";
} else {
    echo "Storage directories verified\n";
}
