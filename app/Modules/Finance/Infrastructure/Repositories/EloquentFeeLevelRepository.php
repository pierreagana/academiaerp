<?php

namespace App\Modules\Finance\Infrastructure\Repositories;

use App\Modules\Finance\Domain\Models\FeeLevel;
use App\Modules\Finance\Domain\Repositories\FeeLevelRepositoryInterface;

class EloquentFeeLevelRepository implements FeeLevelRepositoryInterface
{
    public function all(string $type = 'tuition')
    {
        return FeeLevel::where('school_id', auth()->user()->school_id)->where('type', $type)->orderBy('level')->get();
    }

    public function find($id)
    {
        return FeeLevel::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return FeeLevel::create($data);
    }

    public function update($id, array $data)
    {
        $feeLevel = $this->find($id);
        $feeLevel->update($data);
        return $feeLevel;
    }

    public function delete($id)
    {
        $feeLevel = $this->find($id);
        return $feeLevel->delete();
    }

    public function findForLevel(string $level, string $academicYear)
    {
        return FeeLevel::where('school_id', auth()->user()->school_id)
            ->where('level', $level)
            ->where('academic_year', $academicYear)
            ->first();
    }
}
