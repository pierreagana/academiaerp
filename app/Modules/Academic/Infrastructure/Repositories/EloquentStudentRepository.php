<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface;

class EloquentStudentRepository implements StudentRepositoryInterface
{
    public function all()
    {
        return Student::where('school_id', auth()->user()->school_id)
            ->whereBranch(auth()->user()->activeBranchId())
            ->with(['academicClass', 'guardians'])->get();
    }

    public function paginate($perPage = 10, array $filters = [])
    {
        $query = Student::where('school_id', auth()->user()->school_id)
            ->whereBranch(auth()->user()->activeBranchId())
            ->with(['academicClass', 'guardians']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('roll_number', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['academic_class_id'])) {
            $query->where('academic_class_id', $filters['academic_class_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function find($id)
    {
        return Student::where('school_id', auth()->user()->school_id)
            ->whereBranch(auth()->user()->activeBranchId())
            ->with('guardians')->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;

        // A student's branch always follows the class they're enrolled into, so creation
        // stays possible even in Vue Globale as long as a class was picked.
        if (empty($data['branch_id']) && !empty($data['academic_class_id'])) {
            $data['branch_id'] = \App\Modules\Academic\Domain\Models\AcademicClass::find($data['academic_class_id'])?->branch_id;
        }
        $data['branch_id'] = $data['branch_id'] ?? auth()->user()->activeBranchId();

        if (empty($data['branch_id'])) {
            throw new \InvalidArgumentException("Sélectionnez une succursale précise avant de créer un enregistrement (la Vue Globale ne permet pas la création).");
        }

        $school = \App\Modules\SuperAdmin\Domain\Models\School::find($data['school_id']);
        $package = $school?->activePackage();
        if ($package && !empty($package->max_students)) {
            $currentCount = Student::where('school_id', $data['school_id'])->count();
            if ($currentCount >= $package->max_students) {
                throw new \InvalidArgumentException(
                    "Limite d'élèves atteinte pour le forfait « {$package->name} » ({$package->max_students} élèves maximum). Passez à un forfait supérieur pour ajouter plus d'élèves."
                );
            }
        }

        return Student::create($data);
    }

    public function update($id, array $data)
    {
        $student = $this->find($id);
        $student->update($data);
        return $student;
    }

    public function delete($id)
    {
        $student = $this->find($id);
        return $student->delete();
    }
}
