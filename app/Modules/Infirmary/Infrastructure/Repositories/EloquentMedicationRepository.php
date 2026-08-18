<?php

namespace App\Modules\Infirmary\Infrastructure\Repositories;

use App\Modules\Infirmary\Domain\Models\Medication;
use App\Modules\Infirmary\Domain\Models\MedicationMovement;
use App\Modules\Infirmary\Domain\Repositories\MedicationRepositoryInterface;
use Illuminate\Support\Carbon;

class EloquentMedicationRepository implements MedicationRepositoryInterface
{
    public function all()
    {
        return Medication::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
    }

    public function paginate(int $perPage = 10)
    {
        return Medication::where('school_id', auth()->user()->school_id)->orderBy('name')->paginate($perPage);
    }

    public function find($id)
    {
        return Medication::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Medication::create($data);
    }

    public function expiringWithin(int $days)
    {
        return Medication::where('school_id', auth()->user()->school_id)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays($days)])
            ->get();
    }

    public function globalStockLevel(): float
    {
        $items = Medication::where('school_id', auth()->user()->school_id)
            ->whereNotNull('max_quantity')
            ->where('max_quantity', '>', 0)
            ->get(['quantity', 'max_quantity']);

        $totalMax = $items->sum('max_quantity');

        if ($totalMax <= 0) {
            return 0;
        }

        return round(($items->sum('quantity') / $totalMax) * 100, 1);
    }

    public function recentMovements(int $limit = 5)
    {
        return MedicationMovement::where('school_id', auth()->user()->school_id)
            ->with('medication')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
