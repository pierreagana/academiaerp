<?php

namespace App\Modules\Canteen\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class Allergen extends Model
{
    use BelongsToSchool;
    protected $table = 'canteen_allergens';

    protected $fillable = [
        'school_id',
        'name',
    ];
}
