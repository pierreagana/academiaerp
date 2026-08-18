<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_id',
        'subject',
        'description',
        'school_id',
        'school_name',
        'priority',
        'status',
        'category',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
