<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Repositories\StaffRepositoryInterface;

class EloquentStaffRepository implements StaffRepositoryInterface
{
    public function all()
    {
        return Staff::where('school_id', auth()->user()->school_id)
            ->whereBranch(auth()->user()->activeBranchId())->get();
    }

    public function paginate($perPage = 10, array $filters = [])
    {
        $query = Staff::where('school_id', auth()->user()->school_id)
            ->whereBranch(auth()->user()->activeBranchId())->latest();

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        return $query->paginate($perPage);
    }

    public function find($id)
    {
        return Staff::where('school_id', auth()->user()->school_id)
            ->whereBranch(auth()->user()->activeBranchId())->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        $data['branch_id'] = $data['branch_id'] ?? auth()->user()->activeBranchId();
        if (empty($data['branch_id'])) {
            throw new \InvalidArgumentException("Sélectionnez une succursale précise avant de créer un enregistrement (la Vue Globale ne permet pas la création).");
        }
        return Staff::create($data);
    }

    public function update($id, array $data)
    {
        $staff = $this->find($id);
        $staff->update($data);
        return $staff;
    }

    public function delete($id)
    {
        $staff = $this->find($id);
        return $staff->delete();
    }
}
