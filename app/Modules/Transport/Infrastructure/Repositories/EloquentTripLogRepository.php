<?php

namespace App\Modules\Transport\Infrastructure\Repositories;

use App\Modules\Transport\Domain\Models\TripLog;
use App\Modules\Transport\Domain\Repositories\TripLogRepositoryInterface;

class EloquentTripLogRepository implements TripLogRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return TripLog::create($data);
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = TripLog::where('school_id', auth()->user()->school_id)->with(['route', 'bus.driver']);

        if (!empty($filters['bus_id'])) {
            $query->where('bus_id', $filters['bus_id']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('trip_date', $filters['date']);
        }

        return $query->latest('trip_date')->latest('id')->paginate($perPage);
    }

    public function forRange(string $start, string $end)
    {
        return TripLog::where('school_id', auth()->user()->school_id)
            ->whereBetween('trip_date', [$start, $end])
            ->get();
    }

    public function latestForBusOnDate($busId, string $date)
    {
        return TripLog::where('school_id', auth()->user()->school_id)
            ->where('bus_id', $busId)
            ->whereDate('trip_date', $date)
            ->latest('id')
            ->first();
    }
}
