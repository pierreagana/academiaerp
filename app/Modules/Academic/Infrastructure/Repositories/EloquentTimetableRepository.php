<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\Timetable;
use App\Modules\Academic\Domain\Repositories\TimetableRepositoryInterface;

class EloquentTimetableRepository implements TimetableRepositoryInterface
{
    /**
     * Timetables have no school_id column of their own — scoped via the
     * academic_class relation, which is itself school-scoped.
     */
    private function scoped()
    {
        return Timetable::whereHas('academicClass', function ($q) {
            $q->where('school_id', auth()->user()->school_id);
        });
    }

    public function all() { return $this->scoped()->get(); }
    public function find($id) { return $this->scoped()->findOrFail($id); }
    public function create(array $data) { return Timetable::create($data); }
    public function update($id, array $data) {
        $model = $this->find($id);
        $model->update($data);
        return $model;
    }
    public function delete($id) { return $this->find($id)->delete(); }
}
