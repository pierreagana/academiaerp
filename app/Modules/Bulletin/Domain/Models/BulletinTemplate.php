<?php

namespace App\Modules\Bulletin\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class BulletinTemplate extends Model
{
    use BelongsToSchool;
    protected $fillable = [
        'school_id', 'name', 'show_coefficient', 'show_class_average',
        'show_highest_lowest', 'show_ranking', 'show_signature_area', 'suggested_remarks_enabled',
    ];

    protected $casts = [
        'show_coefficient' => 'boolean',
        'show_class_average' => 'boolean',
        'show_highest_lowest' => 'boolean',
        'show_ranking' => 'boolean',
        'show_signature_area' => 'boolean',
        'suggested_remarks_enabled' => 'boolean',
    ];
}
