<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Finance\Domain\Repositories\ScholarshipTypeRepositoryInterface;

class DeleteScholarshipTypeUseCase
{
    private ScholarshipTypeRepositoryInterface $repository;

    public function __construct(ScholarshipTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
