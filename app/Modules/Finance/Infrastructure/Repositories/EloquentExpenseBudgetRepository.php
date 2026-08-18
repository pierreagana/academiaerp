<?php

namespace App\Modules\Finance\Infrastructure\Repositories;

use App\Modules\Finance\Domain\Models\ExpenseBudget;
use App\Modules\Finance\Domain\Repositories\ExpenseBudgetRepositoryInterface;

class EloquentExpenseBudgetRepository implements ExpenseBudgetRepositoryInterface
{
    public function all()
    {
        return ExpenseBudget::where('school_id', auth()->user()->school_id)->orderBy('category')->get();
    }

    public function find($id)
    {
        return ExpenseBudget::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return ExpenseBudget::create($data);
    }

    public function update($id, array $data)
    {
        $budget = $this->find($id);
        $budget->update($data);
        return $budget;
    }

    public function delete($id)
    {
        $budget = $this->find($id);
        return $budget->delete();
    }
}
