<?php

namespace App\Modules\ReportCard\Application\UseCases;

use App\Modules\ReportCard\Application\DTOs\CreateObservationDTO;
use App\Modules\ReportCard\Domain\Repositories\ReportCardObservationRepositoryInterface;

class CreateObservationUseCase
{
    public function __construct(private ReportCardObservationRepositoryInterface $repository) {}

    public function execute(CreateObservationDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
