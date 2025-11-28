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
        // CRITICAL: Ensure directories exist in BOTH locations
        // Laravel might resolve storage_path() to staging or current
        
        // Paths in /var/app/current (where app runs)
        $currentBase = '/var/app/current';
        $currentPaths = [
            $currentBase . '/storage/framework/views',
            $currentBase . '/storage/framework/cache',
            $currentBase . '/storage/framework/sessions',
            $currentBase . '/storage/logs',
            $currentBase . '/bootstrap/cache',
        ];
        
        // Paths in /var/app/staging (where composer runs, Laravel might resolve here)
        $stagingBase = '/var/app/staging';
        $stagingPaths = [
            $stagingBase . '/storage/framework/views',
            $stagingBase . '/storage/framework/cache',
            $stagingBase . '/storage/framework/sessions',
            $stagingBase . '/storage/logs',
            $stagingBase . '/bootstrap/cache',
        ];
        
        // Also use Laravel's storage_path() and base_path() (whatever they resolve to)
        $laravelPaths = [
            storage_path('framework/views'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];
        
        // Combine all paths
        $allPaths = array_unique(array_merge($currentPaths, $stagingPaths, $laravelPaths));
        
        foreach ($allPaths as $path) {
            // Normalize path separators
            $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            
            // Skip if path doesn't exist (e.g., staging might not exist at runtime)
            $baseDir = dirname($path, 3); // Go up 3 levels to check base
            if (!is_dir($baseDir)) {
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
}
