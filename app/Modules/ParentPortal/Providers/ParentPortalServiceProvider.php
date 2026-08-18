<?php

namespace App\Modules\ParentPortal\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ParentPortalServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../Presentation/Views', 'ParentPortal');

        Route::middleware(['web'])
            ->group(__DIR__.'/../Presentation/Routes/web.php');
    }
}
