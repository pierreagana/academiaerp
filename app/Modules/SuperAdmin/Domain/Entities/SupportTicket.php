<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

use Carbon\Carbon;

class SupportTicket
{
    public readonly ?Carbon $createdAtObj;

    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $ticketId = null,
        public readonly ?string $subject = null,
        public readonly ?string $description = null,
        public readonly ?int $schoolId = null,
        public readonly ?string $schoolName = null,
        public readonly ?string $priority = null,
        public readonly ?string $status = null,
        public readonly ?string $category = null,
        public readonly ?string $createdAt = null
    ) {
        $this->createdAtObj = $createdAt ? Carbon::parse($createdAt) : Carbon::now();
    }

    public function __get(string $name)
    {
        return match ($name) {
            'ticket_id'   => $this->ticketId ?? ('TKT-' . str_pad($this->id ?? 1, 4, '0', STR_PAD_LEFT)),
            'school_name' => $this->schoolName ?? ('Établissement #' . ($this->schoolId ?? 1)),
            'created_at'  => $this->createdAtObj,
            default       => property_exists($this, $name) ? $this->$name : null,
        };
    }
}
