<?php

namespace App\Modules\HR\Application\UseCases;

use App\Modules\HR\Application\DTOs\CreatePayrollComponentDTO;
use App\Modules\HR\Domain\Repositories\PayrollComponentRepositoryInterface;

class CreatePayrollComponentUseCase
{
    private PayrollComponentRepositoryInterface $repository;

    public function __construct(PayrollComponentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreatePayrollComponentDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
