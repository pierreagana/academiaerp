<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\SchoolRepositoryInterface;

class ListSchoolsUseCase
{
    public function __construct(
        private SchoolRepositoryInterface $schoolRepository
    ) {}

    /**
     * @param int $perPage
     * @param string|null $search
     * @param string|null $status
     * @param string|null $country
     * @param string|null $plan
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function execute(
        int $perPage = 10,
        ?string $search = null,
        ?string $status = null,
        ?string $country = null,
        ?string $plan = null
    ) {
        return $this->schoolRepository->paginate($perPage, $search, $status, $country, $plan);
    }
}
