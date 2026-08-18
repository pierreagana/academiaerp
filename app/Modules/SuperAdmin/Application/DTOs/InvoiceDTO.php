<?php

namespace App\Modules\SuperAdmin\Application\DTOs;

class InvoiceDTO
{
    public function __construct(
        public readonly ?int    $id,
        public readonly string  $invoiceNumber,
        public readonly ?int    $schoolId,
        public readonly string  $schoolName,
        public readonly float   $amount,
        public readonly string  $status,
        public readonly ?string $issueDate,
        public readonly ?string $dueDate,
        public readonly ?string $planName,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:             $data['id'] ?? null,
            invoiceNumber:  $data['invoice_number'],
            schoolId:       $data['school_id'] ?? null,
            schoolName:     $data['school_name'],
            amount:         (float)($data['amount'] ?? 0),
            status:         $data['status'] ?? 'pending',
            issueDate:      $data['issue_date'] ?? null,
            dueDate:        $data['due_date'] ?? null,
            planName:       $data['plan_name'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'invoice_number' => $this->invoiceNumber,
            'school_id'      => $this->schoolId,
            'school_name'    => $this->schoolName,
            'amount'         => $this->amount,
            'status'         => $this->status,
            'issue_date'     => $this->issueDate,
            'due_date'       => $this->dueDate,
            'plan_name'      => $this->planName,
        ];
    }
}
