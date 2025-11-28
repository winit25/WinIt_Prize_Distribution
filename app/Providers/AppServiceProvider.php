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
        $storagePaths = [
            storage_path('framework/views'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];
        
        foreach ($storagePaths as $path) {
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
