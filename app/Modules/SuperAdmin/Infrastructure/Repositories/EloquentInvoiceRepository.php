<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\Invoice as DomainInvoice;
use App\Modules\SuperAdmin\Domain\Models\Invoice as EloquentInvoice;
use App\Modules\SuperAdmin\Domain\Repositories\InvoiceRepositoryInterface;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function getAll(): array
    {
        $eloquentInvoices = EloquentInvoice::with('school')->get();
        
        return $eloquentInvoices->map(function ($invoice) {
            return new DomainInvoice(
                id: $invoice->id,
                invoiceNumber: $invoice->invoice_number ?? 'INV-'.$invoice->id,
                schoolId: $invoice->school_id,
                schoolName: $invoice->school?->name ?? 'Établissement #'.$invoice->school_id,
                amount: (float)($invoice->amount ?? 0),
                status: $invoice->status ?? 'pending',
                issueDate: $invoice->issue_date?->format('Y-m-d'),
                dueDate: $invoice->due_date?->format('Y-m-d')
            );
        })->toArray();
    }
    
    public function paginate(int $perPage = 10)
    {
        $paginator = EloquentInvoice::with('school')->paginate($perPage);
        
        $paginator->getCollection()->transform(function ($invoice) {
            return new DomainInvoice(
                id: $invoice->id,
                invoiceNumber: $invoice->invoice_number ?? 'INV-'.$invoice->id,
                schoolId: $invoice->school_id,
                schoolName: $invoice->school?->name ?? 'Établissement #'.$invoice->school_id,
                amount: (float)($invoice->amount ?? 0),
                status: $invoice->status ?? 'pending',
                issueDate: $invoice->issue_date?->format('Y-m-d'),
                dueDate: $invoice->due_date?->format('Y-m-d')
            );
        });
        
        return $paginator;
    }
    
    public function getTotalPaidRevenue(): float
    {
        return (float) EloquentInvoice::where('status', 'paid')->sum('amount');
    }
}
