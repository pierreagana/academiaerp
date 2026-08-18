<?php

namespace App\Modules\Academic\Application\UseCases;

use App\Modules\Academic\Application\DTOs\CreateStaffDTO;
use App\Modules\Academic\Application\Services\PortalAccountService;
use App\Modules\Academic\Domain\Repositories\StaffRepositoryInterface;

class CreateStaffUseCase
{
    private StaffRepositoryInterface $repository;
    private PortalAccountService $portalAccountService;

    public function __construct(StaffRepositoryInterface $repository, PortalAccountService $portalAccountService)
    {
        $this->repository = $repository;
        $this->portalAccountService = $portalAccountService;
    }

    public function execute(CreateStaffDTO $dto)
    {
        $plainPassword = $dto->data['password'] ?? null;

        $staff = $this->repository->create($dto->data);

        $this->portalAccountService->sync(
            $staff,
            'staff_id',
            [
                'first_name' => $dto->data['first_name'] ?? null,
                'last_name' => $dto->data['last_name'] ?? null,
                'email' => $dto->data['email'] ?? null,
                'login_id' => $dto->data['login_id'] ?? null,
                'role_id' => $dto->data['role_id'] ?? null,
            ],
            $plainPassword,
            $staff->school_id
        );

        return $staff;
    }
}
