<?php

namespace App\Modules\HR\Infrastructure\Repositories;

use App\Modules\HR\Domain\Models\PayrollComponent;
use App\Modules\HR\Domain\Repositories\PayrollComponentRepositoryInterface;

class EloquentPayrollComponentRepository implements PayrollComponentRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return PayrollComponent::create($data);
    }

    public function all()
    {
        return PayrollComponent::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
    }

    public function toggle($id): void
    {
        $component = PayrollComponent::where('school_id', auth()->user()->school_id)->findOrFail($id);
        $component->update(['enabled' => !$component->enabled]);
    }

    public function ensureDefaults(): void
    {
        $schoolId = auth()->user()->school_id;

        if (PayrollComponent::where('school_id', $schoolId)->exists()) {
            return;
        }

        $defaults = [
            ['name' => 'Allocations Familiales', 'type' => 'allocation', 'rate_type' => 'percentage', 'rate_value' => 5, 'enabled' => true],
            ['name' => 'Mutuelle Santé', 'type' => 'deduction', 'rate_type' => 'percentage', 'rate_value' => 2.5, 'enabled' => true],
            ['name' => 'Indemnité Transport', 'type' => 'allocation', 'rate_type' => 'fixed', 'rate_value' => 25000, 'enabled' => false],
        ];

        foreach ($defaults as $default) {
            $default['school_id'] = $schoolId;
            PayrollComponent::create($default);
        }
    }
}
