<?php

namespace App\Modules\Canteen\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class MenuTag extends Model
{
    protected $table = 'canteen_menu_tags';

    protected $fillable = [
        'school_id',
        'name',
    ];
}
