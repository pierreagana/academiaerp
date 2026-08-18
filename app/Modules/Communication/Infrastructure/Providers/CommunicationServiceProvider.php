<?php

namespace App\Modules\Communication\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\Communication\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Communication\Infrastructure\Repositories\EloquentEventRepository;

use App\Modules\Communication\Domain\Repositories\EventRegistrationRepositoryInterface;
use App\Modules\Communication\Infrastructure\Repositories\EloquentEventRegistrationRepository;

class CommunicationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EventRepositoryInterface::class, EloquentEventRepository::class);
        $this->app->bind(EventRegistrationRepositoryInterface::class, EloquentEventRegistrationRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
