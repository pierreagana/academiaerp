<?php

namespace App\Modules\Transport\Infrastructure\Repositories;

use App\Modules\Transport\Domain\Models\RouteStop;
use App\Modules\Transport\Domain\Repositories\RouteStopRepositoryInterface;

class EloquentRouteStopRepository implements RouteStopRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return RouteStop::create($data);
    }

    public function update($id, array $data)
    {
        $stop = $this->find($id);
        $stop->update($data);
        return $stop;
    }

    public function delete($id)
    {
        $this->find($id)->delete();
    }

    public function find($id)
    {
        return RouteStop::where('school_id', auth()->user()->school_id)->with('students')->findOrFail($id);
    }

    public function forRoute($routeId)
    {
        return RouteStop::where('school_id', auth()->user()->school_id)
            ->where('route_id', $routeId)
            ->withCount('students')
            ->with('students')
            ->orderBy('sequence')
            ->get();
    }

    public function nextSequence($routeId): int
    {
        $max = RouteStop::where('school_id', auth()->user()->school_id)
            ->where('route_id', $routeId)
            ->max('sequence');

        return $max === null ? 1 : $max + 1;
    }

    public function swapSequence($id, string $direction)
    {
        $stop = $this->find($id);

        $neighbor = RouteStop::where('school_id', auth()->user()->school_id)
            ->where('route_id', $stop->route_id)
            ->when($direction === 'up', fn ($q) => $q->where('sequence', '<', $stop->sequence)->orderByDesc('sequence'))
            ->when($direction === 'down', fn ($q) => $q->where('sequence', '>', $stop->sequence)->orderBy('sequence'))
            ->first();

        if ($neighbor) {
            [$a, $b] = [$stop->sequence, $neighbor->sequence];
            $stop->update(['sequence' => $b]);
            $neighbor->update(['sequence' => $a]);
        }

        return $stop;
    }

    public function syncStudents($stopId, array $studentIds)
    {
        $stop = $this->find($stopId);
        $stop->students()->sync($studentIds);
        return $stop;
    }

    public function detachStudent($stopId, $studentId)
    {
        $stop = $this->find($stopId);
        $stop->students()->detach($studentId);
    }

    /**
     * Unlike detachStudent(), only removes the pivot row for this one period — a
     * student assigned to both morning and evening on the same stop keeps the
     * other period's row intact. belongsToMany::detach() ignores wherePivot()
     * constraints, so this goes at the pivot table directly.
     */
    public function detachStudentForPeriod($stopId, $studentId, string $period)
    {
        \Illuminate\Support\Facades\DB::table('transport_route_stop_student')
            ->where('route_stop_id', $stopId)
            ->where('student_id', $studentId)
            ->where('period', $period)
            ->delete();
    }
}
