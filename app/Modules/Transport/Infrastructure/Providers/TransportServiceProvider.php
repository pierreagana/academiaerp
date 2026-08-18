<?php

namespace App\Modules\Transport\Infrastructure\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

use App\Modules\Transport\Domain\Models\Driver;

use App\Modules\Transport\Domain\Repositories\BusRepositoryInterface;
use App\Modules\Transport\Infrastructure\Repositories\EloquentBusRepository;

use App\Modules\Transport\Domain\Repositories\RouteRepositoryInterface;
use App\Modules\Transport\Infrastructure\Repositories\EloquentRouteRepository;

use App\Modules\Transport\Domain\Repositories\RouteStopRepositoryInterface;
use App\Modules\Transport\Infrastructure\Repositories\EloquentRouteStopRepository;

use App\Modules\Transport\Domain\Repositories\TripLogRepositoryInterface;
use App\Modules\Transport\Infrastructure\Repositories\EloquentTripLogRepository;

use App\Modules\Transport\Domain\Repositories\DriverRepositoryInterface;
use App\Modules\Transport\Infrastructure\Repositories\EloquentDriverRepository;

class TransportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BusRepositoryInterface::class, EloquentBusRepository::class);
        $this->app->bind(RouteRepositoryInterface::class, EloquentRouteRepository::class);
        $this->app->bind(RouteStopRepositoryInterface::class, EloquentRouteStopRepository::class);
        $this->app->bind(TripLogRepositoryInterface::class, EloquentTripLogRepository::class);
        $this->app->bind(DriverRepositoryInterface::class, EloquentDriverRepository::class);
    }

    public function boot(): void
    {
        Relation::morphMap([
            'driver' => Driver::class,
        ]);
    }
}
