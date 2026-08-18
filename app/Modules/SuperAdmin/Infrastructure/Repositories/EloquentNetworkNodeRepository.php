<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\NetworkNode as DomainNetworkNode;
use App\Modules\SuperAdmin\Domain\Models\NetworkNode as EloquentNetworkNode;
use App\Modules\SuperAdmin\Domain\Repositories\NetworkNodeRepositoryInterface;

class EloquentNetworkNodeRepository implements NetworkNodeRepositoryInterface
{
    public function getAll(): array
    {
        return EloquentNetworkNode::all()->map(function ($node) {
            return new DomainNetworkNode(
                id: $node->id,
                name: $node->name ?? 'Noeud Système',
                region: $node->region ?? 'Dakar-1',
                status: $node->status ?? 'online',
                ipAddress: $node->ip_address ?? '127.0.0.1',
                latencyMs: (float)($node->latency_ms ?? 10),
                loadPct: (float)($node->load_pct ?? 30)
            );
        })->toArray();
    }
}
