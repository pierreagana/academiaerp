<?php

namespace App\Modules\SuperAdmin\Domain\Repositories;

interface SaasModuleRepositoryInterface
{
    /**
     * @return \App\Modules\SuperAdmin\Domain\Entities\SaasModule[]
     */
    public function getAll(): array;

    /**
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 10);
}
