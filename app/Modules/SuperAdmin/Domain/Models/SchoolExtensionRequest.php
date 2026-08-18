<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SchoolExtensionRequest extends Model
{
    protected $fillable = [
        'school_id',
        'module_name',
        'status',
        'requested_by',
        'decided_by',
        'decided_at',
        'note',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
