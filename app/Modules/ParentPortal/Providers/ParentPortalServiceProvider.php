<?php

namespace App\Modules\ParentPortal\Providers;

use App\Modules\ParentPortal\Application\Services\ParentPortalService;
use App\Modules\SchoolTrack\Application\Services\SchoolTrackAccessService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
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

        // Shared to every parent page so the notification bell in the layout
        // always shows an up-to-date badge, without every controller having
        // to remember to pass it in.
        View::composer('ParentPortal::*', function ($view) {
            $parent = Auth::guard('parent')->user();
            $view->with('unreadNotificationsCount', $parent ? $parent->notificationLogs()->whereNull('read_at')->count() : 0);

            // Feeds the child-switcher dropdown in the layout's per-child subnav —
            // set here (not per-controller) so every one of the 8 per-student
            // pages gets it for free instead of repeating childrenOf() 8 times.
            if ($parent && array_key_exists('child', $view->getData())) {
                $view->with('siblingChildren', app(ParentPortalService::class)->childrenOf($parent));
            }

            // Feeds the sidebar's "School Track" entry (active vs. locked) on
            // every page — the dashboard controller already computes its own
            // copy for the subscribe-modal card, so this only fills the gap
            // for every other page rather than recomputing it twice.
            if (!array_key_exists('schoolTrackStatus', $view->getData())) {
                $view->with('schoolTrackStatus', app(SchoolTrackAccessService::class)->statusFor($parent));
            }
        });
    }
}
