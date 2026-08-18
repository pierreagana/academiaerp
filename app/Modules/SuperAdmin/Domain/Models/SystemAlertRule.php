<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemAlertRule extends Model
{
    use HasFactory;

    protected $table = 'system_alert_rules';

    protected $fillable = [
        'title',
        'severity',
        'metric',
        'threshold',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
