<?php

namespace App\Modules\Canteen\Infrastructure\Repositories;

use App\Modules\Canteen\Domain\Models\MenuItem;
use App\Modules\Canteen\Domain\Models\MenuWeek;
use App\Modules\Canteen\Domain\Repositories\MenuRepositoryInterface;
use Illuminate\Support\Carbon;

class EloquentMenuRepository implements MenuRepositoryInterface
{
    public function itemsForWeek(string $weekStartDate)
    {
        $start = Carbon::parse($weekStartDate);
        $end = $start->copy()->addDays(4);

        return MenuItem::where('school_id', auth()->user()->school_id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();
    }

    public function findItem($id)
    {
        return MenuItem::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function saveItem(array $data)
    {
        $schoolId = auth()->user()->school_id;

        $attributes = [
            'date' => $data['date'],
            'slot' => $data['slot'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'tags' => $data['tags'] ?? null,
            'allergens' => $data['allergens'] ?? null,
        ];

        if (!empty($data['id'])) {
            $item = MenuItem::where('school_id', $schoolId)->findOrFail($data['id']);
            $item->update($attributes);
            return $item;
        }

        $attributes['school_id'] = $schoolId;
        return MenuItem::create($attributes);
    }

    public function deleteItem($id)
    {
        $item = $this->findItem($id);
        return $item->delete();
    }

    public function weekFor(string $weekStartDate)
    {
        return MenuWeek::firstOrCreate([
            'school_id' => auth()->user()->school_id,
            'week_start_date' => $weekStartDate,
        ]);
    }

    public function publishWeek(string $weekStartDate)
    {
        $week = $this->weekFor($weekStartDate);
        $week->update(['published_at' => now()]);
        return $week;
    }
}
