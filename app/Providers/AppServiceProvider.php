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
        // Get the actual base path - ensure we're using /var/app/current, not staging
        $basePath = base_path();
        
        // If somehow we're resolving to staging, force current
        if (strpos($basePath, '/var/app/staging') !== false) {
            $basePath = str_replace('/var/app/staging', '/var/app/current', $basePath);
        }
        
        // Ensure we're using the correct paths
        $storageBase = $basePath . DIRECTORY_SEPARATOR . 'storage';
        $frameworkBase = $storageBase . DIRECTORY_SEPARATOR . 'framework';
        
        $storagePaths = [
            $frameworkBase . DIRECTORY_SEPARATOR . 'views',
            $frameworkBase . DIRECTORY_SEPARATOR . 'cache',
            $frameworkBase . DIRECTORY_SEPARATOR . 'sessions',
            $storageBase . DIRECTORY_SEPARATOR . 'logs',
            $basePath . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache',
        ];
        
        // Also use Laravel's storage_path() as fallback
        $laravelPaths = [
            storage_path('framework/views'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];
        
        // Combine both sets of paths
        $allPaths = array_unique(array_merge($storagePaths, $laravelPaths));
        
        foreach ($allPaths as $path) {
            // Normalize path separators
            $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            
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
