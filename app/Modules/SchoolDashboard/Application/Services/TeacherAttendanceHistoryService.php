<?php

namespace App\Modules\SchoolDashboard\Application\Services;

use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Academic\Domain\Models\Timetable;
use App\Modules\Presence\Domain\Models\AccessLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The teacher/staff member's own badge-in/badge-out history ("Ma Présence"),
 * built strictly from their own access_logs rows — not to be confused with
 * the Presence module's student attendance (AttendanceRecord) or the admin
 * access-control dashboard, which lists everyone.
 */
class TeacherAttendanceHistoryService
{
    private const GRACE_MINUTES = 10;
    private const DAY_NAMES = [1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi', 7 => 'dimanche'];

    /**
     * Real per-day pointage rows for [$start, $end], built by pairing each day's
     * earliest 'entry' and latest 'exit' access_logs scan against the teacher's
     * own real Timetable start time for that weekday (+grace period).
     *
     * @return Collection<string, array{status:string, arrival:?Carbon, departure:?Carbon, duration_minutes:?int, late_minutes:int, expected_start:?string}>
     */
    public function buildDailyLog(Teacher $teacher, Carbon $start, Carbon $end): Collection
    {
        $today = Carbon::today();
        $rows = [];

        for ($date = $start->copy()->startOfDay(); $date->lte($end); $date->addDay()) {
            $dayName = self::DAY_NAMES[$date->dayOfWeekIso] ?? null;
            $expectedStart = $dayName ? $this->expectedStartTimeFor($teacher, $dayName) : null;

            if (!$expectedStart || $date->gt($today)) {
                $rows[$date->toDateString()] = [
                    'status' => 'not_applicable',
                    'arrival' => null,
                    'departure' => null,
                    'duration_minutes' => null,
                    'late_minutes' => 0,
                    'expected_start' => $expectedStart,
                ];
                continue;
            }

            $dayLogs = AccessLog::where('holder_type', 'teacher')
                ->where('holder_id', $teacher->id)
                ->whereBetween('occurred_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                ->orderBy('occurred_at')
                ->get();

            $entry = $dayLogs->where('action', AccessLog::ACTION_ENTRY)->first();
            $exit = $dayLogs->where('action', AccessLog::ACTION_EXIT)->last();

            if (!$entry) {
                $rows[$date->toDateString()] = [
                    'status' => 'absent',
                    'arrival' => null,
                    'departure' => null,
                    'duration_minutes' => null,
                    'late_minutes' => 0,
                    'expected_start' => $expectedStart,
                ];
                continue;
            }

            $arrival = $entry->occurred_at;
            $expectedAt = $date->copy()->setTimeFromTimeString($expectedStart)->addMinutes(self::GRACE_MINUTES);
            $lateMinutes = $arrival->gt($expectedAt) ? $expectedAt->diffInMinutes($arrival) : 0;

            $departure = $exit && $exit->occurred_at->gt($arrival) ? $exit->occurred_at : null;

            $rows[$date->toDateString()] = [
                'status' => $lateMinutes > 0 ? 'late' : 'present',
                'arrival' => $arrival,
                'departure' => $departure,
                'duration_minutes' => $departure ? $arrival->diffInMinutes($departure) : null,
                'late_minutes' => $lateMinutes,
                'expected_start' => $expectedStart,
            ];
        }

        return collect($rows);
    }

    /** Real monthly aggregates. No leave-balance field — no such concept exists in this schema. */
    public function monthlyStats(Teacher $teacher, Carbon $monthStart): array
    {
        $daily = $this->buildDailyLog($teacher, $monthStart->copy()->startOfMonth(), $monthStart->copy()->endOfMonth());
        $applicable = $daily->filter(fn ($row) => $row['status'] !== 'not_applicable');

        $daysScheduled = $applicable->count();
        $presentCount = $applicable->where('status', 'present')->count();
        $lateRows = $applicable->where('status', 'late');
        $absentCount = $applicable->where('status', 'absent')->count();

        return [
            'punctuality_rate' => $daysScheduled > 0 ? round($presentCount / $daysScheduled * 100) : null,
            'days_worked' => $presentCount + $lateRows->count(),
            'days_scheduled' => $daysScheduled,
            'late_count' => $lateRows->count(),
            'late_minutes_total' => (int) $lateRows->sum('late_minutes'),
            'absent_count' => $absentCount,
        ];
    }

    /** Calendar-friendly array keyed by day-of-month => status ('present'|'late'|'absent'|null). */
    public function monthCalendar(Teacher $teacher, Carbon $monthStart): array
    {
        $daily = $this->buildDailyLog($teacher, $monthStart->copy()->startOfMonth(), $monthStart->copy()->endOfMonth());

        $calendar = [];
        foreach ($daily as $date => $row) {
            $day = (int) Carbon::parse($date)->format('j');
            $calendar[$day] = $row['status'] === 'not_applicable' ? null : $row['status'];
        }

        return $calendar;
    }

    /** Teacher's own earliest published Timetable start time for a French lowercase weekday name, or null if nothing scheduled that day. */
    private function expectedStartTimeFor(Teacher $teacher, string $dayOfWeek): ?string
    {
        return Timetable::where('teacher_id', $teacher->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('status', 'published')
            ->orderBy('start_time')
            ->value('start_time');
    }
}
