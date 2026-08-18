<?php

namespace App\Modules\ReportCard\Application\UseCases;

use App\Modules\ReportCard\Application\DTOs\CreateSubdomainDTO;
use App\Modules\ReportCard\Domain\Repositories\ReportCardSubdomainRepositoryInterface;

class CreateSubdomainUseCase
{
    public function __construct(private ReportCardSubdomainRepositoryInterface $repository) {}

    public function execute(CreateSubdomainDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
