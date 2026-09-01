<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\TimetableBreak;
use App\Modules\Academic\Domain\Repositories\TimetableBreakRepositoryInterface;

class EloquentTimetableBreakRepository implements TimetableBreakRepositoryInterface
{
    public function allForClass($classId)
    {
        if (!$classId) {
            return collect();
        }

        return TimetableBreak::where('school_id', auth()->user()->school_id)
            ->where('academic_class_id', $classId)
            ->orderByRaw("FIELD(day_of_week, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi')")
            ->orderBy('start_time')
            ->get();
    }

    public function find($id)
    {
        return TimetableBreak::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return TimetableBreak::create($data);
    }

    public function update($id, array $data)
    {
        $model = $this->find($id);
        $model->update($data);
        return $model;
    }

    public function delete($id) { return $this->find($id)->delete(); }
}
