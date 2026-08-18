<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\InvoiceRepositoryInterface;

class ListInvoicesUseCase
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository
    ) {}

    /**
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function execute(int $perPage = 10)
    {
        return $this->invoiceRepository->paginate($perPage);
    }
}
