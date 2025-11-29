<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register MonolithicService as singleton
        $this->app->singleton(\App\Services\MonolithicService::class, function ($app) {
            return new \App\Services\MonolithicService();
        });
        
        // Ensure storage directories exist BEFORE Laravel boots
        $this->ensureStorageDirectoriesExist();
        
        // Override view compiler to ensure directories exist before compilation
        $this->app->afterResolving('view', function ($view) {
            $this->ensureStorageDirectoriesExist();
        });
        
        // Ensure view finder can locate views
        $this->app->afterResolving('view.finder', function ($finder) {
            $this->ensureStorageDirectoriesExist();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure storage directories exist
        $this->ensureStorageDirectoriesExist();
    }

    /**
     * Ensure all required storage directories exist
     */
    protected function ensureStorageDirectoriesExist(): void
    {
        // CRITICAL: Fix path resolution - Laravel might resolve to wrong base path
        // Force base_path to resolve correctly on server
        $resolvedBasePath = $this->resolveBasePath();
        
        // Storage paths using resolved base path
        $storagePaths = [
            $resolvedBasePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'views',
            $resolvedBasePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache',
            $resolvedBasePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'sessions',
            $resolvedBasePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs',
            $resolvedBasePath . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache',
        ];
        
        // Also ensure in /var/app/current (where app runs)
        $currentPaths = [
            '/var/app/current/storage/framework/views',
            '/var/app/current/storage/framework/cache',
            '/var/app/current/storage/framework/sessions',
            '/var/app/current/storage/logs',
            '/var/app/current/bootstrap/cache',
        ];
        
        // Combine all paths
        $allPaths = array_unique(array_merge($storagePaths, $currentPaths));
        
        foreach ($allPaths as $path) {
            // Normalize path separators
            $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            
            // Skip if base directory doesn't exist
            $baseDir = dirname($path, 3);
            if (!is_dir($baseDir) && strpos($path, '/var/app/') !== 0) {
                continue;
            }
            
            // Create parent directories first
            $parent = dirname($path);
            if (!is_dir($parent)) {
                @File::makeDirectory($parent, 0777, true, true);
                @chmod($parent, 0777);
            }
            
            // Create the directory itself
            if (!is_dir($path)) {
                @File::makeDirectory($path, 0777, true, true);
                @chmod($path, 0777);
            }
            
            // Ensure writable
            if (is_dir($path) && !is_writable($path)) {
                @chmod($path, 0777);
            }
        }
    }
    
    /**
     * Resolve the correct base path on server
     */
    protected function resolveBasePath(): string
    {
        // Check if we're on AWS EB
        if (is_dir('/var/app/current')) {
            return '/var/app/current';
        }
        
        // Check if we're in staging
        if (is_dir('/var/app/staging')) {
            return '/var/app/staging';
        }
        
        // Fallback to Laravel's base_path()
        $basePath = base_path();
        
        // If base_path resolves to a local macOS path, force server path
        if (strpos($basePath, '/Users/') === 0 || strpos($basePath, '/var/app/') === false) {
            // Try to detect if we're on the server
            if (is_dir('/var/app/current')) {
                return '/var/app/current';
            }
            if (is_dir('/var/app/staging')) {
                return '/var/app/staging';
            }
        }
        
        return $basePath;
    }
}
