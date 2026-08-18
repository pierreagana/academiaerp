<?php

namespace App\Modules\SuperAdmin\Domain\Repositories;

interface SchoolRepositoryInterface
{
    /**
     * @return \App\Modules\SuperAdmin\Domain\Entities\School[]
     */
    public function getAll(): array;

    /**
     * @param int $perPage
     * @param string|null $search
     * @param string|null $status
     * @param string|null $country
     * @param string|null $plan
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(
        int $perPage = 10,
        ?string $search = null,
        ?string $status = null,
        ?string $country = null,
        ?string $plan = null
    );
}
