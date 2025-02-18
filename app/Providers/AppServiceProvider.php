<?php

namespace App\Providers;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Observers\ProductVariantObserver;
use App\Observers\StockMovementObserver;
use Illuminate\Support\ServiceProvider;

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
        // ProductVariant::observe(ProductVariantObserver::class);
        StockMovement::observe(StockMovementObserver::class);
    }
}
