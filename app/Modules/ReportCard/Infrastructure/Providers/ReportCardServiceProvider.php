<?php

namespace App\Modules\ReportCard\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\ReportCard\Domain\Repositories\ReportCardDomainRepositoryInterface;
use App\Modules\ReportCard\Infrastructure\Repositories\EloquentReportCardDomainRepository;

use App\Modules\ReportCard\Domain\Repositories\ReportCardSubdomainRepositoryInterface;
use App\Modules\ReportCard\Infrastructure\Repositories\EloquentReportCardSubdomainRepository;

use App\Modules\ReportCard\Domain\Repositories\ReportCardCompetencyRepositoryInterface;
use App\Modules\ReportCard\Infrastructure\Repositories\EloquentReportCardCompetencyRepository;

use App\Modules\ReportCard\Domain\Repositories\ReportCardAssessmentRepositoryInterface;
use App\Modules\ReportCard\Infrastructure\Repositories\EloquentReportCardAssessmentRepository;

use App\Modules\ReportCard\Domain\Repositories\ReportCardObservationRepositoryInterface;
use App\Modules\ReportCard\Infrastructure\Repositories\EloquentReportCardObservationRepository;

class ReportCardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ReportCardDomainRepositoryInterface::class, EloquentReportCardDomainRepository::class);
        $this->app->bind(ReportCardSubdomainRepositoryInterface::class, EloquentReportCardSubdomainRepository::class);
        $this->app->bind(ReportCardCompetencyRepositoryInterface::class, EloquentReportCardCompetencyRepository::class);
        $this->app->bind(ReportCardAssessmentRepositoryInterface::class, EloquentReportCardAssessmentRepository::class);
        $this->app->bind(ReportCardObservationRepositoryInterface::class, EloquentReportCardObservationRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
