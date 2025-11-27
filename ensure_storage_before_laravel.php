<?php

/**
 * Ensure storage directories exist BEFORE Laravel's ComposerScripts runs
 * This must run immediately before Illuminate\Foundation\ComposerScripts::postAutoloadDump
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
            @mkdir($path, 0777, true);
            @chmod($path, 0777);
        } else {
            @chmod($path, 0777);
        }
    }
}

