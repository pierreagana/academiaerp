<?php

namespace App\Modules\SuperAdmin\Domain\Repositories;

interface InvoiceRepositoryInterface
{
    /**
     * @return \App\Modules\SuperAdmin\Domain\Entities\Invoice[]
     */
    public function getAll(): array;

    /**
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 10);
    
    /**
     * @return float
     */
    public function getTotalPaidRevenue(): float;
}
