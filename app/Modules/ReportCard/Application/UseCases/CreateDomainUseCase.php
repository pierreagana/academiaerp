<?php

namespace App\Modules\ReportCard\Application\UseCases;

use App\Modules\ReportCard\Application\DTOs\CreateDomainDTO;
use App\Modules\ReportCard\Domain\Repositories\ReportCardDomainRepositoryInterface;

class CreateDomainUseCase
{
    public function __construct(private ReportCardDomainRepositoryInterface $repository) {}

    public function execute(CreateDomainDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
