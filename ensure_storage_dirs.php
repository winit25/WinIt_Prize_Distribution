<?php

/**
 * Ensure storage directories exist before Laravel compiles views
 * This script is called by composer before package discovery
 */

$dirs = [
    'storage/framework/views',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/logs',
    'bootstrap/cache',
];

$paths = ['/var/app/staging', getcwd()];

foreach ($paths as $base) {
    if (!is_dir($base)) {
        continue;
    }
    
    foreach ($dirs as $dir) {
        $path = $base . '/' . $dir;
        
        if (!is_dir($path)) {
            if (!@mkdir($path, 0777, true)) {
                error_log("Failed to create directory: $path");
                continue;
            }
        }
        
        // Ensure writable
        @chmod($path, 0777);
        
        // Verify it exists
        if (!is_dir($path)) {
            error_log("Directory still does not exist after creation: $path");
        }
    }
}

echo "Storage directories ensured\n";

