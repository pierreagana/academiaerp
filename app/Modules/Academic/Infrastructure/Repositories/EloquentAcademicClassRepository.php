<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface;

class EloquentAcademicClassRepository implements AcademicClassRepositoryInterface
{
    public function all()
    {
        return AcademicClass::where('school_id', auth()->user()->school_id)
            ->whereBranch(auth()->user()->activeBranchId())->get();
    }

    public function find($id)
    {
        return AcademicClass::where('school_id', auth()->user()->school_id)
            ->whereBranch(auth()->user()->activeBranchId())->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        $data['branch_id'] = $data['branch_id'] ?? auth()->user()->activeBranchId();
        if (empty($data['branch_id'])) {
            throw new \InvalidArgumentException("Sélectionnez une succursale précise avant de créer un enregistrement (la Vue Globale ne permet pas la création).");
        }
        return AcademicClass::create($data);
    }

    public function update($id, array $data)
    {
        $model = $this->find($id);
        $model->update($data);
        return $model;
    }

    public function delete($id) { return $this->find($id)->delete(); }
}
