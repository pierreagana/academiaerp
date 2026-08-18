<?php

namespace App\Modules\Infirmary\Application\UseCases;

use App\Modules\Infirmary\Application\DTOs\CreateInterventionDTO;
use App\Modules\Infirmary\Domain\Repositories\InterventionRepositoryInterface;

class RecordInterventionUseCase
{
    private InterventionRepositoryInterface $repository;

    public function __construct(InterventionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateInterventionDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
