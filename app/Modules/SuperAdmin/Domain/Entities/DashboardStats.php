<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

class DashboardStats
{
    public function __construct(
        public int $totalSchools,
        public ?string $totalSchoolsGrowth,
        public int $activeSubscriptions,
        public ?string $activeSubscriptionsGrowth,
        public string $totalRevenues,
        public ?string $totalRevenuesGrowth,
        public string $activeUsers,
        public ?string $activeUsersGrowth,
        public array $recentActivities = [],
        public array $monthlyRevenueData = []
    ) {}
}
