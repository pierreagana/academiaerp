<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\StudentClassMovement;
use App\Modules\Academic\Domain\Repositories\StudentClassMovementRepositoryInterface;

class EloquentStudentClassMovementRepository implements StudentClassMovementRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return StudentClassMovement::create($data);
    }

    public function recent(?string $type = null, int $limit = 15)
    {
        $query = StudentClassMovement::where('school_id', auth()->user()->school_id)
            ->with(['student', 'fromClass', 'toClass', 'movedBy'])
            ->latest();

        if ($type) {
            $query->where('type', $type);
        }

        return $query->limit($limit)->get();
    }
}
