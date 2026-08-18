<?php

namespace App\Modules\HR\Infrastructure\Repositories;

use App\Modules\HR\Domain\Models\ContractType;
use App\Modules\HR\Domain\Repositories\ContractTypeRepositoryInterface;

class EloquentContractTypeRepository implements ContractTypeRepositoryInterface
{
    public function create(string $name)
    {
        return ContractType::create([
            'school_id' => auth()->user()->school_id,
            'name' => $name,
        ]);
    }

    public function all()
    {
        return ContractType::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
    }

    public function delete($id): void
    {
        ContractType::where('school_id', auth()->user()->school_id)->where('id', $id)->delete();
    }

    public function ensureDefaults(): void
    {
        $schoolId = auth()->user()->school_id;

        if (ContractType::where('school_id', $schoolId)->exists()) {
            return;
        }

        foreach (['CDI', 'CDD', 'Prestataire'] as $name) {
            ContractType::create(['school_id' => $schoolId, 'name' => $name]);
        }
    }
}
