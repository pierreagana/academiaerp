<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Finance\Application\DTOs\CreateScholarshipTypeDTO;
use App\Modules\Finance\Domain\Repositories\ScholarshipTypeRepositoryInterface;

class CreateScholarshipTypeUseCase
{
    private ScholarshipTypeRepositoryInterface $repository;

    public function __construct(ScholarshipTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateScholarshipTypeDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
