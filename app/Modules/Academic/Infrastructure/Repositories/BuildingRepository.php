<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\Building;
use App\Modules\Academic\Domain\Repositories\BuildingRepositoryInterface;

class BuildingRepository implements BuildingRepositoryInterface
{
    public function all()
    {
        return Building::where('school_id', auth()->user()->school_id)->with('rooms')->get();
    }

    public function find($id)
    {
        return Building::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Building::create($data);
    }

    public function update($id, array $data)
    {
        $building = $this->find($id);
        $building->update($data);
        return $building;
    }

    public function delete($id)
    {
        return $this->find($id)->delete();
    }
}
