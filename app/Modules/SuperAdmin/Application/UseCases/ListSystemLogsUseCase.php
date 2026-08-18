<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\SystemLogRepositoryInterface;

class ListSystemLogsUseCase
{
    public function __construct(
        private SystemLogRepositoryInterface $logRepository
    ) {}

    public function execute(int $perPage = 15)
    {
        return $this->logRepository->paginate($perPage);
    }
}
