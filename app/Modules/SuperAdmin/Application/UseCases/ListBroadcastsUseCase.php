<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\BroadcastRepositoryInterface;

class ListBroadcastsUseCase
{
    public function __construct(
        private BroadcastRepositoryInterface $broadcastRepository
    ) {}

    public function execute(): array
    {
        return $this->broadcastRepository->getAll();
    }
}
