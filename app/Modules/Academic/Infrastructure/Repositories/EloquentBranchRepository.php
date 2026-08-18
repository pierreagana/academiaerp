<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Branch;
use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Academic\Domain\Repositories\BranchRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentBranchRepository implements BranchRepositoryInterface
{
    public function all()
    {
        return Branch::where('school_id', auth()->user()->school_id)->orderByDesc('is_main')->orderBy('name')->get();
    }

    public function find($id)
    {
        return Branch::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function create(array $data)
    {
        $schoolId = auth()->user()->school_id;
        $data['school_id'] = $schoolId;
        $data['is_main'] = !Branch::where('school_id', $schoolId)->exists();

        return Branch::create($data);
    }

    public function update($id, array $data)
    {
        $branch = $this->find($id);
        unset($data['is_main']);
        $branch->update($data);
        return $branch;
    }

    public function delete($id)
    {
        $branch = $this->find($id);

        if ($branch->is_main) {
            throw new \InvalidArgumentException("Impossible de supprimer la succursale principale. Définissez d'abord une autre succursale comme principale.");
        }

        $hasPopulation = Student::where('branch_id', $id)->exists()
            || Teacher::where('branch_id', $id)->exists()
            || Staff::where('branch_id', $id)->exists()
            || AcademicClass::where('branch_id', $id)->exists();

        if ($hasPopulation) {
            throw new \InvalidArgumentException('Impossible de supprimer cette succursale : des classes, élèves ou membres du personnel y sont encore rattachés.');
        }

        return $branch->delete();
    }

    public function mainBranch()
    {
        return Branch::where('school_id', auth()->user()->school_id)->where('is_main', true)->first();
    }

    public function setMain($id)
    {
        $schoolId = auth()->user()->school_id;

        DB::transaction(function () use ($id, $schoolId) {
            Branch::where('school_id', $schoolId)->where('is_main', true)->update(['is_main' => false]);
            Branch::where('school_id', $schoolId)->where('id', $id)->update(['is_main' => true]);
        });

        return $this->find($id);
    }
}
