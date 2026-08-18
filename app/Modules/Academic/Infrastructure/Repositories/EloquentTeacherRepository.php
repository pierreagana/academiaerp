<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Academic\Domain\Repositories\TeacherRepositoryInterface;

class EloquentTeacherRepository implements TeacherRepositoryInterface
{
    public function all()
    {
        return Teacher::where('school_id', auth()->user()->school_id)
            ->whereBranch(auth()->user()->activeBranchId())
            ->with(['subjects', 'classes'])->get();
    }

    public function paginate($perPage = 10, array $filters = [])
    {
        $query = Teacher::where('school_id', auth()->user()->school_id)
            ->whereBranch(auth()->user()->activeBranchId())
            ->with(['subjects', 'classes', 'headTeacherClasses'])
            ->latest();

        if (!empty($filters['subject_id'])) {
            $query->whereHas('subjects', function($q) use ($filters) {
                $q->where('subjects.id', $filters['subject_id']);
            });
        }

        return $query->paginate($perPage);
    }

    public function find($id)
    {
        return Teacher::where('school_id', auth()->user()->school_id)
            ->whereBranch(auth()->user()->activeBranchId())
            ->with(['subjects', 'classes'])->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        $data['branch_id'] = $data['branch_id'] ?? auth()->user()->activeBranchId();
        if (empty($data['branch_id'])) {
            throw new \InvalidArgumentException("Sélectionnez une succursale précise avant de créer un enregistrement (la Vue Globale ne permet pas la création).");
        }
        return Teacher::create($data);
    }

    public function update($id, array $data)
    {
        $teacher = $this->find($id);
        $teacher->update($data);
        return $teacher;
    }

    public function delete($id)
    {
        $teacher = $this->find($id);
        return $teacher->delete();
    }

    public function syncClasses($teacherId, array $classIds)
    {
        $teacher = $this->find($teacherId);
        $teacher->classes()->sync($classIds);
    }

    public function syncSubjects($teacherId, array $subjectIds)
    {
        $teacher = $this->find($teacherId);
        $teacher->subjects()->sync($subjectIds);
    }
}
