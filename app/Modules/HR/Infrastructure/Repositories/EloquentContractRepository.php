<?php

namespace App\Modules\HR\Infrastructure\Repositories;

use App\Modules\HR\Domain\Models\Contract;
use App\Modules\HR\Domain\Repositories\ContractRepositoryInterface;

class EloquentContractRepository implements ContractRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Contract::create($data);
    }

    public function find($id)
    {
        return Contract::where('school_id', auth()->user()->school_id)->with('holder')->findOrFail($id);
    }

    public function all()
    {
        return Contract::where('school_id', auth()->user()->school_id)
            ->with('holder')
            ->orderByDesc('start_date')
            ->get();
    }

    public function acknowledgeReminder($id): void
    {
        $contract = $this->find($id);
        $contract->update(['reminder_acknowledged_at' => now()]);
    }
}
