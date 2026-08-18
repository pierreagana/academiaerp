<?php

namespace App\Modules\Presence\Application\Services;

use App\Modules\Presence\Domain\Models\AccessLog;
use Illuminate\Support\Carbon;

class AccessControlStatsService
{
    public function onCampusCount(int $schoolId, ?int $branchId = null): int
    {
        return AccessLog::where('school_id', $schoolId)
            ->whereBranch($branchId)
            ->where('authorized', true)
            ->whereNotNull('holder_type')
            ->orderByDesc('occurred_at')
            ->get(['holder_type', 'holder_id', 'action'])
            ->groupBy(fn ($log) => $log->holder_type . ':' . $log->holder_id)
            ->filter(fn ($group) => $group->first()->action === AccessLog::ACTION_ENTRY)
            ->count();
    }

    public function peakEntryHour(int $schoolId, ?int $branchId = null): ?string
    {
        $today = Carbon::today();

        $hour = AccessLog::where('school_id', $schoolId)
            ->whereBranch($branchId)
            ->where('action', AccessLog::ACTION_ENTRY)
            ->whereBetween('occurred_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->get(['occurred_at'])
            ->groupBy(fn ($log) => $log->occurred_at->format('H'))
            ->sortByDesc(fn ($group) => $group->count())
            ->keys()
            ->first();

        if ($hour === null) {
            return null;
        }

        return sprintf('%02d:00 - %02d:00', (int) $hour, (int) $hour + 1);
    }

    public function unauthorizedTodayCount(int $schoolId, ?int $branchId = null): int
    {
        $today = Carbon::today();

        return AccessLog::where('school_id', $schoolId)
            ->whereBranch($branchId)
            ->where('authorized', false)
            ->whereBetween('occurred_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->count();
    }
}
