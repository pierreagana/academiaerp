<?php

namespace App\Modules\SchoolDashboard\Application\Services;

use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Room;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Academic\Domain\Models\Timetable;
use App\Modules\Bulletin\Application\Services\BulletinStatsService;
use App\Modules\Bulletin\Domain\Models\BulletinGrade;
use App\Modules\Presence\Domain\Models\AttendanceRecord;
use App\Modules\Presence\Domain\Models\TeacherSessionCheckin;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TeacherDashboardService
{
    private const REPEATED_ABSENCE_LOOKBACK_DAYS = 30;
    private const REPEATED_ABSENCE_MIN_COUNT = 3;

    public function __construct(private BulletinStatsService $bulletinStats) {}

    public function currentSemester(int $schoolId)
    {
        return $this->bulletinStats->currentSemester($schoolId);
    }

    public function previousSemester(int $schoolId, $current)
    {
        return $this->bulletinStats->previousSemester($schoolId, $current);
    }

    /** Classes this teacher teaches or is head teacher of, deduplicated. */
    public function myClasses(Teacher $teacher)
    {
        return $teacher->classes->merge($teacher->headTeacherClasses)->unique('id')->values();
    }

    private function myStudentIds(Teacher $teacher)
    {
        return $this->myClasses($teacher)->flatMap(fn ($class) => $class->students()->where('status', 'active')->pluck('id'))->unique()->values();
    }

    public function classAverage(Teacher $teacher, ?int $semesterId): ?float
    {
        if (!$semesterId) {
            return null;
        }

        $scores = BulletinGrade::where('teacher_id', $teacher->id)->where('semester_id', $semesterId)->pluck('score');

        return $scores->isNotEmpty() ? round($scores->avg(), 2) : null;
    }

    public function classAverageTrend(Teacher $teacher, ?int $currentSemesterId, ?int $previousSemesterId): ?string
    {
        $current = $this->classAverage($teacher, $currentSemesterId);
        $previous = $this->classAverage($teacher, $previousSemesterId);

        if ($current === null || $previous === null) {
            return null;
        }

        $diff = $current - $previous;
        $sign = $diff >= 0 ? '+' : '';

        return $sign . round($diff, 1) . ' pts';
    }

    public function attendanceRate(Teacher $teacher): ?float
    {
        $studentIds = $this->myStudentIds($teacher);
        if ($studentIds->isEmpty()) {
            return null;
        }

        $since = Carbon::now()->subDays(30)->toDateString();
        $records = AttendanceRecord::whereIn('student_id', $studentIds)->where('date', '>=', $since)->get();

        if ($records->isEmpty()) {
            return null;
        }

        $present = $records->whereIn('status', [AttendanceRecord::STATUS_PRESENT, AttendanceRecord::STATUS_LATE])->count();

        return round($present / $records->count() * 100);
    }

    /** Real count of (student × subject) slots this teacher hasn't graded yet this semester. */
    public function gradesToEnter(Teacher $teacher, ?int $semesterId): int
    {
        if (!$semesterId) {
            return 0;
        }

        $expected = 0;
        $filled = 0;

        foreach ($teacher->classes as $class) {
            $activeStudentIds = $class->students()->where('status', 'active')->pluck('id');
            if ($activeStudentIds->isEmpty()) {
                continue;
            }

            foreach ($teacher->subjects as $subject) {
                $expected += $activeStudentIds->count();
                $filled += BulletinGrade::where('semester_id', $semesterId)
                    ->where('subject_id', $subject->id)
                    ->where('teacher_id', $teacher->id)
                    ->whereIn('student_id', $activeStudentIds)
                    ->count();
            }
        }

        return max($expected - $filled, 0);
    }

    /** Real students whose grade in one of this teacher's subjects dropped vs the previous semester. */
    public function averageDropAlerts(Teacher $teacher, ?int $currentSemesterId, ?int $previousSemesterId, int $limit = 5): array
    {
        if (!$currentSemesterId || !$previousSemesterId) {
            return [];
        }

        $current = BulletinGrade::where('teacher_id', $teacher->id)->where('semester_id', $currentSemesterId)->get()->keyBy(fn ($g) => $g->student_id . '-' . $g->subject_id);
        $previous = BulletinGrade::where('teacher_id', $teacher->id)->where('semester_id', $previousSemesterId)->get();

        $drops = [];
        foreach ($previous as $prevGrade) {
            $key = $prevGrade->student_id . '-' . $prevGrade->subject_id;
            $currentGrade = $current->get($key);
            if (!$currentGrade) {
                continue;
            }

            $diff = $currentGrade->score - $prevGrade->score;
            if ($diff < 0) {
                $drops[] = [
                    'student' => $currentGrade->student,
                    'subject' => $currentGrade->subject,
                    'drop' => round(abs($diff), 1),
                ];
            }
        }

        usort($drops, fn ($a, $b) => $b['drop'] <=> $a['drop']);

        return array_slice($drops, 0, $limit);
    }

    /** Real students in this teacher's classes with repeated absences in the last 30 days. */
    public function repeatedAbsenceAlerts(Teacher $teacher, int $limit = 5): array
    {
        $studentIds = $this->myStudentIds($teacher);
        if ($studentIds->isEmpty()) {
            return [];
        }

        $since = Carbon::now()->subDays(self::REPEATED_ABSENCE_LOOKBACK_DAYS)->toDateString();

        $flagged = AttendanceRecord::whereIn('student_id', $studentIds)
            ->where('status', AttendanceRecord::STATUS_ABSENT)
            ->where('date', '>=', $since)
            ->with('student')
            ->get()
            ->groupBy('student_id')
            ->filter(fn ($group) => $group->count() >= self::REPEATED_ABSENCE_MIN_COUNT)
            ->map(fn ($group) => [
                'student' => $group->first()->student,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->take($limit);

        return $flagged->all();
    }

    /** Real recent (class, subject) grade batches this teacher entered, with real pass rate. */
    public function recentGradeEntries(Teacher $teacher, ?int $semesterId, int $limit = 5): array
    {
        if (!$semesterId) {
            return [];
        }

        $grades = BulletinGrade::where('teacher_id', $teacher->id)
            ->where('semester_id', $semesterId)
            ->with(['subject', 'student.academicClass'])
            ->get();

        $grouped = $grades->groupBy(fn ($g) => ($g->student->academic_class_id ?? 0) . '-' . $g->subject_id);

        $entries = $grouped->map(function ($group) {
            $first = $group->first();
            $total = $group->count();
            $passed = $group->where('score', '>=', 10)->count();

            return [
                'subject' => $first->subject->name ?? '—',
                'class' => $first->student->academicClass->name ?? '—',
                'date' => $group->max('updated_at'),
                'pass_rate' => $total > 0 ? round($passed / $total * 100) : null,
                'count' => $total,
            ];
        })->sortByDesc('date')->take($limit)->values();

        return $entries->all();
    }

    /** Real timetable slots for today, this teacher only. */
    public function todaySchedule(Teacher $teacher)
    {
        $days = [1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi', 7 => 'dimanche'];
        $today = $days[now()->dayOfWeekIso] ?? 'lundi';

        return Timetable::where('teacher_id', $teacher->id)
            ->where('day_of_week', $today)
            ->with(['subject', 'academicClass', 'room'])
            ->orderBy('start_time')
            ->get();
    }

    /** Real room this teacher most often uses for this class, from published Timetable slots. Null if none scheduled. */
    public function classRoom(Teacher $teacher, AcademicClass $class): ?Room
    {
        $roomId = Timetable::where('teacher_id', $teacher->id)
            ->where('academic_class_id', $class->id)
            ->where('status', 'published')
            ->whereNotNull('room_id')
            ->get()
            ->countBy('room_id')
            ->sortDesc()
            ->keys()
            ->first();

        return $roomId ? Room::find($roomId) : null;
    }

    /** Real average of this teacher's own grades for students in this specific class this semester. */
    public function classAverageForClass(Teacher $teacher, AcademicClass $class, ?int $semesterId): ?float
    {
        if (!$semesterId) {
            return null;
        }

        $scores = BulletinGrade::where('teacher_id', $teacher->id)
            ->where('semester_id', $semesterId)
            ->whereHas('student', fn ($q) => $q->where('academic_class_id', $class->id))
            ->pluck('score');

        return $scores->isNotEmpty() ? round($scores->avg(), 2) : null;
    }

    /** Real attendance rate for this class over the last $days, from AttendanceRecord.academic_class_id directly. */
    public function classAttendanceRate(AcademicClass $class, int $days = 30): ?float
    {
        $since = Carbon::now()->subDays($days)->toDateString();
        $records = AttendanceRecord::where('academic_class_id', $class->id)->where('date', '>=', $since)->get();

        if ($records->isEmpty()) {
            return null;
        }

        $present = $records->whereIn('status', [AttendanceRecord::STATUS_PRESENT, AttendanceRecord::STATUS_LATE])->count();

        return round($present / $records->count() * 100);
    }

    /** Real next upcoming published Timetable slot for this teacher, across all their classes, today or later this week. */
    public function nextCourse(Teacher $teacher): ?array
    {
        $days = [1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi', 7 => 'dimanche'];
        $todayIso = now()->dayOfWeekIso;
        $currentTime = now()->format('H:i:s');

        $slots = Timetable::where('teacher_id', $teacher->id)
            ->where('status', 'published')
            ->with(['subject', 'academicClass', 'room'])
            ->get();

        if ($slots->isEmpty()) {
            return null;
        }

        $dayIndex = fn ($dayOfWeek) => array_search($dayOfWeek, $days, true) ?: 8;

        $best = $slots
            ->map(function ($slot) use ($dayIndex, $todayIso, $currentTime) {
                $offset = ($dayIndex($slot->day_of_week) - $todayIso + 7) % 7;
                if ($offset === 0 && $slot->start_time < $currentTime) {
                    $offset = 7;
                }

                return ['slot' => $slot, 'offset' => $offset];
            })
            ->sortBy([['offset', 'asc'], ['slot.start_time', 'asc']])
            ->first();

        if (!$best) {
            return null;
        }

        $slot = $best['slot'];

        return [
            'subject' => $slot->subject,
            'academicClass' => $slot->academicClass,
            'room' => $slot->room,
            'date' => now()->addDays($best['offset']),
            'start_time' => $slot->start_time,
        ];
    }

    /** Every published Timetable slot for this teacher today, in order, each paired with its checkin (if the teacher has already pointed). */
    public function todaysCourses(Teacher $teacher): Collection
    {
        $days = [1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi', 7 => 'dimanche'];
        $todayName = $days[now()->dayOfWeekIso] ?? null;

        if (!$todayName) {
            return collect();
        }

        $slots = Timetable::where('teacher_id', $teacher->id)
            ->where('status', 'published')
            ->where('day_of_week', $todayName)
            ->with(['subject', 'academicClass', 'room'])
            ->orderBy('start_time')
            ->get();

        $checkins = TeacherSessionCheckin::where('teacher_id', $teacher->id)
            ->where('date', now()->toDateString())
            ->get()
            ->keyBy('timetable_id');

        return $slots->map(fn ($slot) => [
            'slot' => $slot,
            'checkin' => $checkins->get($slot->id),
        ]);
    }
}
