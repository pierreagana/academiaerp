<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\DashboardStats;
use App\Modules\SuperAdmin\Domain\Repositories\DashboardRepositoryInterface;

class MockDashboardRepository implements DashboardRepositoryInterface
{
    public function getStats(): DashboardStats
    {
        return new DashboardStats(
            totalSchools: 1284,
            totalSchoolsGrowth: '+12%',
            activeSubscriptions: 1120,
            activeSubscriptionsGrowth: '+8%',
            totalRevenues: '14.5M',
            totalRevenuesGrowth: '+24%',
            activeUsers: '45.2K',
            activeUsersGrowth: '+18%',
            recentActivities: [
                [
                    'type' => 'new_school',
                    'text' => 'Nouvelle école inscrite : Lycée d\'Excellence (Dakar)',
                    'time' => 'Il y a 2h',
                    'icon' => 'ph-buildings',
                    'color' => 'indigo'
                ],
                [
                    'type' => 'payment',
                    'text' => 'Paiement reçu : Complexe Scolaire Les Leaders (Premium)',
                    'time' => 'Il y a 4h',
                    'icon' => 'ph-money',
                    'color' => 'emerald'
                ],
                [
                    'type' => 'support',
                    'text' => 'Ticket résolu : Problème de synchronisation (ID: #4092)',
                    'time' => 'Il y a 5h',
                    'icon' => 'ph-check-circle',
                    'color' => 'blue'
                ],
                [
                    'type' => 'alert',
                    'text' => 'Alerte : Espace de stockage critique pour Institut Saint-Jean',
                    'time' => 'Hier',
                    'icon' => 'ph-warning',
                    'color' => 'amber'
                ],
            ],
            monthlyRevenueData: [65, 72, 85, 82, 95, 110, 115]
        );
    }
}
