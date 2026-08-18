<?php

namespace App\Modules\Canteen\Infrastructure\Repositories;

use App\Modules\Canteen\Domain\Models\Product;
use App\Modules\Canteen\Domain\Models\StockMovement;
use App\Modules\Canteen\Domain\Repositories\ProductRepositoryInterface;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function all()
    {
        return Product::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
    }

    public function paginate($perPage = 10)
    {
        return Product::where('school_id', auth()->user()->school_id)->orderBy('name')->paginate($perPage);
    }

    public function find($id)
    {
        return Product::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Product::create($data);
    }

    public function criticalOrLow()
    {
        return Product::where('school_id', auth()->user()->school_id)
            ->where(function ($q) {
                $q->whereNotNull('critical_threshold')->whereColumn('quantity', '<=', 'critical_threshold');
            })
            ->orWhere(function ($q) {
                $q->where('school_id', auth()->user()->school_id)
                    ->whereNotNull('low_stock_threshold')
                    ->whereColumn('quantity', '<=', 'low_stock_threshold');
            })
            ->get();
    }

    public function recentMovements($limit = 5)
    {
        return StockMovement::where('school_id', auth()->user()->school_id)
            ->with('product')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
