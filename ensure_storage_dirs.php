<?php

/**
 * Ensure storage directories exist before Laravel compiles views
 * This script MUST run before any Laravel code executes
 * 
 * This script is non-fatal - it will attempt to create directories
 * but won't fail composer install if it can't
 */

// Suppress all errors to prevent composer failures
error_reporting(0);
ini_set('display_errors', 0);

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
        
        try {
            if (!is_dir($path)) {
                if (@mkdir($path, 0777, true)) {
                    @chmod($path, 0777);
                    $created++;
                }
            } else {
                // Ensure writable
                @chmod($path, 0777);
            }
        } catch (Exception $e) {
            // Silently continue - don't fail composer
        }
    }
}

// Always exit successfully to not break composer
exit(0);
