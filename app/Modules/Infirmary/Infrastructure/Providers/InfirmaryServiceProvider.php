<?php

namespace App\Modules\Infirmary\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\Infirmary\Domain\Repositories\ConsultationMotiveRepositoryInterface;
use App\Modules\Infirmary\Infrastructure\Repositories\EloquentConsultationMotiveRepository;

use App\Modules\Infirmary\Domain\Repositories\InterventionRepositoryInterface;
use App\Modules\Infirmary\Infrastructure\Repositories\EloquentInterventionRepository;

use App\Modules\Infirmary\Domain\Repositories\MedicationRepositoryInterface;
use App\Modules\Infirmary\Infrastructure\Repositories\EloquentMedicationRepository;

use App\Modules\Infirmary\Domain\Repositories\MedicationMovementRepositoryInterface;
use App\Modules\Infirmary\Infrastructure\Repositories\EloquentMedicationMovementRepository;

class InfirmaryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ConsultationMotiveRepositoryInterface::class, EloquentConsultationMotiveRepository::class);
        $this->app->bind(InterventionRepositoryInterface::class, EloquentInterventionRepository::class);
        $this->app->bind(MedicationRepositoryInterface::class, EloquentMedicationRepository::class);
        $this->app->bind(MedicationMovementRepositoryInterface::class, EloquentMedicationMovementRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
