<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIModel extends Model
{
    use HasFactory;

    protected $table = 'ai_models';

    protected $fillable = [
        'name',
        'provider',
        'status',
        'status_label',
        'latency',
        'color',
    ];
}
