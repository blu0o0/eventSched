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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Reservation::class, \App\Policies\ReservationPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Venue::class, \App\Policies\VenuePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\EmergencyReport::class, \App\Policies\EmergencyReportPolicy::class);
    }
}
