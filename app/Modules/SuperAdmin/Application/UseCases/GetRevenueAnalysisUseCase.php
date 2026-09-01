<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Models\Invoice;
use App\Modules\SuperAdmin\Domain\Models\SaasPackage;
use App\Modules\SuperAdmin\Domain\Models\School;

class GetRevenueAnalysisUseCase
{
    public function execute(string $period = '6_months'): array
    {
        // Determine start date + comparable-length previous window based on the period filter
        [$startDate, $periodDays] = match ($period) {
            'today', 'day'     => [now()->startOfDay(), 1],
            '1_week', 'week'   => [now()->subDays(7), 7],
            '1_month', 'month' => [now()->subDays(30), 30],
            '3_months'         => [now()->subDays(90), 90],
            'year'             => [now()->startOfYear(), (int) now()->startOfYear()->diffInDays(now()) + 1],
            default            => [now()->subDays(180), 180],
        };

        $invoiceQuery = Invoice::with('school')->latest('id');
        if ($startDate) {
            $invoiceQuery->where('created_at', '>=', $startDate);
        }
        $invoices = $invoiceQuery->get();

        $schools  = School::orderBy('name')->get();
        $packages = SaasPackage::where('status', 'active')->get();

        // 1. Real KPIs — no fallback fabrication. A genuine 0 is shown as 0.
        $totalPaid = (float) $invoices->where('status', 'paid')->sum('amount');
        $pendingRevenue = (float) $invoices->where('status', 'pending')->sum('amount');
        $overdueRevenue = (float) $invoices->whereIn('status', ['overdue', 'failed'])->sum('amount');
        $thisMonth = (float) Invoice::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        $invoiceCount = $invoices->count();

        // Real growth: compare paid total in the current window to the immediately
        // preceding window of equal length — not a hardcoded constant.
        $previousWindowStart = (clone $startDate)->subDays($periodDays);
        $previousPaid = (float) Invoice::where('status', 'paid')
            ->whereBetween('created_at', [$previousWindowStart, $startDate])
            ->sum('amount');
        $growthPct = $previousPaid > 0
            ? round((($totalPaid - $previousPaid) / $previousPaid) * 100, 1)
            : ($totalPaid > 0 ? 100.0 : 0.0);

        $kpis = [
            'total_revenue'   => $totalPaid,
            'growth_pct'      => $growthPct,
            'this_month'      => $thisMonth,
            'pending_revenue' => $pendingRevenue,
            'invoice_count'   => $invoiceCount,
            'overdue_revenue' => $overdueRevenue,
        ];

        // 2. Real trend columns — actual per-day/per-month paid sums, 0 shown as 0.
        $months = [];
        if (in_array($period, ['today', 'day', '1_week', 'week'])) {
            $daysCount = ($period === 'today' || $period === 'day') ? 1 : 7;
            for ($i = $daysCount - 1; $i >= 0; $i--) {
                $d = now()->subDays($i);
                $dayLabel = ucfirst($d->locale('fr')->shortDayName) . ' ' . $d->format('d');
                $dayTotal = (float) Invoice::where('status', 'paid')
                    ->whereDate('created_at', $d->toDateString())
                    ->sum('amount');
                $months[] = ['label' => $dayLabel, 'total' => $dayTotal];
            }
        } else {
            $monthCount = match ($period) {
                '1_month', 'month' => 3,
                '3_months'         => 3,
                default            => 6,
            };

            for ($i = $monthCount - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthLabel = ucfirst($date->locale('fr')->shortMonthName);
                $monthTotal = (float) Invoice::where('status', 'paid')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount');

                $months[] = ['label' => $monthLabel, 'total' => $monthTotal];
            }
        }

        // 3. Real revenue by plan — grouped from actual paid invoices in the period.
        // Falls back to a package-name skeleton (all zero) only so the donut has
        // labels to show when there's no paid invoice data yet at all.
        $revenueByPlan = $invoices->where('status', 'paid')
            ->groupBy(fn ($inv) => $inv->plan_name ?: 'Non spécifié')
            ->map(fn ($group) => (float) $group->sum('amount'))
            ->sortDesc();

        if ($revenueByPlan->isEmpty() && $packages->count() > 0) {
            $revenueByPlan = $packages->mapWithKeys(fn ($pkg) => [$pkg->name => 0.0]);
        }

        // 4. Real top paying schools — grouped from actual paid invoices, not rand().
        $topSchools = $invoices->where('status', 'paid')
            ->groupBy(fn ($inv) => $inv->school_name ?: ($inv->school?->name ?? 'Établissement'))
            ->map(function ($group, $schoolName) {
                $latest = $group->sortByDesc('created_at')->first();

                return (object) [
                    'school_name' => $schoolName,
                    'total_paid'  => (float) $group->sum('amount'),
                    'package'     => $latest->plan_name ?: 'Non spécifié',
                    'status'      => 'paid',
                ];
            })
            ->sortByDesc('total_paid')
            ->take(5)
            ->values();

        // 5. Recent invoices — real data, no hardcoded plan name.
        $recentInvoices = $invoices->take(5)->map(function ($inv) {
            return (object) [
                'invoice_number' => $inv->invoice_number ?? ('INV-' . $inv->id),
                'school_name'    => $inv->school_name ?? ($inv->school?->name ?? 'Établissement'),
                'issue_date'     => $inv->issue_date ?? $inv->created_at,
                'plan_name'      => $inv->plan_name ?: '—',
                'amount'         => (float) $inv->amount,
                'status'         => $inv->status ?? 'pending',
            ];
        });

        return [
            'kpis'            => $kpis,
            'months'          => $months,
            'revenueByPlan'   => $revenueByPlan,
            'topSchools'      => $topSchools,
            'recentInvoices'  => $recentInvoices,
            'total_revenue'   => $totalPaid,
            'mrr'             => $periodDays > 0 ? round($totalPaid / ($periodDays / 30)) : 0,
            'arr'             => $periodDays > 0 ? round($totalPaid / ($periodDays / 365)) : 0,
        ];
    }
}
