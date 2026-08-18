<?php

namespace App\Modules\Infirmary\Application\UseCases;

use App\Modules\Infirmary\Application\DTOs\CreateMedicationDTO;
use App\Modules\Infirmary\Domain\Repositories\MedicationRepositoryInterface;

class CreateMedicationUseCase
{
    private MedicationRepositoryInterface $repository;

    public function __construct(MedicationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateMedicationDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
