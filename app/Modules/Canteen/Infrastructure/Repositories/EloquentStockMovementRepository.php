<?php

namespace App\Modules\Canteen\Infrastructure\Repositories;

use App\Modules\Canteen\Domain\Models\StockMovement;
use App\Modules\Canteen\Domain\Repositories\StockMovementRepositoryInterface;

class EloquentStockMovementRepository implements StockMovementRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return StockMovement::create($data);
    }

    public function wasteRatioForMonth(): float
    {
        $schoolId = auth()->user()->school_id;
        $monthStart = now()->startOfMonth();

        $totalOut = (float) StockMovement::where('school_id', $schoolId)
            ->where('type', 'out')
            ->where('created_at', '>=', $monthStart)
            ->sum('quantity');

        if ($totalOut <= 0) {
            return 0;
        }

        $waste = (float) StockMovement::where('school_id', $schoolId)
            ->where('type', 'out')
            ->where('category', 'waste')
            ->where('created_at', '>=', $monthStart)
            ->sum('quantity');

        return round(($waste / $totalOut) * 100, 1);
    }
}
