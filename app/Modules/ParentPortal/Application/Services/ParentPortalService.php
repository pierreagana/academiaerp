<?php

namespace App\Modules\ParentPortal\Application\Services;

use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\Bulletin\Application\Services\BulletinStatsService;
use App\Modules\Bulletin\Domain\Models\BulletinPublication;
use App\Modules\Bulletin\Domain\Repositories\BulletinGradeRepositoryInterface;
use App\Modules\Canteen\Domain\Models\Account as CanteenAccount;
use App\Modules\Canteen\Domain\Models\MenuItem;
use App\Modules\Communication\Domain\Models\Event;
use App\Modules\Finance\Application\Services\StudentFeeService;
use App\Modules\Homework\Domain\Models\HomeworkAssignment;
use App\Modules\Homework\Domain\Models\HomeworkSubmission;
use App\Modules\Presence\Domain\Models\AttendanceRecord;
use App\Modules\SuperAdmin\Domain\Models\School;
use App\Modules\Transport\Domain\Models\RouteStop;

/**
 * Single source of truth for everything a parent is allowed to see — consumed
 * identically by the web portal controllers and the mobile API controllers,
 * so there is exactly one place that decides "does this parent own this
 * child" and exactly one place per module that decides what real data to
 * show. No business logic is duplicated here — every method delegates to the
 * module's own existing service/models and only adds the parent-ownership
 * check + read-only shaping.
 */
class ParentPortalService
{
    public function __construct(
        private BulletinStatsService $bulletinStats,
        private StudentFeeService $feeService,
    ) {}

    /** Every real child (any school) linked to this parent via any of their Guardian rows. */
    public function childrenOf(ParentAccount $parent)
    {
        $guardianIds = $parent->guardianRecords()->pluck('id');

        return Student::whereHas('guardians', fn ($q) => $q->whereIn('guardians.id', $guardianIds))
            ->where('status', 'active')
            ->with('academicClass')
            ->get()
            ->map(function (Student $student) {
                $student->setAttribute('school', School::find($student->school_id));
                return $student;
            });
    }

    /**
     * Everything the dashboard needs in one pass: each child annotated with
     * average/attendance/fee-status, a combined upcoming-work list across all
     * children, and real school announcements (Event rows) relevant to them —
     * no fabricated content, every figure traces back to a real record.
     */
    public function overview(ParentAccount $parent): array
    {
        $children = $this->childrenOf($parent)->map(function (Student $student) {
            $bulletin = $this->bulletin($student);
            $attendance = $this->attendance($student);
            $fees = $this->fees($student);

            $student->setAttribute('average', $bulletin['average']);
            $student->setAttribute('attendanceRate', $attendance['attendanceRate']);
            $student->setAttribute('feeStatus', $fees['status']);

            return $student;
        });

        $upcoming = $children
            ->flatMap(function (Student $student) {
                return collect($this->homework($student)['upcoming'])->map(function ($assignment) use ($student) {
                    $assignment->setAttribute('studentFirstName', $student->first_name);
                    $assignment->setAttribute('studentId', $student->id);
                    return $assignment;
                });
            })
            ->sortBy('scheduled_at')
            ->take(5)
            ->values();

        $schoolIds = $children->pluck('school_id')->unique()->values();
        $classIds = $children->pluck('academic_class_id')->filter()->unique()->values();

        $news = Event::whereIn('school_id', $schoolIds)
            ->where('status', '!=', 'cancelled')
            ->where('start_at', '>=', now()->subDay())
            ->where(function ($q) use ($classIds) {
                $q->whereIn('audience_type', ['all', 'parents_only'])
                    ->orWhere(function ($q2) use ($classIds) {
                        $q2->where('audience_type', 'specific_classes')
                            ->whereHas('academicClasses', fn ($q3) => $q3->whereIn('academic_classes.id', $classIds));
                    });
            })
            ->with('school:id,name')
            ->orderBy('start_at')
            ->limit(3)
            ->get();

        return compact('children', 'upcoming', 'news');
    }

    /** The single authorization gate every module method goes through first. */
    public function ensureChildBelongsToParent(ParentAccount $parent, int $studentId): Student
    {
        $guardianIds = $parent->guardianRecords()->pluck('id');

        $student = Student::whereHas('guardians', fn ($q) => $q->whereIn('guardians.id', $guardianIds))
            ->where('id', $studentId)
            ->first();

        abort_if(!$student, 404, "Cet élève n'est pas rattaché à votre compte.");

        return $student;
    }

    /**
     * Same publish-gated pipeline as BulletinController::print() — a parent only ever
     * sees matières/moyennes that have actually been published (class-level AND
     * subject-level), never a draft.
     */
    public function bulletin(Student $student): array
    {
        $currentSemester = $this->bulletinStats->currentSemester($student->school_id);
        $isPublished = false;
        $grades = collect();
        $average = null;
        $rank = null;
        $classSize = 0;

        if ($currentSemester && $student->academic_class_id) {
            $publication = app(\App\Modules\Bulletin\Domain\Repositories\BulletinPublicationRepositoryInterface::class)
                ->findOrCreate($student->academic_class_id, $currentSemester->id);
            $isPublished = $publication->status === BulletinPublication::STATUS_PUBLISHED;
        }

        if ($currentSemester && $student->academic_class_id && $isPublished) {
            $gradeRepository = app(BulletinGradeRepositoryInterface::class);
            $classGrades = $gradeRepository->forClassAndSemester($student->academic_class_id, $currentSemester->id);
            $classGrades = $this->bulletinStats->mergeHomeworkGrades($classGrades, $student->academic_class_id, $currentSemester->id, null, $student->school_id);
            $classGrades = $this->bulletinStats->filterPublishedSubjects($classGrades, $student->academic_class_id, $currentSemester->id);

            $rawStudentGrades = $classGrades->where('student_id', $student->id);
            $grades = $this->bulletinStats->aggregateToSubjectGrades($rawStudentGrades);
            $average = $this->bulletinStats->studentAverage($grades);

            $ranking = $this->bulletinStats->classRanking($student->academic_class_id, $currentSemester->id, $classGrades);
            $studentRow = collect($ranking)->firstWhere('student.id', $student->id);
            $rank = $studentRow['rank'] ?? null;
            $classSize = count($ranking);
        }

        return compact('currentSemester', 'isPublished', 'grades', 'average', 'rank', 'classSize');
    }

    public function attendance(Student $student, int $days = 60): array
    {
        $since = now()->subDays($days)->toDateString();
        $records = AttendanceRecord::where('student_id', $student->id)
            ->where('date', '>=', $since)
            ->orderByDesc('date')
            ->get();

        $total = $records->count();
        $present = $records->where('status', AttendanceRecord::STATUS_PRESENT)->count();

        return [
            'records' => $records,
            'unjustifiedAbsences' => $records->where('status', AttendanceRecord::STATUS_ABSENT)->where('justified', false)->count(),
            'lateCount' => $records->where('status', AttendanceRecord::STATUS_LATE)->count(),
            'attendanceRate' => $total > 0 ? round($present / $total * 100) : null,
        ];
    }

    public function homework(Student $student): array
    {
        $assignments = collect();

        if ($student->academic_class_id) {
            $assignments = HomeworkAssignment::where('academic_class_id', $student->academic_class_id)
                ->with(['subject', 'teacher'])
                ->orderByDesc('scheduled_at')
                ->get()
                ->map(function (HomeworkAssignment $assignment) use ($student) {
                    $submission = HomeworkSubmission::where('homework_assignment_id', $assignment->id)
                        ->where('student_id', $student->id)
                        ->first();
                    $assignment->setAttribute('submission', $submission);
                    return $assignment;
                });
        }

        return [
            'assignments' => $assignments,
            'upcoming' => $assignments->filter(fn ($a) => $a->scheduled_at?->isFuture())->values(),
            'graded' => $assignments->filter(fn ($a) => $a->submission?->score !== null)->values(),
        ];
    }

    public function fees(Student $student, string $type = 'tuition'): array
    {
        return $this->feeService->summaryFor($student, $type);
    }

    public function canteen(Student $student): array
    {
        $account = CanteenAccount::where('holder_type', Student::class)->where('holder_id', $student->id)->first();

        $weekStart = MenuItem::currentWeekStart();

        $menu = MenuItem::where('school_id', $student->school_id)
            ->where('date', '>=', $weekStart->toDateString())
            ->where('date', '<=', $weekStart->copy()->addDays(4)->toDateString())
            ->orderBy('date')
            ->get();

        return [
            'account' => $account,
            'recentMeals' => $account ? $account->mealRecords()->orderByDesc('date')->limit(15)->get() : collect(),
            'weekMenu' => $menu,
        ];
    }

    /**
     * A student can have up to two stop assignments on their bus route — one
     * for the morning leg (home → school) and one for the evening leg
     * (school → home) — tracked via the `period` column on the pivot.
     */
    public function transport(Student $student): array
    {
        $stops = RouteStop::whereHas('students', fn ($q) => $q->where('students.id', $student->id))
            ->with(['route.bus.driver', 'students' => fn ($q) => $q->where('students.id', $student->id)])
            ->get();

        // A stop can serve as both the morning and evening point for the same
        // student, so it may carry two distinct pivot rows — check all of
        // them rather than assuming the first one matches the period asked for.
        $stopForPeriod = fn (string $period) => $stops->first(
            fn (RouteStop $s) => $s->students->contains(fn ($stu) => $stu->pivot?->period === $period)
        );

        $morningStop = $stopForPeriod('morning');
        $eveningStop = $stopForPeriod('evening');
        $anyStop = $morningStop ?? $eveningStop;

        return [
            'morningStop' => $morningStop,
            'eveningStop' => $eveningStop,
            'route' => $anyStop?->route,
            'bus' => $anyStop?->route?->bus,
        ];
    }
}
