<?php

namespace App\Modules\Academic\Application\UseCases\Guardian;

use App\Modules\Academic\Domain\Repositories\GuardianRepositoryInterface;
use App\Modules\Academic\Application\DTOs\CreateGuardianDTO;

class CreateGuardianUseCase
{
    private $repository;

    public function __construct(GuardianRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateGuardianDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
