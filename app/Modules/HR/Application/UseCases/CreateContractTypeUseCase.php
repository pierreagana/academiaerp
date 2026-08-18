<?php

namespace App\Modules\HR\Application\UseCases;

use App\Modules\HR\Domain\Repositories\ContractTypeRepositoryInterface;

class CreateContractTypeUseCase
{
    private ContractTypeRepositoryInterface $repository;

    public function __construct(ContractTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $name)
    {
        return $this->repository->create($name);
    }
}
