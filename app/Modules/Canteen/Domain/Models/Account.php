<?php

namespace App\Modules\Canteen\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class Account extends Model
{
    use BelongsToSchool;
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
