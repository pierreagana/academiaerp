<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\Room;
use App\Modules\Academic\Domain\Repositories\RoomRepositoryInterface;

class RoomRepository implements RoomRepositoryInterface
{
    public function all()
    {
        return Room::where('school_id', auth()->user()->school_id)->with('building')->get();
    }

    public function find($id)
    {
        return Room::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Room::create($data);
    }

    public function update($id, array $data)
    {
        $room = $this->find($id);
        $room->update($data);
        return $room;
    }

    public function delete($id)
    {
        return $this->find($id)->delete();
    }
}
