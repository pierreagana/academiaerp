<?php

namespace App\Modules\Finance\Application\Services;

use App\Modules\Finance\Domain\Models\Expense;
use App\Modules\Finance\Domain\Models\ExpenseBudget;
use Illuminate\Support\Collection;

class ExpenseStatsService
{
    public function monthlyStats(int $schoolId): array
    {
        $monthStart = now()->startOfMonth();

        $monthlyTotal = (float) Expense::where('school_id', $schoolId)
            ->where('status', '!=', 'rejected')
            ->where('expense_date', '>=', $monthStart)
            ->sum('amount');

        $budgets = ExpenseBudget::where('school_id', $schoolId)->get();
        $totalBudget = (float) $budgets->sum('amount');
        $totalConsumed = $budgets->sum(fn (ExpenseBudget $b) => $b->consumed);
        $remaining = $totalBudget - $totalConsumed;

        return [
            'monthlyTotal' => $monthlyTotal,
            'totalBudget' => $totalBudget,
            'totalConsumed' => $totalConsumed,
            'remaining' => $remaining,
            'usagePercentage' => $totalBudget > 0 ? min(round(($totalConsumed / $totalBudget) * 100), 100) : 0,
        ];
    }

    public function categoryBreakdown(int $schoolId): Collection
    {
        return Expense::where('school_id', $schoolId)
            ->where('status', '!=', 'rejected')
            ->get()
            ->groupBy('category')
            ->map(fn (Collection $group, string $category) => [
                'category' => $category,
                'amount' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('amount')
            ->values();
    }

    public function recentExpenses(int $schoolId, int $limit = 5)
    {
        return Expense::where('school_id', $schoolId)
            ->latest('expense_date')
            ->latest('id')
            ->limit($limit)
            ->get();
    }
}
