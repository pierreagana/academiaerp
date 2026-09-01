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

    /** Past this many minutes after a session's start with no check-in, the teacher is marked
     *  absent for it and can no longer point (or take that class's attendance) on their own —
     *  only an admin/staff user (who isn't gated by this check at all) can still act on it. */
    public const ABSENCE_DEADLINE_MINUTES = 60;

    /** The moment a session is considered missed if no check-in happened by then. */
    private function absenceDeadline(Timetable $slot): Carbon
    {
        return Carbon::today()->setTimeFromTimeString($slot->start_time)->addMinutes(self::ABSENCE_DEADLINE_MINUTES);
    }

    /** True once today's absence deadline for this slot has passed with no check-in recorded. */
    public function isSlotMissed(Timetable $slot, ?TeacherSessionCheckin $checkin): bool
    {
        return !$checkin && Carbon::now()->gt($this->absenceDeadline($slot));
    }

    /**
     * Idempotent: returns the existing checkin if the teacher already pointed for this session
     * today. Rejects a first-time check-in once ABSENCE_DEADLINE_MINUTES have passed since the
     * session started — that session is already marked absent and self-service pointing is over.
     */
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

        if ($now->gt($this->absenceDeadline($slot))) {
            throw new \RuntimeException(
                "Le délai de pointage pour ce cours est dépassé (plus d'une heure après le début). ".
                "Vous êtes marqué absent pour cette séance — contactez l'administration si c'est une erreur."
            );
        }

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

    /**
     * Admin-only correction: force-records a check-in for a teacher who was locked out past the
     * absence deadline, restoring their ability to take that class's attendance. Bypasses the
     * deadline entirely — callers must enforce their own permission check before calling this.
     */
    public function adminOverrideCheckIn(Teacher $teacher, int $timetableId): TeacherSessionCheckin
    {
        $slot = Timetable::where('teacher_id', $teacher->id)->findOrFail($timetableId);

        return TeacherSessionCheckin::updateOrCreate(
            ['teacher_id' => $teacher->id, 'timetable_id' => $slot->id, 'date' => Carbon::today()->toDateString()],
            ['checked_in_at' => Carbon::now(), 'late_minutes' => 0]
        );
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

    /**
     * True once the teacher has run out of every chance to point for this class today — every
     * published slot they have with it today is past its absence deadline, and no check-in
     * exists for any of them. This is the hard "marked absent, no more appel" lock; a single
     * still-open slot (deadline not yet reached) keeps the softer "you must check in" gate instead.
     */
    public function missedCheckinForClassToday(Teacher $teacher, int $academicClassId): bool
    {
        if ($this->hasCheckedInForClassToday($teacher, $academicClassId)) {
            return false;
        }

        $days = [1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi', 7 => 'dimanche'];
        $todayName = $days[now()->dayOfWeekIso] ?? null;

        if (!$todayName) {
            return false;
        }

        $slots = Timetable::where('teacher_id', $teacher->id)
            ->where('academic_class_id', $academicClassId)
            ->where('status', 'published')
            ->where('day_of_week', $todayName)
            ->get();

        if ($slots->isEmpty()) {
            return false;
        }

        return $slots->every(fn (Timetable $slot) => $this->isSlotMissed($slot, null));
    }
}
