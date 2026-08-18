<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Finance\Application\DTOs\UpdateScholarshipTypeDTO;
use App\Modules\Finance\Domain\Repositories\ScholarshipTypeRepositoryInterface;

class UpdateScholarshipTypeUseCase
{
    private ScholarshipTypeRepositoryInterface $repository;

    public function __construct(ScholarshipTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id, UpdateScholarshipTypeDTO $dto)
    {
        return $this->repository->update($id, $dto->data);
    }
}
