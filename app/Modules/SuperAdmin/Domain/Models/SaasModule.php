<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class SaasModule extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'version',
        'status',
        'price',
        'required_plans',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'required_plans' => 'array',
    ];
}
