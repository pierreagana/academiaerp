<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\DashboardStats;
use App\Modules\SuperAdmin\Domain\Repositories\DashboardRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Models\School;
use App\Modules\SuperAdmin\Domain\Models\Invoice;
use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;

class EloquentDashboardRepository implements DashboardRepositoryInterface
{
    public function getStats(): DashboardStats
    {
        $totalSchools = School::count();
        $activeSchools = School::where('status', 'actif')->count();

        $totalRevenues = Invoice::where('status', 'paid')->sum('amount');
        $totalStudents = School::sum('students_count');

        $setting = GlobalSetting::where('key', 'currency')->first();
        $val = $setting ? $setting->value : 'Franc CFA (XOF)';
        $curr = 'FCFA';
        if (str_contains($val, 'XOF') || str_contains($val, 'CFA')) $curr = 'FCFA';
        elseif (str_contains($val, 'EUR') || str_contains($val, 'Euro')) $curr = 'EUR';
        elseif (str_contains($val, 'USD') || str_contains($val, 'Dollar')) $curr = 'USD';
        elseif (str_contains($val, 'GNF') || str_contains($val, 'Guinéen')) $curr = 'GNF';

        // Format revenue to formatted string using system currency
        if ($totalRevenues >= 1000000) {
            $formattedRevenue = number_format($totalRevenues / 1000000, 1) . 'M ' . $curr;
        } elseif ($totalRevenues >= 1000) {
            $formattedRevenue = number_format($totalRevenues / 1000, 0, ',', ' ') . 'K ' . $curr;
        } else {
            $formattedRevenue = number_format($totalRevenues, 0, ',', ' ') . ' ' . $curr;
        }

        // Format active students users count
        $formattedUsers = number_format($totalStudents, 0, ',', ' ');

        // Real month-over-month growth, derived from created_at timestamps.
        $now = now();
        $startOfThisMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonth = $startOfThisMonth->copy()->subSecond();

        $schoolsThisMonth = School::where('created_at', '>=', $startOfThisMonth)->count();
        $schoolsLastMonth = School::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();

        $revenueThisMonth = (float) Invoice::where('status', 'paid')->where('created_at', '>=', $startOfThisMonth)->sum('amount');
        $revenueLastMonth = (float) Invoice::where('status', 'paid')->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->sum('amount');

        // 6-month real revenue trend from paid invoices.
        $monthlyRevenueData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonthsNoOverflow($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $monthlyRevenueData[] = [
                'label' => ucfirst($monthStart->translatedFormat('M')),
                'value' => (float) Invoice::where('status', 'paid')
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->sum('amount'),
            ];
        }

        return new DashboardStats(
            totalSchools: $totalSchools,
            totalSchoolsGrowth: $this->growthLabel($schoolsThisMonth, $schoolsLastMonth),
            activeSubscriptions: $activeSchools,
            // No historical snapshot of active-subscription counts exists, so no
            // trend can be honestly computed for this metric.
            activeSubscriptionsGrowth: null,
            totalRevenues: $formattedRevenue,
            totalRevenuesGrowth: $this->growthLabel($revenueThisMonth, $revenueLastMonth),
            activeUsers: $formattedUsers,
            // Same limitation: students_count is a live counter, not a time series.
            activeUsersGrowth: null,
            recentActivities: [],
            monthlyRevenueData: $monthlyRevenueData
        );
    }

    private function growthLabel(int|float $current, int|float $previous): ?string
    {
        if ($previous <= 0) {
            return $current > 0 ? 'Nouveau' : null;
        }

        $pct = (($current - $previous) / $previous) * 100;
        $sign = $pct >= 0 ? '+' : '';

        return $sign . round($pct) . '%';
    }
}
