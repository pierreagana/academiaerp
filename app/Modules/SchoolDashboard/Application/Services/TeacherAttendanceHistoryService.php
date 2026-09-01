<?php

namespace App\Modules\SchoolDashboard\Application\Services;

use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Academic\Domain\Models\Timetable;
use App\Modules\Presence\Domain\Models\AccessLog;
use App\Modules\Presence\Domain\Models\TeacherSessionCheckin;
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
                    'sessions' => collect(),
                    'sessions_scheduled' => 0,
                    'sessions_checked_in' => 0,
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
                $absentSessions = $this->sessionsFor($teacher, $date);

                $rows[$date->toDateString()] = [
                    'status' => 'absent',
                    'arrival' => null,
                    'departure' => null,
                    'duration_minutes' => null,
                    'late_minutes' => 0,
                    'expected_start' => $expectedStart,
                    'sessions' => $absentSessions,
                    'sessions_scheduled' => $absentSessions->count(),
                    'sessions_checked_in' => $absentSessions->filter(fn ($s) => $s['checked_in'])->count(),
                ];
                continue;
            }

            $arrival = $entry->occurred_at;
            $expectedAt = $date->copy()->setTimeFromTimeString($expectedStart)->addMinutes(self::GRACE_MINUTES);
            // Cast: Carbon 3's diffInMinutes() returns an unrounded float, but this is
            // displayed directly ("Retard (Xm)") and declared as an int everywhere else.
            $lateMinutes = $arrival->gt($expectedAt) ? (int) round($expectedAt->diffInMinutes($arrival)) : 0;

            $departure = $exit && $exit->occurred_at->gt($arrival) ? $exit->occurred_at : null;

            $sessions = $this->sessionsFor($teacher, $date);

            $rows[$date->toDateString()] = [
                'status' => $lateMinutes > 0 ? 'late' : 'present',
                'arrival' => $arrival,
                'departure' => $departure,
                // Cast: Carbon 3's diffInMinutes() returns an unrounded float, but this column
                // is declared as ?int and fed straight into intdiv() by its callers.
                'duration_minutes' => $departure ? (int) round($arrival->diffInMinutes($departure)) : null,
                'late_minutes' => $lateMinutes,
                'expected_start' => $expectedStart,
                'sessions' => $sessions,
                'sessions_scheduled' => $sessions->count(),
                'sessions_checked_in' => $sessions->filter(fn ($s) => $s['checked_in'])->count(),
            ];
        }

        return collect($rows);
    }

    /**
     * Real per-course-session pointage for one day ("présence au cours",
     * distinct from the school-wide badge-in above) — every published
     * Timetable slot this teacher has that weekday, matched against any
     * TeacherSessionCheckin recorded for that exact date+slot. Deduplicated
     * by (day_of_week, start_time) keeping the newest valid_from at or before
     * $date, same versioning rule used for the timetable everywhere else in
     * this codebase — otherwise a stale re-versioned slot would double-count.
     */
    private function sessionsFor(Teacher $teacher, Carbon $date): Collection
    {
        $dayName = self::DAY_NAMES[$date->dayOfWeekIso] ?? null;
        if (!$dayName) {
            return collect();
        }

        $slots = Timetable::where('teacher_id', $teacher->id)
            ->where('day_of_week', $dayName)
            ->where('status', 'published')
            ->where('valid_from', '<=', $date->toDateString())
            ->with(['subject', 'academicClass'])
            ->orderBy('start_time')
            ->get();

        $checkins = TeacherSessionCheckin::where('teacher_id', $teacher->id)
            ->where('date', $date->toDateString())
            ->get()
            ->keyBy('timetable_id');

        // Same dedup rule as TeacherDashboardService::todaysCourses(): keep whichever
        // re-versioned Timetable row the teacher actually checked into that day, if
        // any, so a check-in recorded against a since-superseded slot still counts —
        // otherwise blindly keeping only the newest valid_from would silently drop a
        // real check-in the teacher already made.
        $slots = $slots->groupBy('start_time')
            ->map(fn ($group) => $group->first(fn ($slot) => $checkins->has($slot->id)) ?? $group->sortByDesc('valid_from')->first())
            ->values();

        return $slots->map(function ($slot) use ($checkins) {
            $checkin = $checkins->get($slot->id);

            return [
                'timetable_id' => $slot->id,
                'subject' => $slot->subject?->name,
                'class' => $slot->academicClass?->name,
                'start_time' => $slot->start_time,
                'checked_in' => (bool) $checkin,
                'checked_in_at' => $checkin?->checked_in_at,
                'late_minutes' => $checkin?->late_minutes ?? 0,
            ];
        })->values();
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

        $sessionsScheduled = (int) $applicable->sum('sessions_scheduled');
        $sessionsCheckedIn = (int) $applicable->sum('sessions_checked_in');

        return [
            'punctuality_rate' => $daysScheduled > 0 ? round($presentCount / $daysScheduled * 100) : null,
            'days_worked' => $presentCount + $lateRows->count(),
            'days_scheduled' => $daysScheduled,
            'late_count' => $lateRows->count(),
            'late_minutes_total' => (int) $lateRows->sum('late_minutes'),
            'absent_count' => $absentCount,
            'sessions_scheduled' => $sessionsScheduled,
            'sessions_checked_in' => $sessionsCheckedIn,
            'sessions_rate' => $sessionsScheduled > 0 ? round($sessionsCheckedIn / $sessionsScheduled * 100) : null,
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
