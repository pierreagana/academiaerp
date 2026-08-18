<?php

namespace App\Modules\Infirmary\Infrastructure\Repositories;

use App\Modules\Infirmary\Domain\Models\MedicationMovement;
use App\Modules\Infirmary\Domain\Repositories\MedicationMovementRepositoryInterface;

class EloquentMedicationMovementRepository implements MedicationMovementRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return MedicationMovement::create($data);
    }

    public function dailyUsageRate($medicationId, int $days = 14): float
    {
        $totalOut = (int) MedicationMovement::where('school_id', auth()->user()->school_id)
            ->where('medication_id', $medicationId)
            ->where('type', 'out')
            ->where('created_at', '>=', now()->subDays($days))
            ->sum('quantity');

        return $days > 0 ? round($totalOut / $days, 2) : 0.0;
    }
}
