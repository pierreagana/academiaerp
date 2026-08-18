<?php

namespace App\Modules\Transport\Infrastructure\Repositories;

use App\Modules\Transport\Domain\Models\Bus;
use App\Modules\Transport\Domain\Repositories\BusRepositoryInterface;

class EloquentBusRepository implements BusRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Bus::create($data);
    }

    public function update($id, array $data)
    {
        $bus = $this->find($id);
        $bus->update($data);
        return $bus;
    }

    public function find($id)
    {
        return Bus::where('school_id', auth()->user()->school_id)->with('driver')->findOrFail($id);
    }

    public function all()
    {
        return Bus::where('school_id', auth()->user()->school_id)->with(['driver', 'routes'])->orderBy('bus_number')->get();
    }

    public function activeDrivable()
    {
        return Bus::where('school_id', auth()->user()->school_id)
            ->whereIn('status', ['en_service', 'disponible'])
            ->orderBy('bus_number')
            ->get();
    }

    public function countByStatus(): array
    {
        return Bus::where('school_id', auth()->user()->school_id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }
}
