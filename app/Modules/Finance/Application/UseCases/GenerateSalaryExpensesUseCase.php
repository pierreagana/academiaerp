<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Finance\Domain\Models\Expense;
use App\Modules\Finance\Domain\Repositories\ExpenseRepositoryInterface;

class GenerateSalaryExpensesUseCase
{
    private ExpenseRepositoryInterface $repository;

    public function __construct(ExpenseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Creates one "Salaires" expense per active, salaried Teacher/Staff for
     * the current month — idempotent, skips anyone already paid this month.
     */
    public function execute(int $schoolId, ?int $recordedBy = null): int
    {
        $monthStart = now()->startOfMonth();
        $created = 0;

        $teachers = Teacher::where('school_id', $schoolId)
            ->where('status', 'active')
            ->whereNotNull('salary')
            ->where('salary', '>', 0)
            ->get();

        foreach ($teachers as $teacher) {
            $alreadyPaid = Expense::where('school_id', $schoolId)
                ->where('teacher_id', $teacher->id)
                ->where('category', 'Salaires')
                ->where('expense_date', '>=', $monthStart)
                ->exists();

            if ($alreadyPaid) {
                continue;
            }

            $this->repository->create([
                'school_id' => $schoolId,
                'title' => 'Salaire — ' . $teacher->first_name . ' ' . $teacher->last_name,
                'amount' => $teacher->salary,
                'category' => 'Salaires',
                'payee' => $teacher->first_name . ' ' . $teacher->last_name,
                'expense_date' => now()->toDateString(),
                'status' => 'approved',
                'teacher_id' => $teacher->id,
                'recorded_by' => $recordedBy,
            ]);
            $created++;
        }

        $staffMembers = Staff::where('school_id', $schoolId)
            ->where('status', 'active')
            ->whereNotNull('salary')
            ->where('salary', '>', 0)
            ->get();

        foreach ($staffMembers as $staff) {
            $alreadyPaid = Expense::where('school_id', $schoolId)
                ->where('staff_id', $staff->id)
                ->where('category', 'Salaires')
                ->where('expense_date', '>=', $monthStart)
                ->exists();

            if ($alreadyPaid) {
                continue;
            }

            $this->repository->create([
                'school_id' => $schoolId,
                'title' => 'Salaire — ' . $staff->first_name . ' ' . $staff->last_name,
                'amount' => $staff->salary,
                'category' => 'Salaires',
                'payee' => $staff->first_name . ' ' . $staff->last_name,
                'expense_date' => now()->toDateString(),
                'status' => 'approved',
                'staff_id' => $staff->id,
                'recorded_by' => $recordedBy,
            ]);
            $created++;
        }

        return $created;
    }
}
