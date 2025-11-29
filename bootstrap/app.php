<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Resolve base path correctly on AWS EB
$basePath = dirname(__DIR__);
if (is_dir('/var/app/current')) {
    $basePath = '/var/app/current';
} elseif (is_dir('/var/app/staging')) {
    $basePath = '/var/app/staging';
}

return Application::configure(basePath: $basePath)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add security headers to all responses
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        
        // Ensure CSRF protection is enabled for web routes
        $middleware->web(append: [
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
        
        // Register middleware aliases
        $middleware->alias([
            'check.permission' => \App\Http\Middleware\CheckPermission::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
            'device.fingerprint' => \App\Http\Middleware\CheckDeviceFingerprint::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
