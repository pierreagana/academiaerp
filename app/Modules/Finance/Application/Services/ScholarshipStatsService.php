<?php

namespace App\Modules\Finance\Application\Services;

use App\Modules\Finance\Domain\Models\Scholarship;
use App\Modules\Finance\Domain\Models\ScholarshipDisbursement;
use Illuminate\Support\Collection;

class ScholarshipStatsService
{
    /**
     * "Budget Total" = somme des montants annuels des bourses actives (argent
     * réellement engagé) ; "Utilisation" = part de ce budget déjà décaissée.
     */
    public function dashboardStats(int $schoolId): array
    {
        $active = Scholarship::where('school_id', $schoolId)->where('status', 'active')->get();

        $totalBudget = $active->sum('annual_amount');
        $totalDisbursed = $active->isEmpty()
            ? 0.0
            : (float) ScholarshipDisbursement::whereIn('scholarship_id', $active->pluck('id'))
                ->where('status', 'paid')
                ->sum('amount');

        $utilization = $totalBudget > 0 ? round(($totalDisbursed / $totalBudget) * 100, 1) : 0.0;

        return [
            'totalBudget' => $totalBudget,
            'totalDisbursed' => $totalDisbursed,
            'utilization' => $utilization,
            'activeCount' => $active->count(),
        ];
    }

    public function typeBreakdown(int $schoolId): Collection
    {
        $active = Scholarship::where('school_id', $schoolId)->where('status', 'active')->with('type')->get();
        $total = $active->sum('annual_amount');

        return $active->groupBy(fn (Scholarship $s) => $s->type->name ?? 'Autre')
            ->map(function (Collection $group, string $name) use ($total) {
                $amount = $group->sum('annual_amount');
                return [
                    'name' => $name,
                    'amount' => $amount,
                    'percentage' => $total > 0 ? round(($amount / $total) * 100) : 0,
                ];
            })
            ->sortByDesc('amount')
            ->values();
    }

    public function pendingRequests(int $schoolId)
    {
        return Scholarship::where('school_id', $schoolId)
            ->where('status', 'pending')
            ->with(['student', 'type'])
            ->orderByDesc('priority')
            ->oldest()
            ->get();
    }
}
