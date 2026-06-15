<?php

namespace App\Providers;

use App\Models\Grievance;
use App\Models\Incident;
use App\Models\LeaveRequest;
use App\Observers\GrievanceObserver;
use App\Observers\IncidentObserver;
use App\Observers\LeaveRequestObserver;
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
        // ----------------------------------------------------------------
        // Model Observers — trigger notifications on key events
        // ----------------------------------------------------------------
        Incident::observe(IncidentObserver::class);
        LeaveRequest::observe(LeaveRequestObserver::class);
        Grievance::observe(GrievanceObserver::class);
    }
}
