<?php

namespace App\Modules\SuperAdmin\Domain\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\DashboardStats;

interface DashboardRepositoryInterface
{
    public function getStats(): DashboardStats;
}
