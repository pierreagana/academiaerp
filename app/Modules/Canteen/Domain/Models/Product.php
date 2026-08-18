<?php

namespace App\Modules\Canteen\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'canteen_products';

    protected $fillable = [
        'school_id',
        'name',
        'category',
        'unit',
        'quantity',
        'low_stock_threshold',
        'critical_threshold',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity' => 'decimal:2',
        'low_stock_threshold' => 'decimal:2',
        'critical_threshold' => 'decimal:2',
    ];

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getStatusAttribute(): string
    {
        if ($this->critical_threshold !== null && $this->quantity <= $this->critical_threshold) {
            return 'critical';
        }

        if ($this->low_stock_threshold !== null && $this->quantity <= $this->low_stock_threshold) {
            return 'low_stock';
        }

        if ($this->expiry_date && $this->expiry_date->lte(Carbon::today()->addDays(14))) {
            return 'expiring_soon';
        }

        return 'optimal';
    }
}
