<?php

namespace App\Modules\Infirmary\Infrastructure\Repositories;

use App\Modules\Infirmary\Domain\Models\Intervention;
use App\Modules\Infirmary\Domain\Repositories\InterventionRepositoryInterface;
use Illuminate\Support\Carbon;

class EloquentInterventionRepository implements InterventionRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Intervention::create($data);
    }

    public function find($id)
    {
        return Intervention::where('school_id', auth()->user()->school_id)->with('student')->findOrFail($id);
    }

    public function countToday(): int
    {
        return Intervention::where('school_id', auth()->user()->school_id)
            ->whereDate('arrival_time', Carbon::today())
            ->count();
    }

    public function activeToday(): int
    {
        return Intervention::where('school_id', auth()->user()->school_id)
            ->whereDate('arrival_time', Carbon::today())
            ->where('decision', 'repos_infirmerie')
            ->count();
    }

    public function motiveCountsToday(): array
    {
        return Intervention::where('school_id', auth()->user()->school_id)
            ->whereDate('arrival_time', Carbon::today())
            ->selectRaw('motive, count(*) as total')
            ->groupBy('motive')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['motive' => $row->motive, 'count' => (int) $row->total])
            ->all();
    }

    public function motiveCountForRange(string $motive, string $start, string $end): int
    {
        return Intervention::where('school_id', auth()->user()->school_id)
            ->where('motive', $motive)
            ->whereBetween('arrival_time', [$start, $end])
            ->count();
    }

    public function recent(int $limit = 10)
    {
        return Intervention::where('school_id', auth()->user()->school_id)
            ->with('student')
            ->latest('arrival_time')
            ->limit($limit)
            ->get();
    }

    public function paginate(int $perPage = 15)
    {
        return Intervention::where('school_id', auth()->user()->school_id)
            ->with('student')
            ->latest('arrival_time')
            ->paginate($perPage);
    }

    public function forStudent($studentId)
    {
        return Intervention::where('school_id', auth()->user()->school_id)
            ->where('student_id', $studentId)
            ->latest('arrival_time')
            ->get();
    }
}
