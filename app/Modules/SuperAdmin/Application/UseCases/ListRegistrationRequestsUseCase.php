<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\RegistrationRequestRepositoryInterface;

class ListRegistrationRequestsUseCase
{
    public function __construct(
        private RegistrationRequestRepositoryInterface $requestRepository
    ) {}

    /**
     * @param int $perPage
     * @param string|null $search
     * @param string|null $status
     * @param string|null $country
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function execute(
        int $perPage = 10,
        ?string $search = null,
        ?string $status = null,
        ?string $country = null
    ) {
        return $this->requestRepository->paginate($perPage, $search, $status, $country);
    }
}
