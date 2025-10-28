<?php

namespace App\Providers;

use App\Services\BuyPowerApiService;
use App\Services\MockBuyPowerApiService;
use Illuminate\Support\ServiceProvider;

class BuyPowerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('buypower.api', function ($app) {
            // Use mock service for development when API is not available
            $useMock = config('buypower.use_mock', true);
            
            if ($useMock) {
                return new MockBuyPowerApiService();
            }
            
            return new BuyPowerApiService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
