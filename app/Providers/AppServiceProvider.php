<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Fund;
use App\Observers\FundObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the fund observer
        Fund::observe(FundObserver::class);
    }
    
}
