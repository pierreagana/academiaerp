<?php

namespace App\Modules\Transport\Infrastructure\Repositories;

use App\Modules\Transport\Domain\Models\Driver;
use App\Modules\Transport\Domain\Repositories\DriverRepositoryInterface;

class EloquentDriverRepository implements DriverRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Driver::create($data);
    }

    public function find($id)
    {
        return Driver::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function all()
    {
        return Driver::where('school_id', auth()->user()->school_id)->orderBy('first_name')->get();
    }
}
