<?php

namespace App\Modules\Canteen\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'canteen_accounts';

    protected $fillable = [
        'school_id',
        'holder_type',
        'holder_id',
        'status',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function holder()
    {
        return $this->morphTo();
    }

    public function mealRecords()
    {
        return $this->hasMany(MealRecord::class);
    }
}
