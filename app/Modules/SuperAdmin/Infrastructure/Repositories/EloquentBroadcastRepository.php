<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\BroadcastMessage as DomainBroadcastMessage;
use App\Modules\SuperAdmin\Domain\Models\BroadcastMessage as EloquentBroadcastMessage;
use App\Modules\SuperAdmin\Domain\Repositories\BroadcastRepositoryInterface;

class EloquentBroadcastRepository implements BroadcastRepositoryInterface
{
    public function getAll(): array
    {
        return EloquentBroadcastMessage::all()->map(function ($msg) {
            return new DomainBroadcastMessage(
                id: $msg->id,
                title: $msg->title ?? 'Annonce Système',
                message: $msg->message ?? '',
                type: $msg->type ?? 'info',
                targetRoles: $msg->target_roles ?? [],
                isActive: (bool)($msg->is_active ?? true),
                expiresAt: $msg->expires_at?->format('Y-m-d H:i:s')
            );
        })->toArray();
    }
}
