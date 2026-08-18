<?php

namespace App\Modules\Finance\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\Finance\Domain\Repositories\FeeLevelRepositoryInterface;
use App\Modules\Finance\Infrastructure\Repositories\EloquentFeeLevelRepository;

use App\Modules\Finance\Domain\Repositories\PaymentRepositoryInterface;
use App\Modules\Finance\Infrastructure\Repositories\EloquentPaymentRepository;

use App\Modules\Finance\Domain\Repositories\ScholarshipTypeRepositoryInterface;
use App\Modules\Finance\Infrastructure\Repositories\EloquentScholarshipTypeRepository;

use App\Modules\Finance\Domain\Repositories\ScholarshipRepositoryInterface;
use App\Modules\Finance\Infrastructure\Repositories\EloquentScholarshipRepository;

use App\Modules\Finance\Domain\Repositories\ExpenseRepositoryInterface;
use App\Modules\Finance\Infrastructure\Repositories\EloquentExpenseRepository;

use App\Modules\Finance\Domain\Repositories\ExpenseBudgetRepositoryInterface;
use App\Modules\Finance\Infrastructure\Repositories\EloquentExpenseBudgetRepository;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FeeLevelRepositoryInterface::class, EloquentFeeLevelRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, EloquentPaymentRepository::class);
        $this->app->bind(ScholarshipTypeRepositoryInterface::class, EloquentScholarshipTypeRepository::class);
        $this->app->bind(ScholarshipRepositoryInterface::class, EloquentScholarshipRepository::class);
        $this->app->bind(ExpenseRepositoryInterface::class, EloquentExpenseRepository::class);
        $this->app->bind(ExpenseBudgetRepositoryInterface::class, EloquentExpenseBudgetRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
