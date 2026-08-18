<?php

namespace App\Modules\Communication\Infrastructure\Repositories;

use App\Modules\Communication\Domain\Models\Event;
use App\Modules\Communication\Domain\Repositories\EventRepositoryInterface;
use Illuminate\Support\Carbon;

class EloquentEventRepository implements EventRepositoryInterface
{
    public function all()
    {
        return Event::where('school_id', auth()->user()->school_id)
            ->with(['room', 'academicClasses', 'teachers'])
            ->orderBy('start_at')
            ->get();
    }

    public function find($id)
    {
        return Event::where('school_id', auth()->user()->school_id)
            ->with(['room', 'academicClasses', 'teachers', 'registrations.student'])
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Event::create($data);
    }

    public function update($id, array $data)
    {
        $event = $this->find($id);
        $event->update($data);
        return $event;
    }

    public function delete($id)
    {
        $event = $this->find($id);
        return $event->delete();
    }

    public function upcoming($limit = 5)
    {
        return Event::where('school_id', auth()->user()->school_id)
            ->where('start_at', '>=', Carbon::now()->startOfDay())
            ->with('room')
            ->orderBy('start_at')
            ->limit($limit)
            ->get();
    }

    public function forMonth(int $year, int $month, array $filters = [])
    {
        $query = Event::where('school_id', auth()->user()->school_id)
            ->with(['academicClasses'])
            ->whereYear('start_at', $year)
            ->whereMonth('start_at', $month);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['organizer_name'])) {
            $query->where('organizer_name', 'like', '%' . $filters['organizer_name'] . '%');
        }

        if (!empty($filters['academic_class_id'])) {
            $query->whereHas('academicClasses', function ($q) use ($filters) {
                $q->where('academic_classes.id', $filters['academic_class_id']);
            });
        }

        return $query->orderBy('start_at')->get();
    }

    public function syncClasses($eventId, array $classIds)
    {
        $event = $this->find($eventId);
        $event->academicClasses()->sync($classIds);
    }

    public function syncTeachers($eventId, array $teacherIds)
    {
        $event = $this->find($eventId);
        $event->teachers()->sync($teacherIds);
    }
}
