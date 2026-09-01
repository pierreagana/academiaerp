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

        // The (school_id, level, academic_year) unique index is a plain DB
        // constraint — it doesn't know about deleted_at, so a soft-deleted row
        // still blocks a fresh insert for the same combination. Restore and
        // update it instead of letting that hit a duplicate-key error.
        $trashed = FeeLevel::onlyTrashed()
            ->where('school_id', $data['school_id'])
            ->where('type', $data['type'])
            ->where('level', $data['level'])
            ->where('academic_year', $data['academic_year'])
            ->first();

        if ($trashed) {
            $trashed->restore();
            $trashed->update($data);
            return $trashed;
        }

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
