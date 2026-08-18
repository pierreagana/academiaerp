<?php

namespace App\Modules\HR\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\HR\Domain\Repositories\SalaryGradeRepositoryInterface;
use App\Modules\HR\Infrastructure\Repositories\EloquentSalaryGradeRepository;

use App\Modules\HR\Domain\Repositories\PayrollComponentRepositoryInterface;
use App\Modules\HR\Infrastructure\Repositories\EloquentPayrollComponentRepository;

use App\Modules\HR\Domain\Repositories\ContractTypeRepositoryInterface;
use App\Modules\HR\Infrastructure\Repositories\EloquentContractTypeRepository;

use App\Modules\HR\Domain\Repositories\ContractRepositoryInterface;
use App\Modules\HR\Infrastructure\Repositories\EloquentContractRepository;

class HRServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SalaryGradeRepositoryInterface::class, EloquentSalaryGradeRepository::class);
        $this->app->bind(PayrollComponentRepositoryInterface::class, EloquentPayrollComponentRepository::class);
        $this->app->bind(ContractTypeRepositoryInterface::class, EloquentContractTypeRepository::class);
        $this->app->bind(ContractRepositoryInterface::class, EloquentContractRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
