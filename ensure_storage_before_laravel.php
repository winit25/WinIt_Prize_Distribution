<?php

/**
 * Ensure storage directories exist BEFORE Laravel's ComposerScripts runs
 * This must run immediately before Illuminate\Foundation\ComposerScripts::postAutoloadDump
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

foreach ($paths as $base) {
    if (!is_dir($base)) {
        continue;
    }
    
    foreach ($dirs as $dir) {
        $path = $base . '/' . $dir;
        
        try {
            if (!is_dir($path)) {
                @mkdir($path, 0777, true);
                @chmod($path, 0777);
            } else {
                @chmod($path, 0777);
            }
        } catch (Exception $e) {
            // Silently continue - don't fail composer
        }
    }
}

// Always exit successfully to not break composer
exit(0);

