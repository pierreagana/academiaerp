<?php

namespace App\Modules\Canteen\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class MealRecord extends Model
{
    use BelongsToSchool;
    protected $table = 'canteen_meal_records';

    protected $fillable = [
        'school_id',
        'account_id',
        'date',
        'price',
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
