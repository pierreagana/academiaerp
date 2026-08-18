<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    use HasFactory;

    protected $table = 'backup_logs';

    protected $fillable = [
        'filename',
        'size',
        'type',
        'status',
        'storage_location',
    ];
}
