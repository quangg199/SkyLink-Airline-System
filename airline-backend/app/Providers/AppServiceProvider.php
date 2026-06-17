<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\BookingObserver;
use App\Models\Booking;
use App\Services\CheckInService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ensure the check-in service is bound so facades and app('check-in-service') work
        $this->app->singleton('check-in-service', function () {
            return new CheckInService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Booking::observe(BookingObserver::class);
    }
}
