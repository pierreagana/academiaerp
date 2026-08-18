<?php

namespace App\Modules\SchoolDashboard\Application\Services;

use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Academic\Domain\Models\Timetable;
use App\Modules\Presence\Domain\Models\TeacherSessionCheckin;
use Illuminate\Support\Carbon;

/**
 * A teacher pointing their own presence for one specific course session (not
 * to be confused with the daily building badge-in behind "Ma Présence", nor
 * with student attendance-taking) — this is what PresenceController gates on
 * before letting a teacher record their students' attendance for a class
 * they teach today.
 */
class TeacherSessionCheckinService
{
    private const GRACE_MINUTES = 10;

    /** Idempotent: returns the existing checkin if the teacher already pointed for this session today. */
    public function checkIn(Teacher $teacher, int $timetableId): TeacherSessionCheckin
    {
        $slot = Timetable::where('teacher_id', $teacher->id)->findOrFail($timetableId);

        $today = Carbon::today();
        $existing = TeacherSessionCheckin::where('teacher_id', $teacher->id)
            ->where('timetable_id', $slot->id)
            ->where('date', $today->toDateString())
            ->first();

        if ($existing) {
            return $existing;
        }

        $now = Carbon::now();
        $expectedAt = $today->copy()->setTimeFromTimeString($slot->start_time)->addMinutes(self::GRACE_MINUTES);
        $lateMinutes = $now->gt($expectedAt) ? $expectedAt->diffInMinutes($now) : 0;

        return TeacherSessionCheckin::create([
            'teacher_id' => $teacher->id,
            'timetable_id' => $slot->id,
            'date' => $today->toDateString(),
            'checked_in_at' => $now,
            'late_minutes' => $lateMinutes,
        ]);
    }

    /** True if this teacher has pointed at least one of today's sessions with this class — the take-attendance gate. */
    public function hasCheckedInForClassToday(Teacher $teacher, int $academicClassId): bool
    {
        return TeacherSessionCheckin::where('teacher_id', $teacher->id)
            ->where('date', Carbon::today()->toDateString())
            ->whereHas('timetable', fn ($q) => $q->where('academic_class_id', $academicClassId))
            ->exists();
    }

    /** True if this teacher actually has a published Timetable slot with this class today (used to decide whether the gate even applies). */
    public function teachesClassToday(Teacher $teacher, int $academicClassId): bool
    {
        $days = [1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi', 7 => 'dimanche'];
        $todayName = $days[now()->dayOfWeekIso] ?? null;

        if (!$todayName) {
            return false;
        }

        return Timetable::where('teacher_id', $teacher->id)
            ->where('academic_class_id', $academicClassId)
            ->where('status', 'published')
            ->where('day_of_week', $todayName)
            ->exists();
    }
}
