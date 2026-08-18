<?php

namespace App\Modules\Communication\Application\Services;

use App\Modules\Communication\Domain\Models\Event;
use App\Modules\Communication\Domain\Models\EventRegistration;

class EventStatsService
{
    public function dashboardStats(int $schoolId): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $eventsThisMonth = Event::where('school_id', $schoolId)
            ->whereBetween('start_at', [$monthStart, $monthEnd])
            ->count();

        $activeEventIds = Event::where('school_id', $schoolId)
            ->where('status', '!=', 'cancelled')
            ->pluck('id');

        $registrations = EventRegistration::whereIn('event_id', $activeEventIds)->get();
        $totalRegistrations = $registrations->count();
        $validatedRegistrations = $registrations->where('parental_authorization', 'validated')->count();
        $participationRate = $totalRegistrations > 0
            ? round(($validatedRegistrations / $totalRegistrations) * 100)
            : 0;

        $budgetEngaged = (float) Event::where('school_id', $schoolId)
            ->where('status', '!=', 'cancelled')
            ->sum('estimated_budget');

        $pendingValidation = Event::where('school_id', $schoolId)
            ->where('status', 'pending')
            ->count();

        return [
            'events_this_month' => $eventsThisMonth,
            'participation_rate' => $participationRate,
            'budget_engaged' => $budgetEngaged,
            'pending_validation' => $pendingValidation,
        ];
    }

    public function typeBreakdown(int $schoolId): array
    {
        $events = Event::where('school_id', $schoolId)->get();
        $total = $events->count();

        if ($total === 0) {
            return [];
        }

        return $events->groupBy('type')
            ->map(function ($group, $type) use ($total) {
                return [
                    'type' => $type,
                    'count' => $group->count(),
                    'percent' => round(($group->count() / $total) * 100),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();
    }
}
