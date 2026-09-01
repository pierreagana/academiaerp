<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicketMessage extends Model
{
    public const SENDER_SCHOOL = 'school';
    public const SENDER_SUPPORT = 'support';

    protected $fillable = [
        'support_ticket_id',
        'sender_type',
        'sender_name',
        'message',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }
}
