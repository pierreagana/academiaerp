<?php

namespace App\Modules\Cards\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\Cards\Domain\Repositories\CardTemplateRepositoryInterface;
use App\Modules\Cards\Infrastructure\Repositories\EloquentCardTemplateRepository;

class CardsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CardTemplateRepositoryInterface::class, EloquentCardTemplateRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
