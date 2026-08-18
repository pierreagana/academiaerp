<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\Subject;
use App\Modules\Academic\Domain\Repositories\SubjectRepositoryInterface;

class EloquentSubjectRepository implements SubjectRepositoryInterface
{
    public function all()
    {
        return Subject::where('school_id', auth()->user()->school_id)->with('teachers')->get();
    }

    public function find($id)
    {
        return Subject::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Subject::create($data);
    }

    public function update($id, array $data)
    {
        $model = $this->find($id);
        $model->update($data);
        return $model;
    }

    public function delete($id) { return $this->find($id)->delete(); }
}
