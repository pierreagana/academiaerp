<?php

namespace App\Modules\Canteen\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\Canteen\Domain\Repositories\MenuRepositoryInterface;
use App\Modules\Canteen\Infrastructure\Repositories\EloquentMenuRepository;

use App\Modules\Canteen\Domain\Repositories\ProductRepositoryInterface;
use App\Modules\Canteen\Infrastructure\Repositories\EloquentProductRepository;

use App\Modules\Canteen\Domain\Repositories\StockMovementRepositoryInterface;
use App\Modules\Canteen\Infrastructure\Repositories\EloquentStockMovementRepository;

use App\Modules\Canteen\Domain\Repositories\AccountRepositoryInterface;
use App\Modules\Canteen\Infrastructure\Repositories\EloquentAccountRepository;

use App\Modules\Canteen\Domain\Repositories\MealRecordRepositoryInterface;
use App\Modules\Canteen\Infrastructure\Repositories\EloquentMealRecordRepository;

use App\Modules\Canteen\Domain\Repositories\MenuTagRepositoryInterface;
use App\Modules\Canteen\Infrastructure\Repositories\EloquentMenuTagRepository;

use App\Modules\Canteen\Domain\Repositories\AllergenRepositoryInterface;
use App\Modules\Canteen\Infrastructure\Repositories\EloquentAllergenRepository;

class CanteenServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MenuRepositoryInterface::class, EloquentMenuRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(StockMovementRepositoryInterface::class, EloquentStockMovementRepository::class);
        $this->app->bind(AccountRepositoryInterface::class, EloquentAccountRepository::class);
        $this->app->bind(MealRecordRepositoryInterface::class, EloquentMealRecordRepository::class);
        $this->app->bind(MenuTagRepositoryInterface::class, EloquentMenuTagRepository::class);
        $this->app->bind(AllergenRepositoryInterface::class, EloquentAllergenRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
