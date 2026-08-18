<?php

namespace App\Modules\Academic\Application\UseCases\Guardian;

use App\Modules\Academic\Domain\Repositories\GuardianRepositoryInterface;

class DeleteGuardianUseCase
{
    private $repository;

    public function __construct(GuardianRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
