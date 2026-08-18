<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\SystemAlertRule as DomainSystemAlertRule;
use App\Modules\SuperAdmin\Domain\Models\SystemAlertRule as EloquentSystemAlertRule;
use App\Modules\SuperAdmin\Domain\Repositories\SystemAlertRepositoryInterface;

class EloquentSystemAlertRepository implements SystemAlertRepositoryInterface
{
    public function getAll(): array
    {
        return EloquentSystemAlertRule::all()->map(function ($rule) {
            return new DomainSystemAlertRule(
                id: $rule->id,
                title: $rule->title ?? 'Règle système',
                severity: $rule->severity ?? 'medium',
                metric: $rule->metric ?? 'CPU',
                threshold: (string)($rule->threshold ?? '80%'),
                isActive: (bool)$rule->is_active
            );
        })->toArray();
    }
}
