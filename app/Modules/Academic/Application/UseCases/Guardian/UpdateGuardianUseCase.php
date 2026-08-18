<?php

namespace App\Modules\Academic\Application\UseCases\Guardian;

use App\Modules\Academic\Domain\Repositories\GuardianRepositoryInterface;
use App\Modules\Academic\Application\DTOs\UpdateGuardianDTO;

class UpdateGuardianUseCase
{
    private $repository;

    public function __construct(GuardianRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id, UpdateGuardianDTO $dto)
    {
        return $this->repository->update($id, $dto->data);
    }
}
