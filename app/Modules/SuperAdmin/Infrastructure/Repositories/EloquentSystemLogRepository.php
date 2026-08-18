<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\SystemLog as DomainSystemLog;
use App\Modules\SuperAdmin\Domain\Models\SystemLog as EloquentSystemLog;
use App\Modules\SuperAdmin\Domain\Repositories\SystemLogRepositoryInterface;

class EloquentSystemLogRepository implements SystemLogRepositoryInterface
{
    public function getAll(): array
    {
        return EloquentSystemLog::orderBy('created_at', 'desc')->get()->map(fn($l) => $this->mapToDomain($l))->toArray();
    }
    
    public function paginate(int $perPage = 10)
    {
        $paginator = EloquentSystemLog::orderBy('created_at', 'desc')->paginate($perPage);
        $paginator->getCollection()->transform(fn($l) => $this->mapToDomain($l));
        return $paginator;
    }

    private function mapToDomain(EloquentSystemLog $model): DomainSystemLog
    {
        return new DomainSystemLog(
            id: $model->id,
            level: $model->level,
            message: $model->message,
            source: $model->source,
            createdAt: $model->created_at?->format('Y-m-d H:i:s')
        );
    }
}
