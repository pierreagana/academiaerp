<?php

namespace App\Modules\Presence\Infrastructure\Repositories;

use App\Modules\Presence\Domain\Models\AccessLog;
use App\Modules\Presence\Domain\Repositories\AccessLogRepositoryInterface;

class EloquentAccessLogRepository implements AccessLogRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return AccessLog::create($data);
    }

    public function paginate(array $filters, int $perPage = 15)
    {
        $query = AccessLog::where('school_id', auth()->user()->school_id)
            ->with('accessPoint')
            ->latest('occurred_at');

        if (!empty($filters['role_label'])) {
            $query->where('role_label', $filters['role_label']);
        }

        if (!empty($filters['access_point_id'])) {
            $query->where('access_point_id', $filters['access_point_id']);
        }

        if (array_key_exists('branch_id', $filters)) {
            $query->whereBranch($filters['branch_id']);
        }

        return $query->paginate($perPage);
    }

    public function lastForHolder(string $holderType, int $holderId)
    {
        return AccessLog::where('school_id', auth()->user()->school_id)
            ->where('holder_type', $holderType)
            ->where('holder_id', $holderId)
            ->latest('occurred_at')
            ->first();
    }
}
