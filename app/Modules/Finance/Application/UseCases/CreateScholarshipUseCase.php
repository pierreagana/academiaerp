<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Finance\Application\DTOs\CreateScholarshipDTO;
use App\Modules\Finance\Domain\Repositories\ScholarshipRepositoryInterface;

class CreateScholarshipUseCase
{
    private ScholarshipRepositoryInterface $repository;

    public function __construct(ScholarshipRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateScholarshipDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
