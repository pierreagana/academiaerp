<?php

namespace App\Modules\ReportCard\Application\UseCases;

use App\Modules\ReportCard\Application\DTOs\CreateCompetencyDTO;
use App\Modules\ReportCard\Domain\Repositories\ReportCardCompetencyRepositoryInterface;

class CreateCompetencyUseCase
{
    public function __construct(private ReportCardCompetencyRepositoryInterface $repository) {}

    public function execute(CreateCompetencyDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
