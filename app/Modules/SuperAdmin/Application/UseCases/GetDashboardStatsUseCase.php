<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Entities\DashboardStats;
use App\Modules\SuperAdmin\Domain\Repositories\DashboardRepositoryInterface;

class GetDashboardStatsUseCase
{
    public function __construct(
        private DashboardRepositoryInterface $dashboardRepository
    ) {}

    public function execute(): DashboardStats
    {
        return $this->dashboardRepository->getStats();
    }
}
