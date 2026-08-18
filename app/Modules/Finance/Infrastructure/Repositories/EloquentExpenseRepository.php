<?php

namespace App\Modules\Finance\Infrastructure\Repositories;

use App\Modules\Finance\Domain\Models\Expense;
use App\Modules\Finance\Domain\Repositories\ExpenseRepositoryInterface;

class EloquentExpenseRepository implements ExpenseRepositoryInterface
{
    public function all()
    {
        return Expense::where('school_id', auth()->user()->school_id)->latest('expense_date')->get();
    }

    public function find($id)
    {
        return Expense::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        $data['reference'] = $data['reference'] ?? $this->generateReference($data['school_id']);
        return Expense::create($data);
    }

    public function update($id, array $data)
    {
        $expense = $this->find($id);
        $expense->update($data);
        return $expense;
    }

    public function delete($id)
    {
        $expense = $this->find($id);
        return $expense->delete();
    }

    private function generateReference(int $schoolId): string
    {
        $next = Expense::where('school_id', $schoolId)->count() + 1;
        return 'TX-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(uniqid(), -4));
    }
}
