<?php

namespace App\Modules\SchoolDashboard\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class SchoolDashboardServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind interfaces and implementations if needed for the school dashboard
    }

    public function boot()
    {
        // Load views for the module
        $this->loadViewsFrom(__DIR__.'/../Presentation/Views', 'SchoolDashboard');

        // Load routes
        \Illuminate\Support\Facades\Route::middleware(['web'])
            ->group(__DIR__.'/../Presentation/Routes/web.php');
    }
}
