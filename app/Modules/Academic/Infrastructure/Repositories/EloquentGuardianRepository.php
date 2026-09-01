<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\Guardian;
use App\Modules\Academic\Domain\Repositories\GuardianRepositoryInterface;

class EloquentGuardianRepository implements GuardianRepositoryInterface
{
    public function all()
    {
        return Guardian::where('school_id', auth()->user()->school_id)->with('students')->get();
    }

    public function paginate($perPage = 10)
    {
        return Guardian::where('school_id', auth()->user()->school_id)->with('students')->latest()->paginate($perPage);
    }

    public function find($id)
    {
        return Guardian::where('school_id', auth()->user()->school_id)->with('students')->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Guardian::create($data);
    }

    public function update($id, array $data)
    {
        $guardian = $this->find($id);
        $guardian->update($data);
        return $guardian;
    }

    public function delete($id)
    {
        $guardian = $this->find($id);
        $parentAccount = $guardian->parentAccount;

        $result = $guardian->delete();

        // A ParentAccount is the actual login (session + Sanctum tokens for the mobile
        // app); deleting its last linked Guardian must revoke access, or the parent
        // stays logged in indefinitely even though the school removed them.
        if ($parentAccount && $parentAccount->guardianRecords()->doesntExist()) {
            $parentAccount->tokens()->delete();
        }

        return $result;
    }
}
