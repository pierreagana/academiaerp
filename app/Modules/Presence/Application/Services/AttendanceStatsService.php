<?php

namespace App\Modules\Presence\Application\Services;

use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Presence\Domain\Models\AttendanceRecord;
use Illuminate\Support\Carbon;

class AttendanceStatsService
{
    public function dashboardStats(int $schoolId, string $date, ?int $branchId = null): array
    {
        $records = AttendanceRecord::where('school_id', $schoolId)->whereBranch($branchId)->where('date', $date)->get();
        $total = $records->count();
        $present = $records->where('status', AttendanceRecord::STATUS_PRESENT)->count();
        $absent = $records->where('status', AttendanceRecord::STATUS_ABSENT)->count();
        $late = $records->where('status', AttendanceRecord::STATUS_LATE)->count();

        return [
            'rate' => $total > 0 ? round(($present + $late) / $total * 100) : null,
            'absent' => $absent,
            'late' => $late,
            'recorded' => $total,
        ];
    }

    public function weeklyTrend(int $schoolId, ?int $branchId = null, int $days = 5): array
    {
        $trend = [];
        $cursor = Carbon::now();
        $collected = 0;

        while ($collected < $days) {
            if ($cursor->isWeekend()) {
                $cursor->subDay();
                continue;
            }

            $records = AttendanceRecord::where('school_id', $schoolId)->whereBranch($branchId)->where('date', $cursor->toDateString())->get();
            $total = $records->count();
            $present = $records->whereIn('status', [AttendanceRecord::STATUS_PRESENT, AttendanceRecord::STATUS_LATE])->count();

            $trend[] = [
                'label' => $cursor->translatedFormat('D'),
                'rate' => $total > 0 ? round($present / $total * 100) : null,
            ];

            $collected++;
            $cursor->subDay();
        }

        return array_reverse($trend);
    }

    public function repeatedAbsences(int $schoolId, ?int $branchId = null, int $lookbackDays = 7, int $minAbsences = 3)
    {
        $since = Carbon::now()->subDays($lookbackDays)->toDateString();

        return AttendanceRecord::where('school_id', $schoolId)
            ->whereBranch($branchId)
            ->where('status', AttendanceRecord::STATUS_ABSENT)
            ->where('date', '>=', $since)
            ->with(['student.academicClass'])
            ->get()
            ->groupBy('student_id')
            ->filter(fn ($group) => $group->count() >= $minAbsences)
            ->map(function ($group) {
                $student = $group->first()->student;
                return [
                    'student' => $student,
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();
    }

    public function classOverview(int $schoolId, string $date, ?int $branchId = null)
    {
        return AcademicClass::where('school_id', $schoolId)
            ->whereBranch($branchId)
            ->with('headTeacher')
            ->get()
            ->map(function (AcademicClass $class) use ($schoolId, $date) {
                $totalStudents = Student::where('school_id', $schoolId)->where('academic_class_id', $class->id)->where('status', 'active')->count();
                $records = AttendanceRecord::where('school_id', $schoolId)->where('academic_class_id', $class->id)->where('date', $date)->get();
                $present = $records->whereIn('status', [AttendanceRecord::STATUS_PRESENT, AttendanceRecord::STATUS_LATE])->count();

                return [
                    'class' => $class,
                    'totalStudents' => $totalStudents,
                    'recorded' => $records->count(),
                    'rate' => $records->count() > 0 ? round($present / $records->count() * 100) : null,
                ];
            });
    }
}
