<?php

namespace App\Modules\SuperAdmin\Domain\Repositories;

interface RegistrationRequestRepositoryInterface
{
    /**
     * @return \App\Modules\SuperAdmin\Domain\Entities\RegistrationRequest[]
     */
    public function getAll(): array;

    /**
     * @param int $perPage
     * @param string|null $search
     * @param string|null $status
     * @param string|null $country
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(
        int $perPage = 10,
        ?string $search = null,
        ?string $status = null,
        ?string $country = null
    );
}
