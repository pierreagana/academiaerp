<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

use Carbon\Carbon;

class Invoice
{
    public readonly ?Carbon $dueDateObj;

    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $invoiceNumber = null,
        public readonly ?int $schoolId = null,
        public readonly ?string $schoolName = null,
        public readonly float $amount = 0.0,
        public readonly string $status = 'pending',
        public readonly ?string $issueDate = null,
        public readonly ?string $dueDate = null
    ) {
        $this->dueDateObj = $dueDate ? Carbon::parse($dueDate) : Carbon::now();
    }

    public function __get(string $name)
    {
        return match ($name) {
            'invoice_number' => $this->invoiceNumber,
            'school_name'    => $this->schoolName ?? 'Établissement',
            'due_date'       => $this->dueDateObj,
            'issue_date'     => $this->issueDate,
            'school_id'      => $this->schoolId,
            default          => property_exists($this, $name) ? $this->$name : null,
        };
    }
}
