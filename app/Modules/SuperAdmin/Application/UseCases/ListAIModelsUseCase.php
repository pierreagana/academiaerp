<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\AIModelRepositoryInterface;

class ListAIModelsUseCase
{
    public function __construct(
        private AIModelRepositoryInterface $aiModelRepository
    ) {}

    public function execute(): array
    {
        return $this->aiModelRepository->getAll();
    }
}
