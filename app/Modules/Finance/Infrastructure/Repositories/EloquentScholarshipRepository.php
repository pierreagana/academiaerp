<?php

namespace App\Modules\Finance\Infrastructure\Repositories;

use App\Modules\Finance\Domain\Models\Scholarship;
use App\Modules\Finance\Domain\Repositories\ScholarshipRepositoryInterface;

class EloquentScholarshipRepository implements ScholarshipRepositoryInterface
{
    public function all()
    {
        return Scholarship::where('school_id', auth()->user()->school_id)
            ->with(['student', 'type'])
            ->latest()
            ->get();
    }

    public function find($id)
    {
        return Scholarship::where('school_id', auth()->user()->school_id)
            ->with(['student', 'type', 'disbursements', 'documents', 'grades'])
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Scholarship::create($data);
    }

    public function update($id, array $data)
    {
        $scholarship = $this->find($id);
        $scholarship->update($data);
        return $scholarship;
    }

    public function delete($id)
    {
        $scholarship = $this->find($id);
        return $scholarship->delete();
    }
}
