<?php

namespace App\Modules\Canteen\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Canteen\Domain\Models\Account;
use App\Modules\Canteen\Domain\Models\MealRecord;
use App\Modules\Canteen\Domain\Repositories\AccountRepositoryInterface;

class EloquentAccountRepository implements AccountRepositoryInterface
{
    public function syncRoster(): void
    {
        $schoolId = auth()->user()->school_id;

        Student::where('school_id', $schoolId)->pluck('id')->each(function ($id) use ($schoolId) {
            Account::firstOrCreate([
                'school_id' => $schoolId,
                'holder_type' => 'student',
                'holder_id' => $id,
            ]);
        });

        Staff::where('school_id', $schoolId)->pluck('id')->each(function ($id) use ($schoolId) {
            Account::firstOrCreate([
                'school_id' => $schoolId,
                'holder_type' => 'staff',
                'holder_id' => $id,
            ]);
        });
    }

    public function paginate($perPage = 15, array $filters = [])
    {
        $schoolId = auth()->user()->school_id;
        $query = Account::where('school_id', $schoolId)->with('holder');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['holder_type'])) {
            $query->where('holder_type', $filters['holder_type']);
        }

        if (!empty($filters['class_id'])) {
            $query->where('holder_type', 'student')
                ->whereHasMorph('holder', [Student::class], function ($q) use ($filters) {
                    $q->where('academic_class_id', $filters['class_id']);
                });
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHasMorph('holder', [Student::class, Staff::class], function ($q) use ($search) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        return $query->orderBy('id')->paginate($perPage)->withQueryString();
    }

    public function find($id)
    {
        return Account::where('school_id', auth()->user()->school_id)->with('holder')->findOrFail($id);
    }

    public function credit($id, float $amount)
    {
        $account = $this->find($id);
        $account->increment('balance', $amount);
        return $account;
    }

    public function debit($id, float $amount)
    {
        $account = $this->find($id);
        $account->decrement('balance', $amount);
        return $account;
    }

    public function mealsCountThisMonth($accountId): int
    {
        return MealRecord::where('account_id', $accountId)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();
    }
}
