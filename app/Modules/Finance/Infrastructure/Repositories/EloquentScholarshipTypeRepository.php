<?php

namespace App\Modules\Finance\Infrastructure\Repositories;

use App\Modules\Finance\Domain\Models\ScholarshipType;
use App\Modules\Finance\Domain\Repositories\ScholarshipTypeRepositoryInterface;

class EloquentScholarshipTypeRepository implements ScholarshipTypeRepositoryInterface
{
    public function all()
    {
        return ScholarshipType::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
    }

    public function find($id)
    {
        return ScholarshipType::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return ScholarshipType::create($data);
    }

    public function update($id, array $data)
    {
        $type = $this->find($id);
        $type->update($data);
        return $type;
    }

    public function delete($id)
    {
        $type = $this->find($id);
        return $type->delete();
    }
}
