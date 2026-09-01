<?php

namespace App\Modules\Canteen\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class MenuWeek extends Model
{
    use BelongsToSchool;
    protected $table = 'canteen_menu_weeks';

    protected $fillable = [
        'school_id',
        'week_start_date',
        'published_at',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'published_at' => 'datetime',
    ];
}
