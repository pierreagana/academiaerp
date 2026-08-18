<?php

namespace App\Modules\HR\Application\Services;

use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Finance\Domain\Models\Expense;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HRStatsService
{
    public function dashboardStats(int $schoolId): array
    {
        $activeTeachers = Teacher::where('school_id', $schoolId)->where('status', 'active')->get();
        $activeStaff = Staff::where('school_id', $schoolId)->where('status', 'active')->get();

        $monthStart = Carbon::now()->startOfMonth();

        $arrivals = Teacher::where('school_id', $schoolId)->where('hire_date', '>=', $monthStart)->count()
            + Staff::where('school_id', $schoolId)->where('hire_date', '>=', $monthStart)->count();

        $departures = Teacher::where('school_id', $schoolId)->where('status', 'inactive')->where('updated_at', '>=', $monthStart)->count()
            + Staff::where('school_id', $schoolId)->where('status', 'inactive')->where('updated_at', '>=', $monthStart)->count();

        return [
            'payrollMass' => (float) $activeTeachers->sum('salary') + (float) $activeStaff->sum('salary'),
            'activeContracts' => $activeTeachers->count() + $activeStaff->count(),
            'arrivals' => $arrivals,
            'departures' => $departures,
        ];
    }

    public function payrollTrend(int $schoolId, int $months = 8): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths($months - 1);

        $expenses = Expense::where('school_id', $schoolId)
            ->where('category', 'Salaires')
            ->where('status', '!=', 'rejected')
            ->where('expense_date', '>=', $start)
            ->get();

        $trend = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $total = $expenses->filter(fn ($e) => $e->expense_date->isSameMonth($month))->sum('amount');
            $trend[] = ['label' => $month->translatedFormat('M'), 'amount' => (float) $total];
        }

        return $trend;
    }

    public function unconfiguredSalaries(int $schoolId): Collection
    {
        $teachers = Teacher::where('school_id', $schoolId)->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('salary')->orWhere('salary', 0))
            ->get()->map(fn ($t) => ['name' => $t->first_name . ' ' . $t->last_name, 'type' => 'Enseignant']);

        $staff = Staff::where('school_id', $schoolId)->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('salary')->orWhere('salary', 0))
            ->get()->map(fn ($s) => ['name' => $s->first_name . ' ' . $s->last_name, 'type' => 'Personnel']);

        return $teachers->concat($staff)->values();
    }

    public function upcomingContractEnds(int $schoolId, int $withinDays = 30): Collection
    {
        $today = Carbon::today();
        $limit = $today->copy()->addDays($withinDays);

        $teachers = Teacher::where('school_id', $schoolId)
            ->whereBetween('contract_end_date', [$today, $limit])
            ->get()->map(fn ($t) => ['name' => $t->first_name . ' ' . $t->last_name, 'type' => 'Enseignant', 'date' => $t->contract_end_date]);

        $staff = Staff::where('school_id', $schoolId)
            ->whereBetween('contract_end_date', [$today, $limit])
            ->get()->map(fn ($s) => ['name' => $s->first_name . ' ' . $s->last_name, 'type' => 'Personnel', 'date' => $s->contract_end_date]);

        return $teachers->concat($staff)->sortBy('date')->values();
    }

    public function payrollRoster(int $schoolId): Collection
    {
        $monthStart = Carbon::now()->startOfMonth();

        $paidTeacherIds = Expense::where('school_id', $schoolId)->where('category', 'Salaires')
            ->where('expense_date', '>=', $monthStart)->whereNotNull('teacher_id')->pluck('teacher_id');
        $paidStaffIds = Expense::where('school_id', $schoolId)->where('category', 'Salaires')
            ->where('expense_date', '>=', $monthStart)->whereNotNull('staff_id')->pluck('staff_id');

        $teachers = Teacher::where('school_id', $schoolId)->where('status', 'active')
            ->whereNotNull('salary')->where('salary', '>', 0)->get()
            ->map(fn ($t) => [
                'name' => $t->first_name . ' ' . $t->last_name,
                'role' => 'Enseignant',
                'photo_path' => $t->photo_path,
                'salary' => (float) $t->salary,
                'paid' => $paidTeacherIds->contains($t->id),
            ]);

        $staff = Staff::where('school_id', $schoolId)->where('status', 'active')
            ->whereNotNull('salary')->where('salary', '>', 0)->get()
            ->map(fn ($s) => [
                'name' => $s->first_name . ' ' . $s->last_name,
                'role' => $s->role ?? 'Personnel',
                'photo_path' => $s->photo_path,
                'salary' => (float) $s->salary,
                'paid' => $paidStaffIds->contains($s->id),
            ]);

        return $teachers->concat($staff)->values();
    }
}
