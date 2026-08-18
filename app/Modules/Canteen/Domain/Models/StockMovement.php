<?php

namespace App\Modules\Canteen\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $table = 'canteen_stock_movements';

    protected $fillable = [
        'school_id',
        'product_id',
        'type',
        'category',
        'quantity',
        'source',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
