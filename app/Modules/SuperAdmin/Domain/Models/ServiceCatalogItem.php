<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCatalogItem extends Model
{
    use HasFactory;

    protected $table = 'service_catalog_items';

    protected $fillable = [
        'name',
        'type',
        'description',
        'price_tag',
        'price_color',
        'icon',
        'icon_bg',
        'is_enabled',
        'is_beta',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_beta'    => 'boolean',
    ];
}
