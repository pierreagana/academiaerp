<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\SupportTicket as DomainSupportTicket;
use App\Modules\SuperAdmin\Domain\Models\SupportTicket as EloquentSupportTicket;
use App\Modules\SuperAdmin\Domain\Repositories\SupportTicketRepositoryInterface;

class EloquentSupportTicketRepository implements SupportTicketRepositoryInterface
{
    public function getAll(): array
    {
        return EloquentSupportTicket::all()->map(function ($ticket) {
            return new DomainSupportTicket(
                id: $ticket->id,
                ticketId: $ticket->ticket_id ?? ('TKT-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT)),
                subject: $ticket->subject ?? 'Demande d\'assistance',
                description: $ticket->description ?? '',
                schoolId: $ticket->school_id,
                schoolName: $ticket->school_name ?? ('École #' . $ticket->school_id),
                priority: $ticket->priority ?? 'medium',
                status: $ticket->status ?? 'open',
                category: $ticket->category ?? 'General',
                createdAt: $ticket->created_at?->format('Y-m-d H:i:s')
            );
        })->toArray();
    }
}
