<?php

namespace App\Modules\HR\Application\UseCases;

use App\Modules\HR\Application\DTOs\CreateSalaryGradeDTO;
use App\Modules\HR\Domain\Repositories\SalaryGradeRepositoryInterface;

class CreateSalaryGradeUseCase
{
    private SalaryGradeRepositoryInterface $repository;

    public function __construct(SalaryGradeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateSalaryGradeDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
