<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\BackupRepositoryInterface;

class ListBackupsUseCase
{
    public function __construct(
        private BackupRepositoryInterface $backupRepository
    ) {}

    public function execute(int $perPage = 10)
    {
        return $this->backupRepository->paginate($perPage);
    }
}
