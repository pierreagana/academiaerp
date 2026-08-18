<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Finance\Application\DTOs\UpdateScholarshipDTO;
use App\Modules\Finance\Domain\Repositories\ScholarshipRepositoryInterface;

class UpdateScholarshipUseCase
{
    private ScholarshipRepositoryInterface $repository;

    public function __construct(ScholarshipRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id, UpdateScholarshipDTO $dto)
    {
        return $this->repository->update($id, $dto->data);
    }
}
