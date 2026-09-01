<?php

namespace App\Modules\Canteen\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class MenuTag extends Model
{
    use BelongsToSchool;
    protected $table = 'canteen_menu_tags';

    protected $fillable = [
        'school_id',
        'name',
    ];
}
