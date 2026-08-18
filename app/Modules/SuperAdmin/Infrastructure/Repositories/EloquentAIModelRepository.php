<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\AIModel as DomainAIModel;
use App\Modules\SuperAdmin\Domain\Models\AIModel as EloquentAIModel;
use App\Modules\SuperAdmin\Domain\Repositories\AIModelRepositoryInterface;

class EloquentAIModelRepository implements AIModelRepositoryInterface
{
    public function getAll(): array
    {
        return EloquentAIModel::all()->map(function ($model) {
            return new DomainAIModel(
                id: $model->id,
                name: $model->name ?? 'Modèle IA',
                provider: $model->provider ?? 'OpenAI / Gemini',
                status: $model->status ?? 'online',
                statusLabel: $model->status_label ?? 'Opérationnel',
                latency: $model->latency ?? '45ms',
                color: $model->color ?? '#10B981'
            );
        })->toArray();
    }
}
