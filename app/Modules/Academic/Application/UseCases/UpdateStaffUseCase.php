<?php

namespace App\Modules\Academic\Application\UseCases;

use App\Modules\Academic\Application\DTOs\UpdateStaffDTO;
use App\Modules\Academic\Application\Services\PortalAccountService;
use App\Modules\Academic\Domain\Repositories\StaffRepositoryInterface;

class UpdateStaffUseCase
{
    private StaffRepositoryInterface $repository;
    private PortalAccountService $portalAccountService;

    public function __construct(StaffRepositoryInterface $repository, PortalAccountService $portalAccountService)
    {
        $this->repository = $repository;
        $this->portalAccountService = $portalAccountService;
    }

    public function execute($id, UpdateStaffDTO $dto)
    {
        $plainPassword = null;

        if (!empty($dto->data['password']) && $dto->data['password'] !== '********') {
            $plainPassword = $dto->data['password'];
        }
        unset($dto->data['password']);

        $staff = $this->repository->update($id, $dto->data);

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
