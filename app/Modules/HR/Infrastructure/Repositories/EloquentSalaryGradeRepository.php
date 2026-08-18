<?php

namespace App\Modules\HR\Infrastructure\Repositories;

use App\Modules\HR\Domain\Models\SalaryGrade;
use App\Modules\HR\Domain\Repositories\SalaryGradeRepositoryInterface;

class EloquentSalaryGradeRepository implements SalaryGradeRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return SalaryGrade::create($data);
    }

    public function all()
    {
        return SalaryGrade::where('school_id', auth()->user()->school_id)->orderBy('base_salary')->get();
    }
}
