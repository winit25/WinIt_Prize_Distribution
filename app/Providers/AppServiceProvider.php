<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ensure storage directories exist before Laravel boots
        $this->ensureStorageDirectoriesExist();
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
            if (!is_dir($path)) {
                @mkdir($path, 0777, true);
                @chmod($path, 0777);
            }
        }
    }
}
