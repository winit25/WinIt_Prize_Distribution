<?php

namespace App\Providers;

use App\Contracts\BuyPowerApiInterface;
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
        // Bind both the alias and the class itself
        $this->app->singleton('buypower.api', function ($app) {
            // Use mock service for development when API is not available
            $useMock = config('buypower.use_mock', false);
            
            if ($useMock) {
                return new MockBuyPowerApiService();
            }
            
            return new BuyPowerApiService();
        });
        
        // Bind the interface to the same instance
        $this->app->singleton(BuyPowerApiInterface::class, function ($app) {
            return $app->make('buypower.api');
        });
        
        // Also bind the concrete class for backward compatibility
        $this->app->singleton(BuyPowerApiService::class, function ($app) {
            return $app->make('buypower.api');
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
