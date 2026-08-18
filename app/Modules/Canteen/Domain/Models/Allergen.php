<?php

namespace App\Modules\Canteen\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class Allergen extends Model
{
    protected $table = 'canteen_allergens';

    protected $fillable = [
        'school_id',
        'name',
    ];
}
