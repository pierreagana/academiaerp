<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class SaasPackage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_cycle',
        'max_students',
        'max_storage_gb',
        'features',
        'status',
        'is_popular',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_popular' => 'boolean',
    ];
}
