<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\BackupLog as DomainBackupLog;
use App\Modules\SuperAdmin\Domain\Models\BackupLog as EloquentBackupLog;
use App\Modules\SuperAdmin\Domain\Repositories\BackupRepositoryInterface;

class EloquentBackupRepository implements BackupRepositoryInterface
{
    public function getAll(): array
    {
        return EloquentBackupLog::all()->map(fn($b) => $this->mapToDomain($b))->toArray();
    }
    
    public function paginate(int $perPage = 10)
    {
        $paginator = EloquentBackupLog::orderBy('created_at', 'desc')->paginate($perPage);
        $paginator->getCollection()->transform(fn($b) => $this->mapToDomain($b));
        return $paginator;
    }

    private function mapToDomain(EloquentBackupLog $model): DomainBackupLog
    {
        return new DomainBackupLog(
            id: $model->id,
            fileName: $model->file_name ?? ('backup_' . ($model->id ?? 1) . '.sql'),
            sizeMb: (float)($model->size_mb ?? 0.0),
            status: $model->status ?? 'completed',
            type: $model->type ?? 'full',
            completedAt: $model->completed_at?->format('Y-m-d H:i:s') ?? $model->created_at?->format('Y-m-d H:i:s')
        );
    }
}
