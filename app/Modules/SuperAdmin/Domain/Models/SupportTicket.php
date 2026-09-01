<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class SupportTicket extends Model
{
    use BelongsToSchool;
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

    public function messages()
    {
        return $this->hasMany(SupportTicketMessage::class)->orderBy('created_at');
    }

    /**
     * The full conversation as a flat, chronological list — the ticket's own
     * `description` is always the opening message (from the school), followed
     * by every real reply. Shared by both the SuperAdmin and school-facing
     * thread views so they never drift out of sync with each other.
     */
    public function thread(): \Illuminate\Support\Collection
    {
        $opening = (object) [
            'sender_type' => SupportTicketMessage::SENDER_SCHOOL,
            'sender_name' => $this->school_name,
            'message' => $this->description ?: 'Aucune description fournie.',
            'created_at' => $this->created_at,
        ];

        return collect([$opening])->merge($this->messages);
    }
}
