<?php

namespace App\Modules\Canteen\Infrastructure\Repositories;

use App\Modules\Canteen\Domain\Models\MealRecord;
use App\Modules\Canteen\Domain\Repositories\MealRecordRepositoryInterface;
use Illuminate\Support\Carbon;

class EloquentMealRecordRepository implements MealRecordRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return MealRecord::create($data);
    }

    public function countToday(): int
    {
        return MealRecord::where('school_id', auth()->user()->school_id)
            ->whereDate('date', Carbon::today())
            ->count();
    }

    public function averagePriceToday(): float
    {
        return (float) (MealRecord::where('school_id', auth()->user()->school_id)
            ->whereDate('date', Carbon::today())
            ->avg('price') ?? 0);
    }

    public function dailyCountsForWeek(string $weekStartDate): array
    {
        $schoolId = auth()->user()->school_id;
        $start = Carbon::parse($weekStartDate);
        $counts = [];

        for ($i = 0; $i < 5; $i++) {
            $day = $start->copy()->addDays($i);
            $count = MealRecord::where('school_id', $schoolId)
                ->whereDate('date', $day->toDateString())
                ->count();

            $counts[] = [
                'label' => $day->translatedFormat('D'),
                'count' => $count,
            ];
        }

        return $counts;
    }
}
