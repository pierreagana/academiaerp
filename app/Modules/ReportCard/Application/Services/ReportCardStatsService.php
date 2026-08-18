<?php

namespace App\Modules\ReportCard\Application\Services;

use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Semester;
use App\Modules\Presence\Domain\Models\AttendanceRecord;
use App\Modules\ReportCard\Domain\Models\ReportCardAssessment;
use Illuminate\Support\Carbon;

class ReportCardStatsService
{
    private const UNJUSTIFIED_ABSENCE_ALERT_THRESHOLD = 3;
    private const NON_ACQUIS_ALERT_THRESHOLD = 3;

    public function currentSemester(int $schoolId): ?Semester
    {
        return Semester::where('school_id', $schoolId)->where('is_current', true)->first()
            ?? Semester::where('school_id', $schoolId)->latest('start_date')->first();
    }

    public function previousSemester(int $schoolId, ?Semester $current): ?Semester
    {
        if (!$current) {
            return null;
        }

        return Semester::where('school_id', $schoolId)
            ->where('start_date', '<', $current->start_date)
            ->orderByDesc('start_date')
            ->first();
    }

    public function acquisitionRate(int $schoolId, ?int $semesterId): ?float
    {
        if (!$semesterId) {
            return null;
        }

        $assessments = ReportCardAssessment::where('semester_id', $semesterId)
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->get();

        if ($assessments->isEmpty()) {
            return null;
        }

        return round($assessments->where('level', 'acquis')->count() / $assessments->count() * 100);
    }

    public function acquisitionGrowth(int $schoolId, ?Semester $current, ?Semester $previous): ?string
    {
        $currentRate = $this->acquisitionRate($schoolId, $current?->id);
        $previousRate = $this->acquisitionRate($schoolId, $previous?->id);

        if ($currentRate === null || $previousRate === null) {
            return null;
        }

        $diff = $currentRate - $previousRate;
        $sign = $diff >= 0 ? '+' : '';

        return $sign . round($diff) . 'pts';
    }

    public function attendanceRate30Days(int $schoolId): ?float
    {
        $since = Carbon::now()->subDays(30)->toDateString();

        $records = AttendanceRecord::where('school_id', $schoolId)->where('date', '>=', $since)->get();

        if ($records->isEmpty()) {
            return null;
        }

        $present = $records->whereIn('status', [AttendanceRecord::STATUS_PRESENT, AttendanceRecord::STATUS_LATE])->count();

        return round($present / $records->count() * 100);
    }

    public function masteryBreakdown(int $schoolId, ?int $semesterId): array
    {
        if (!$semesterId) {
            return ['acquis' => 0, 'en_cours' => 0, 'non_acquis' => 0];
        }

        $assessments = ReportCardAssessment::where('semester_id', $semesterId)
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->get();

        $total = $assessments->count();
        if ($total === 0) {
            return ['acquis' => 0, 'en_cours' => 0, 'non_acquis' => 0];
        }

        return [
            'acquis' => round($assessments->where('level', 'acquis')->count() / $total * 100),
            'en_cours' => round($assessments->where('level', 'en_cours')->count() / $total * 100),
            'non_acquis' => round($assessments->where('level', 'non_acquis')->count() / $total * 100),
        ];
    }

    public function domainsAtRisk(int $schoolId, ?int $semesterId, int $limit = 3): array
    {
        if (!$semesterId) {
            return [];
        }

        $assessments = ReportCardAssessment::where('semester_id', $semesterId)
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->with('competency.subdomain.domain')
            ->get()
            ->filter(fn ($a) => $a->competency?->subdomain?->domain);

        $byDomain = $assessments->groupBy(fn ($a) => $a->competency->subdomain->domain->name);

        return $byDomain->map(function ($group, $name) {
            $total = $group->count();
            return [
                'name' => $name,
                'rate' => $total > 0 ? round($group->where('level', 'acquis')->count() / $total * 100) : 0,
                'evaluated' => $total,
            ];
        })->sortBy('rate')->take($limit)->values()->all();
    }

    public function classesActive(int $schoolId, ?int $semesterId, ?int $branchId = null): array
    {
        $classes = AcademicClass::where('school_id', $schoolId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with(['students', 'headTeacher'])
            ->get();

        return $classes->map(function ($class) use ($semesterId) {
            $studentIds = $class->students->pluck('id');
            $rate = null;

            if ($semesterId && $studentIds->isNotEmpty()) {
                $assessments = ReportCardAssessment::where('semester_id', $semesterId)
                    ->whereIn('student_id', $studentIds)
                    ->get();

                if ($assessments->isNotEmpty()) {
                    $rate = round($assessments->where('level', 'acquis')->count() / $assessments->count() * 100);
                }
            }

            return [
                'id' => $class->id,
                'name' => $class->name,
                'teacher' => $class->headTeacher ? trim($class->headTeacher->first_name . ' ' . $class->headTeacher->last_name) : null,
                'student_count' => $studentIds->count(),
                'acquisition_rate' => $rate,
            ];
        })->all();
    }

    public function alerts(int $schoolId, ?int $semesterId, ?int $branchId = null): array
    {
        $since = Carbon::now()->subDays(30)->toDateString();

        $unjustifiedAbsenceCounts = AttendanceRecord::where('school_id', $schoolId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', AttendanceRecord::STATUS_ABSENT)
            ->where('date', '>=', $since)
            ->where(fn ($q) => $q->whereNull('justified')->orWhere('justified', false))
            ->selectRaw('student_id, academic_class_id, count(*) as absences')
            ->groupBy('student_id', 'academic_class_id')
            ->having('absences', '>', self::UNJUSTIFIED_ABSENCE_ALERT_THRESHOLD)
            ->with('academicClass')
            ->get();

        $flaggedClasses = $unjustifiedAbsenceCounts->groupBy('academic_class_id')->map(function ($group) {
            $class = $group->first()->academicClass;
            return [
                'class' => $class?->name ?? '—',
                'reason' => $group->count() . ' élève(s) avec plus de ' . self::UNJUSTIFIED_ABSENCE_ALERT_THRESHOLD . ' absences non justifiées (30 derniers jours)',
            ];
        });

        $nonAcquisFlags = collect();
        if ($semesterId) {
            $nonAcquisFlags = ReportCardAssessment::where('semester_id', $semesterId)
                ->where('level', 'non_acquis')
                ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
                ->selectRaw('student_id, count(*) as non_acquis_count')
                ->groupBy('student_id')
                ->having('non_acquis_count', '>=', self::NON_ACQUIS_ALERT_THRESHOLD)
                ->with('student.academicClass')
                ->get();

            foreach ($nonAcquisFlags as $flag) {
                $class = $flag->student?->academicClass;
                if ($class) {
                    $flaggedClasses->put($class->id, [
                        'class' => $class->name,
                        'reason' => ($flaggedClasses[$class->id]['reason'] ?? null)
                            ? $flaggedClasses[$class->id]['reason'] . ' ; compétences non acquises multiples'
                            : 'Compétences non acquises multiples ce trimestre',
                    ]);
                }
            }
        }

        return [
            'count' => $unjustifiedAbsenceCounts->count() + $nonAcquisFlags->count(),
            'classes' => $flaggedClasses->values()->all(),
        ];
    }
}
