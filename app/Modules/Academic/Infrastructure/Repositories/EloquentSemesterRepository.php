<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\Semester;
use App\Modules\Academic\Domain\Repositories\SemesterRepositoryInterface;

class EloquentSemesterRepository implements SemesterRepositoryInterface
{
    public function all()
    {
        return Semester::where('school_id', auth()->user()->school_id)->get();
    }

    public function find($id)
    {
        return Semester::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Semester::create($data);
    }

    public function update($id, array $data)
    {
        $model = $this->find($id);
        $model->update($data);
        return $model;
    }

    public function delete($id) { return $this->find($id)->delete(); }
}
