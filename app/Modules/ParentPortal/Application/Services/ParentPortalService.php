<?php

namespace App\Modules\ParentPortal\Application\Services;

use App\Modules\Academic\Domain\Models\Award;
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
use App\Modules\Presence\Domain\Models\AccessLog;
use App\Modules\Presence\Domain\Models\AccessPoint;
use App\Modules\SuperAdmin\Domain\Models\School;
use App\Modules\Transport\Domain\Models\RouteStop;
use App\Modules\Transport\Domain\Models\StopArrival;

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
            ->unique('id')
            ->values()
            ->map(function (Student $student) {
                $student->setAttribute('school', School::find($student->school_id));
                return $student;
            });
    }

    /**
     * Everything the dashboard needs in one pass: each child annotated with
     * average/attendance/fee-status, a combined upcoming-work list across all
     * children, real school announcements, real grade trend, and real RFID
     * badge status — no fabricated content, every figure traces back to a real
     * record. Per-child data collection is wrapped in try/catch so one module
     * failure does not prevent the whole dashboard from loading.
     */
    public function overview(ParentAccount $parent): array
    {
        $children = $this->childrenOf($parent)->map(function (Student $student) {
            try { $bulletin = $this->bulletin($student); } catch (\Throwable) { $bulletin = ['average' => null, 'currentSemester' => null]; }
            try { $attendance = $this->attendance($student); } catch (\Throwable) { $attendance = ['attendanceRate' => null, 'unjustifiedAbsences' => 0, 'lateCount' => 0]; }
            try { $fees = $this->fees($student); } catch (\Throwable) { $fees = ['status' => 'unconfigured']; }

            $student->setAttribute('average', $bulletin['average']);
            $student->setAttribute('attendanceRate', $attendance['attendanceRate']);
            $student->setAttribute('unjustifiedAbsences', $attendance['unjustifiedAbsences'] ?? 0);
            $student->setAttribute('lateCount', $attendance['lateCount'] ?? 0);
            $student->setAttribute('feeStatus', $fees['status']);
            $student->setAttribute('latestAward', $this->latestAward($student));

            // ── Real grade trend: compare current semester vs previous ──────
            $averageTrend = null;
            try {
                $currentSemester = $bulletin['currentSemester'] ?? null;
                if ($currentSemester && $bulletin['average'] !== null) {
                    // Find the semester before the current one for the same school
                    $previousSemester = \App\Modules\Bulletin\Domain\Models\Semester::where('school_id', $student->school_id)
                        ->where('end_date', '<', $currentSemester->start_date)
                        ->orderByDesc('end_date')
                        ->first();

                    if ($previousSemester) {
                        $gradeRepo = app(\App\Modules\Bulletin\Domain\Repositories\BulletinGradeRepositoryInterface::class);
                        $prevGrades = $gradeRepo->forClassAndSemester($student->academic_class_id, $previousSemester->id);
                        $prevStudentGrades = $prevGrades->where('student_id', $student->id);
                        $prevSubjectGrades = $this->bulletinStats->aggregateToSubjectGrades($prevStudentGrades);
                        $prevAverage = $this->bulletinStats->studentAverage($prevSubjectGrades);

                        if ($prevAverage !== null) {
                            $averageTrend = round($bulletin['average'] - $prevAverage, 1);
                        }
                    }
                }
            } catch (\Throwable) {
                $averageTrend = null;
            }
            $student->setAttribute('averageTrend', $averageTrend);

            // ── Real RFID badge status: last AccessLog entry for this student ─
            $accessStatus = null;
            $lastAccessAt = null;
            try {
                $lastLog = AccessLog::where('holder_type', 'App\Modules\Academic\Domain\Models\Student')
                    ->where('holder_id', $student->id)
                    ->orderByDesc('occurred_at')
                    ->first();
                if ($lastLog) {
                    $accessStatus = $lastLog->action === 'entry' ? 'in_school' : 'out_of_school';
                    $lastAccessAt = $lastLog->occurred_at;
                }
            } catch (\Throwable) {
                // AccessLog table may not exist on older installs
            }
            $student->setAttribute('accessStatus', $accessStatus);
            $student->setAttribute('lastAccessAt', $lastAccessAt);

            return $student;
        });

        $upcoming = $children
            ->flatMap(function (Student $student) {
                try {
                    return collect($this->homework($student)['upcoming'])->map(function ($assignment) use ($student) {
                        $assignment->setAttribute('studentFirstName', $student->first_name);
                        $assignment->setAttribute('studentLastName', $student->last_name);
                        $assignment->setAttribute('studentId', $student->id);
                        $assignment->setAttribute('studentPhotoPath', $student->photo_path);
                        $assignment->setAttribute('studentClassName', $student->academicClass?->name ?? '');
                        return $assignment;
                    });
                } catch (\Throwable) {
                    return collect();
                }
            })
            ->sortBy('scheduled_at')
            ->take(5)
            ->values();

        $schoolIds = $children->pluck('school_id')->unique()->values();
        $classIds  = $children->pluck('academic_class_id')->filter()->unique()->values();

        $news = collect();
        try {
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
                ->limit(5)
                ->get();
        } catch (\Throwable) {}

        // ── Real unread notification count ──────────────────────────────────
        $unreadNotificationsCount = 0;
        try {
            $unreadNotificationsCount = $parent->notificationLogs()->whereNull('read_at')->count();
        } catch (\Throwable) {}

        return compact('children', 'upcoming', 'news', 'unreadNotificationsCount');
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
     * Same template/QR the school configures in the Cards module and the mobile
     * app already reads (MobileParentController) — looked up by the student's
     * own school_id rather than CardTemplateRepositoryInterface::findOrDefault(),
     * which reads auth()->user()->school_id (the staff guard, null here under
     * the parent guard).
     */
    public function studentCard(Student $child): array
    {
        $school = School::find($child->school_id);

        $template = \App\Modules\Cards\Domain\Models\CardTemplate::where('school_id', $child->school_id)
            ->where('card_type', 'student')
            ->first();

        if (!$template) {
            $template = \App\Modules\Cards\Domain\Models\CardTemplate::create(array_merge(
                \App\Modules\Cards\Domain\Models\CardTemplate::defaultsFor('student'),
                ['school_id' => $child->school_id, 'card_type' => 'student']
            ));
        }

        $data = [
            'full_name' => trim("{$child->first_name} {$child->last_name}"),
            'student_id' => $child->roll_number,
            'class' => $child->academicClass?->name ?? '-',
            'blood_group' => $child->blood_group ?? '-',
            'academic_year' => $child->academic_year ?? '-',
            'date_of_birth' => $child->dob ? \Illuminate\Support\Carbon::parse($child->dob)->format('d M Y') : '-',
            'emergency_contact' => optional($child->guardians->first())->phone ?? '-',
        ];

        $card = [
            'name' => $data['full_name'],
            'data' => $data,
            'photo' => $child->photo_path ? asset('storage/' . $child->photo_path) : null,
            // Same "{school->code}:{matricule}" format CardController::printCards
            // stamps on physical cards and RecordAccessCheckInUseCase parses on scan.
            'qr' => trim(($school?->code ?? '') . ':' . $child->roll_number, ':'),
        ];

        return compact('template', 'school', 'card');
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
        $classGrades = collect();
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

        return compact('currentSemester', 'isPublished', 'grades', 'classGrades', 'average', 'rank', 'classSize');
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

    public function diplomas(Student $student): array
    {
        $awards = Award::where('recipient_type', 'student')->where('recipient_id', $student->id)
            ->with('type')->orderByDesc('awarded_date')->get();

        return ['awards' => $awards];
    }

    private function latestAward(Student $student): ?Award
    {
        return Award::where('recipient_type', 'student')->where('recipient_id', $student->id)
            ->with('type')->orderByDesc('awarded_date')->first();
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

        $upcoming = $assignments->filter(function ($a) {
            if (!$a->scheduled_at) return true;
            return $a->scheduled_at->isFuture() || $a->scheduled_at->isToday() || $a->scheduled_at >= now()->subDays(7);
        })->values();

        // If no strictly upcoming, show the most recent assignments so the parent always sees active homework
        if ($upcoming->isEmpty() && $assignments->isNotEmpty()) {
            $upcoming = $assignments->take(5);
        }

        return [
            'assignments' => $assignments,
            'upcoming' => $upcoming,
            'graded' => $assignments->filter(fn ($a) => $a->submission?->score !== null)->values(),
        ];
    }

    public function fees(Student $student, string $type = 'tuition'): array
    {
        $zone = $type === 'transport' ? $this->transport($student)['route']?->zone : null;

        return $this->feeService->summaryFor($student, $type, $zone);
    }

    public function canteen(Student $student): array
    {
        $account = CanteenAccount::where('holder_type', 'student')->where('holder_id', $student->id)->first();

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

    /**
     * Complete financial overview for the parent: children tuition status,
     * family wallet balance, next upcoming installments, AI payment insight,
     * and complete transaction history.
     */
    public function finance(ParentAccount $parent): array
    {
        $children = $this->childrenOf($parent)->map(function (Student $student) {
            $fees = $this->fees($student);
            $schedule = collect($fees['schedule'] ?? []);
            $nextTranche = $schedule->firstWhere('status', 'due') ?? $schedule->firstWhere('status', 'upcoming');

            $student->setAttribute('feeSummary', $fees);
            $student->setAttribute('feeTotal', $fees['total'] ?? 0);
            $student->setAttribute('feePaid', $fees['paid'] ?? 0);
            $student->setAttribute('feeRemaining', $fees['remaining'] ?? 0);
            $student->setAttribute('feeStatus', $fees['status'] ?? 'unconfigured');
            $student->setAttribute('nextTranche', $nextTranche);

            return $student;
        });

        $academicYear = $children->first()
            ? $this->bulletinStats->currentSemester($children->first()->school_id)?->academic_year
            : null;

        // Resolve or create the parent's wallet
        $wallet = \App\Modules\Finance\Domain\Models\Wallet::firstOrCreate(
            ['owner_type' => ParentAccount::class, 'owner_id' => $parent->id],
            ['balance' => 0.00, 'currency' => 'XOF']
        );

        $studentIds = $children->pluck('id');
        $realPayments = \App\Modules\Finance\Domain\Models\Payment::whereIn('student_id', $studentIds)
            ->with('student')
            ->orderByDesc('created_at')
            ->get();

        $walletTx = $wallet->transactions()->orderByDesc('created_at')->get();

        // Build unified transaction list
        $transactions = collect();

        foreach ($walletTx as $tx) {
            $transactions->push([
                'id' => 'tx-' . $tx->id,
                'date' => $tx->created_at ?? now(),
                'description' => $tx->description ?? ($tx->type === 'recharge' ? 'Recharge Portefeuille via ' . ($tx->gateway_slug ? ucfirst($tx->gateway_slug) : 'Mobile Money') : 'Paiement Service'),
                'type' => $tx->type === 'recharge' ? 'RECHARGE' : 'PAIEMENT',
                'type_badge' => $tx->type === 'recharge' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700',
                'amount' => (float) $tx->amount,
                'is_positive' => $tx->type === 'recharge',
                'receipt_url' => null,
            ]);
        }

        foreach ($realPayments as $p) {
            $transactions->push([
                'id' => 'pay-' . $p->id,
                'date' => $p->created_at ?? now(),
                'description' => 'Frais Scolarité (' . ($p->student?->first_name ?? 'Élève') . ')' . ($p->notes ? ' - ' . $p->notes : ''),
                'type' => 'PAIEMENT',
                'type_badge' => 'bg-blue-50 text-blue-700',
                'amount' => (float) $p->amount,
                'is_positive' => false,
                'receipt_url' => null,
            ]);
        }

        $transactions = $transactions->sortByDesc('date')->values();
        $recentTransactions = $transactions->take(3);

        // Compute AI insight: check if an upcoming tranche is approaching
        $nextDueKid = $children->first(fn ($c) => $c->nextTranche !== null);
        $aiInsight = null;
        if ($nextDueKid && $nextDueKid->nextTranche) {
            $trancheLabel = $nextDueKid->nextTranche['label'];
            $trancheAmount = $nextDueKid->nextTranche['amount'];
            $trancheDate = $nextDueKid->nextTranche['due_date']->translatedFormat('d F');
            $kidClass = $nextDueKid->academicClass?->name ?? 'Classe';

            $covers = $wallet->balance >= $trancheAmount;
            $aiInsight = [
                'kidName' => $nextDueKid->first_name,
                'className' => $kidClass,
                'trancheLabel' => $trancheLabel,
                'dueDate' => $trancheDate,
                'amount' => $trancheAmount,
                'walletBalance' => (float) $wallet->balance,
                'covers' => $covers,
                'studentId' => $nextDueKid->id,
            ];
        }

        return compact('children', 'wallet', 'transactions', 'recentTransactions', 'aiInsight', 'academicYear');
    }

    /**
     * Complete academic hub data for parent view matching the Espace Académique dashboard.
     */
    public function academic(ParentAccount $parent, ?int $studentId = null): array
    {
        $children = $this->childrenOf($parent);
        
        $selectedChild = $studentId
            ? $children->firstWhere('id', $studentId)
            : $children->first();

        if (!$selectedChild && $children->isNotEmpty()) {
            $selectedChild = $children->first();
        }

        if (!$selectedChild) {
            return [
                'children' => collect(),
                'selectedChild' => null,
                'kpis' => [
                    'average' => null,
                    'averageTrend' => null,
                    'rank' => null,
                    'classSize' => 0,
                    'attendanceRate' => null,
                    'justifiedAbsences' => 0,
                ],
                'currentSemesterName' => null,
                'subjectProgress' => collect(),
                'bulletins' => collect(),
                'competencies' => collect(),
                'teacherResources' => collect(),
                'aiAcademicInsight' => null,
            ];
        }

        $bulletinData = $this->bulletin($selectedChild);
        $attendanceData = $this->attendance($selectedChild);
        $currentSemester = $bulletinData['currentSemester'];

        $average = $bulletinData['average'];
        $rank = $bulletinData['rank'];
        $classSize = $bulletinData['classSize'];
        $attendanceRate = $attendanceData['attendanceRate'];
        $justifiedAbsences = AttendanceRecord::where('student_id', $selectedChild->id)
            ->where('status', AttendanceRecord::STATUS_ABSENT)
            ->where('justified', true)
            ->count();

        // Real trend: this semester's average against the previous one, not a fixed figure.
        $averageTrend = null;
        if ($currentSemester && $average !== null) {
            $previousSemester = $this->bulletinStats->previousSemester($selectedChild->school_id, $currentSemester);
            $previousAverage = $previousSemester ? $this->bulletinStats->studentAverageForSemester($selectedChild, $previousSemester) : null;
            if ($previousAverage !== null) {
                $delta = round($average - $previousAverage, 1);
                $averageTrend = ($delta >= 0 ? '+' : '') . number_format($delta, 1) . ' pts';
            }
        }

        // Subject scores for the chart, each against its real class average for the same semester.
        $grades = $bulletinData['grades'];
        $classGrades = $bulletinData['classGrades'];
        $subjectProgress = collect();

        foreach ($grades as $g) {
            $classAvg = $this->bulletinStats->subjectClassAverage($classGrades, $g->subject_id);
            $subjectProgress->push([
                'subject' => $g->subject?->name ?? 'Matière',
                'score' => (float) $g->score,
                'classAverage' => $classAvg,
                'aboveClassAvg' => $classAvg !== null ? $g->score >= $classAvg : null,
            ]);
        }

        // Bulletins list: every already-published bulletin for this school year, oldest first.
        $bulletins = collect();
        if ($currentSemester && $selectedChild->academic_class_id) {
            $publicationRepo = app(\App\Modules\Bulletin\Domain\Repositories\BulletinPublicationRepositoryInterface::class);
            $semesters = $this->bulletinStats->academicYearSemesters($selectedChild->school_id, $currentSemester);

            foreach ($semesters as $semester) {
                $publication = $publicationRepo->findOrCreate($selectedChild->academic_class_id, $semester->id);
                if ($publication->status === BulletinPublication::STATUS_PUBLISHED) {
                    $bulletins->push([
                        'title' => 'Bulletin - ' . $semester->name,
                        'period' => $publication->published_at
                            ? 'Publié le ' . $publication->published_at->translatedFormat('d F Y')
                            : 'Publié',
                        'url' => route('parent.bulletin', $selectedChild->id),
                    ]);
                }
            }
        }

        // Key competencies tracking — real per-student assessments from the ReportCard module, if the school uses it.
        $levelMeta = [
            \App\Modules\ReportCard\Domain\Models\ReportCardAssessment::LEVEL_ACQUIS => ['label' => 'Acquis', 'percentage' => 100, 'color' => 'bg-emerald-500'],
            \App\Modules\ReportCard\Domain\Models\ReportCardAssessment::LEVEL_EN_COURS => ['label' => 'En cours', 'percentage' => 60, 'color' => 'bg-blue-600'],
            \App\Modules\ReportCard\Domain\Models\ReportCardAssessment::LEVEL_NON_ACQUIS => ['label' => 'Non Acquis', 'percentage' => 20, 'color' => 'bg-rose-500'],
        ];
        $competencies = collect();
        if ($currentSemester) {
            $competencies = \App\Modules\ReportCard\Domain\Models\ReportCardAssessment::where('student_id', $selectedChild->id)
                ->where('semester_id', $currentSemester->id)
                ->with('competency')
                ->get()
                ->filter(fn ($a) => $a->competency !== null)
                ->map(fn ($a) => [
                    'domain' => $a->competency->statement,
                    'level' => $levelMeta[$a->level]['label'] ?? $a->level,
                    'percentage' => $levelMeta[$a->level]['percentage'] ?? 50,
                    'color' => $levelMeta[$a->level]['color'] ?? 'bg-slate-400',
                ])
                ->values();
        }

        // Teacher resources: no real content-sharing model exists yet for teachers to post
        // materials to parents, so this stays honestly empty rather than inventing entries.
        $teacherResources = collect();

        // "IA Insight": rule-based text over real figures only, never a template with invented names.
        $aiAcademicInsight = null;
        $withClassAvg = $subjectProgress->filter(fn ($s) => $s['classAverage'] !== null);
        if ($withClassAvg->count() >= 2) {
            $best = $withClassAvg->sortByDesc(fn ($s) => $s['score'] - $s['classAverage'])->first();
            $worst = $withClassAvg->sortBy(fn ($s) => $s['score'] - $s['classAverage'])->first();
            if ($best['subject'] !== $worst['subject'] && $best['score'] > $best['classAverage'] && $worst['score'] < $worst['classAverage']) {
                $aiAcademicInsight = "{$selectedChild->first_name} montre une bonne progression en **{$best['subject']}**. Un léger soutien en **{$worst['subject']}** pourrait l'aider à progresser davantage.";
            }
        }
        if (!$aiAcademicInsight) {
            $aiAcademicInsight = $subjectProgress->isNotEmpty()
                ? "Pas encore assez de données comparatives pour dégager une tendance par matière ce trimestre."
                : "Aucune note publiée ce trimestre pour établir une analyse.";
        }

        $kpis = [
            'average' => $average,
            'averageTrend' => $averageTrend,
            'rank' => $rank,
            'classSize' => $classSize,
            'attendanceRate' => $attendanceRate,
            'justifiedAbsences' => $justifiedAbsences,
        ];
        $currentSemesterName = $currentSemester?->name;

        return compact(
            'children',
            'selectedChild',
            'kpis',
            'currentSemesterName',
            'subjectProgress',
            'bulletins',
            'competencies',
            'teacherResources',
            'aiAcademicInsight'
        );
    }

    /**
     * Services de Vie Scolaire hub data: canteen menus, health & infirmary interventions,
     * and live GPS transport tracking.
     */
    public function services(ParentAccount $parent, ?int $studentId = null): array
    {
        $children = $this->childrenOf($parent);
        $selectedChild = $studentId
            ? $children->firstWhere('id', $studentId)
            : $children->first();

        if (!$selectedChild && $children->isNotEmpty()) {
            $selectedChild = $children->first();
        }

        if (!$selectedChild) {
            return [
                'children' => collect(),
                'selectedChild' => null,
                'canteenDays' => collect(),
                'healthRecords' => null,
                'transportGPS' => null,
            ];
        }

        // Real weekly canteen menu, one card per day (Mon-Wed) of the current planning week.
        $weekStart = MenuItem::currentWeekStart();
        $weekMenu = MenuItem::where('school_id', $selectedChild->school_id)
            ->where('date', '>=', $weekStart->toDateString())
            ->where('date', '<=', $weekStart->copy()->addDays(4)->toDateString())
            ->orderBy('date')
            ->get();

        $canteenDays = collect(range(0, 2))->map(function (int $i) use ($weekStart, $weekMenu) {
            $date = $weekStart->copy()->addDays($i);
            $itemsForDay = $weekMenu->filter(fn ($m) => $m->date->isSameDay($date));

            return [
                'day_label' => $date->isToday() ? "Aujourd'hui" : $date->translatedFormat('l d M'),
                'title' => $itemsForDay->isNotEmpty() ? $itemsForDay->pluck('title')->implode(' + ') : null,
                'is_today' => $date->isToday(),
            ];
        });

        // Health teaser: most recent real infirmary visit + a genuinely upcoming vaccine, if any.
        $lastIntervention = \App\Modules\Infirmary\Domain\Models\Intervention::where('student_id', $selectedChild->id)
            ->with('createdBy')
            ->orderByDesc('arrival_time')
            ->first();
        $upcomingVaccine = \App\Modules\Infirmary\Domain\Models\Vaccine::where('student_id', $selectedChild->id)
            ->whereNotNull('next_due_at')
            ->where('next_due_at', '>=', now())
            ->orderBy('next_due_at')
            ->first();

        $healthRecords = [
            'recentIntervention' => $lastIntervention ? [
                'treatment' => $lastIntervention->motive,
                'reason' => $lastIntervention->arrival_time->translatedFormat('d M Y à H:i'),
                'staff' => $lastIntervention->createdBy?->name ?? 'Infirmerie',
            ] : null,
            'vaccineAlert' => $upcomingVaccine ? [
                'title' => 'Rappel de vaccination',
                'message' => "Le vaccin {$upcomingVaccine->name} est prévu le " . $upcomingVaccine->next_due_at->translatedFormat('d F Y') . '.',
            ] : null,
        ];

        // Live GPS transport tracking, anchored to the bus actually assigned to this student's route.
        $transportData = $this->transport($selectedChild);
        $bus = $transportData['bus'];
        $route = $transportData['route'];
        $transportGPS = null;

        if ($bus && $route) {
            $isLive = $bus->active_route_id === $route->id && $bus->active_shift && $bus->current_latitude !== null && $bus->current_longitude !== null;
            $period = $bus->active_shift ?? 'morning';
            $today = now()->startOfDay();

            $nextStop = null;
            if ($isLive) {
                $arrivedStopIds = StopArrival::where('route_id', $route->id)
                    ->where('period', $period)
                    ->whereDate('arrived_at', $today)
                    ->pluck('route_stop_id');
                $nextStop = $route->stops->reject(fn ($s) => $arrivedStopIds->contains($s->id))->sortBy('sequence')->first();
            }

            $history = StopArrival::where('route_id', $route->id)
                ->whereDate('arrived_at', $today)
                ->with('routeStop')
                ->orderByDesc('arrived_at')
                ->limit(5)
                ->get()
                ->map(fn ($a) => [
                    'event' => 'Arrivé à ' . ($a->routeStop?->name ?? 'un arrêt'),
                    'time' => $a->arrived_at->translatedFormat('d M, H:i'),
                    'icon' => 'location_on',
                ])
                ->values();

            $transportGPS = [
                'busNumber' => $bus->bus_number,
                'line' => $route->name,
                'isLive' => $isLive,
                'status' => $isLive ? 'En route' : 'Hors service',
                'nextStopName' => $nextStop?->name,
                'latitude' => $isLive ? (float) $bus->current_latitude : null,
                'longitude' => $isLive ? (float) $bus->current_longitude : null,
                'positionUpdatedAt' => $bus->position_updated_at,
                'history' => $history,
            ];
        }

        return compact('children', 'selectedChild', 'canteenDays', 'healthRecords', 'transportGPS');
    }

    /**
     * Settings and Profile management data for the parent.
     */
    public function settings(ParentAccount $parent): array
    {
        $children = $this->childrenOf($parent);

        // Parse full name into first and last name if possible
        $nameParts = explode(' ', trim($parent->name ?? ''), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        $schoolIds = $children->pluck('school_id')->unique()->values();
        $signedDocIds = \App\Modules\LegalDocuments\Domain\Models\LegalDocumentSignature::where('parent_id', $parent->id)
            ->pluck('signed_at', 'legal_document_id');

        $legalDocuments = \App\Modules\LegalDocuments\Domain\Models\LegalDocument::whereIn('school_id', $schoolIds)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($doc) => [
                'id' => $doc->id,
                'title' => $doc->title,
                'signed' => $signedDocIds->has($doc->id),
                'signed_date' => $signedDocIds->has($doc->id) ? 'Signé le ' . $signedDocIds->get($doc->id)->translatedFormat('d/m/Y') : null,
                'icon' => $signedDocIds->has($doc->id) ? 'verified_user' : 'description',
                'url' => \Illuminate\Support\Facades\Storage::disk('public')->url($doc->file_path),
            ]);

        return compact('parent', 'children', 'firstName', 'lastName', 'legalDocuments');
    }

    /** Idempotent — signing an already-signed document just returns the existing signature. */
    public function signLegalDocument(ParentAccount $parent, int $legalDocumentId): void
    {
        \App\Modules\LegalDocuments\Domain\Models\LegalDocumentSignature::firstOrCreate(
            ['legal_document_id' => $legalDocumentId, 'parent_id' => $parent->id],
            ['signed_at' => now()]
        );
    }

    /**
     * Health and Infirmary data for the parent portal.
     */
    public function infirmary(ParentAccount $parent, ?int $studentId = null): array
    {
        $children = $this->childrenOf($parent);
        $selectedChild = $studentId
            ? $children->firstWhere('id', $studentId)
            : $children->first();

        if (!$selectedChild && $children->isNotEmpty()) {
            $selectedChild = $children->first();
        }

        if (!$selectedChild) {
            return [
                'children' => collect(),
                'selectedChild' => null,
                'interventions' => collect(),
                'allergies' => collect(),
                'documents' => collect(),
                'activeInterventionsCount' => 0,
                'medicationsAdministeredCount' => 0,
                'aiHealthOverview' => '',
                'infirmaryPhone' => null,
            ];
        }

        $interventions = \App\Modules\Infirmary\Domain\Models\Intervention::where('student_id', $selectedChild->id)
            ->with('createdBy')
            ->orderByDesc('arrival_time')
            ->get();

        $allergies = \App\Modules\Infirmary\Domain\Models\Allergy::where('student_id', $selectedChild->id)->get();

        $vaccines = \App\Modules\Infirmary\Domain\Models\Vaccine::where('student_id', $selectedChild->id)
            ->orderByDesc('administered_at')
            ->get();
        $prescriptions = \App\Modules\Infirmary\Domain\Models\PrescriptionDocument::where('student_id', $selectedChild->id)
            ->orderByDesc('created_at')
            ->get();

        $documents = $vaccines->map(fn ($v) => [
            'title' => 'Vaccin : ' . $v->name,
            'date_info' => $v->administered_at
                ? 'Administré le ' . $v->administered_at->translatedFormat('d/m/Y')
                : ($v->next_due_at ? 'Prévu le ' . $v->next_due_at->translatedFormat('d/m/Y') : ''),
            'icon' => 'medication',
            'url' => '#',
        ])->concat($prescriptions->map(fn ($p) => [
            'title' => $p->name,
            'date_info' => 'Ajouté le ' . $p->created_at->translatedFormat('d/m/Y'),
            'icon' => 'description',
            'url' => \Illuminate\Support\Facades\Storage::disk('public')->url($p->file_path),
        ]))->values();

        $activeInterventionsCount = $interventions->filter(fn ($i) => $i->arrival_time?->isToday())->count();
        $medicationsAdministeredCount = $interventions->filter(fn ($i) => $i->arrival_time?->isCurrentMonth())->count();

        $lastIntervention = $interventions->first();
        $aiHealthOverview = $lastIntervention
            ? "{$selectedChild->first_name} a été vu(e) à l'infirmerie le " . $lastIntervention->arrival_time->translatedFormat('d F') . " pour : **{$lastIntervention->motive}**."
                . ($allergies->isNotEmpty() ? ' Allergie(s) déclarée(s) : **' . $allergies->pluck('name')->implode(', ') . '**.' : '')
            : ($allergies->isNotEmpty()
                ? "Aucune intervention enregistrée. Allergie(s) déclarée(s) : **" . $allergies->pluck('name')->implode(', ') . '**.'
                : "Aucune intervention ni allergie enregistrée pour {$selectedChild->first_name}.");

        $infirmaryPhone = School::find($selectedChild->school_id)?->contact_phone;

        return compact(
            'children',
            'selectedChild',
            'interventions',
            'allergies',
            'documents',
            'activeInterventionsCount',
            'medicationsAdministeredCount',
            'aiHealthOverview',
            'infirmaryPhone'
        );
    }

    /**
     * School access / badge RFID entry-exit logs for a child.
     */
    public function schoolAccess(ParentAccount $parent, ?int $studentId = null): array
    {
        $children = $this->childrenOf($parent);
        $selectedChild = $studentId
            ? $children->firstWhere('id', $studentId) ?? $children->first()
            : $children->first();

        if (! $selectedChild) {
            return compact('children') + [
                'selectedChild' => null,
                'accessLogs' => collect(),
                'currentStatus' => null,
                'lastScan' => null,
                'weeklyStats' => ['present' => 0, 'absent' => 0, 'late' => 0, 'total' => 0],
                'accessPoints' => collect(),
                'aiSecurityInsight' => null,
                'monthlyHeatmap' => [],
            ];
        }

        // ── Fetch real access logs ─────────────────────────────────────────
        $accessLogs = AccessLog::where('holder_type', 'App\Modules\Academic\Domain\Models\Student')
            ->where('holder_id', $selectedChild->id)
            ->with('accessPoint')
            ->orderByDesc('occurred_at')
            ->take(60)
            ->get();

        // ── Current status ────────────────────────────────────────────────
        $lastScan = $accessLogs->first();
        $currentStatus = $lastScan
            ? ($lastScan->action === 'entry' ? 'in_school' : 'out_of_school')
            : null;

        // ── Weekly stats (Mon–Fri of current week) ────────────────────────
        $weekStart = now()->startOfWeek();
        $weekEnd   = now()->endOfWeek();
        $weeklyLogs = $accessLogs->filter(fn($l) => $l->occurred_at >= $weekStart && $l->occurred_at <= $weekEnd);
        $daysWithEntry = $weeklyLogs->where('action', 'entry')->map(fn($l) => $l->occurred_at->toDateString())->unique()->count();
        $lateDates = $weeklyLogs->where('action', 'entry')->filter(fn($l) => $l->occurred_at->hour >= 8 && $l->occurred_at->minute > 10)->map(fn($l) => $l->occurred_at->toDateString())->unique()->sort()->values();
        $daysLate = $lateDates->count();
        $schoolDaysThisWeek = min(now()->dayOfWeek ?: 5, 5); // Mon=1 … Fri=5, clamp at 5
        $weeklyStats = [
            'present' => $daysWithEntry,
            'absent'  => max(0, $schoolDaysThisWeek - $daysWithEntry),
            'late'    => $daysLate,
            'total'   => $schoolDaysThisWeek,
        ];

        // ── Access points list for filter ─────────────────────────────────
        $accessPoints = $accessLogs->map(fn($l) => $l->accessPoint?->name ?? '—')->unique()->values();

        // ── Rule-based summary over real weekly figures ────────────────────
        $lateDayLabels = $lateDates->map(fn ($d) => \Carbon\Carbon::parse($d)->translatedFormat('l'))->implode(', ');
        $aiSecurityInsight = "{$selectedChild->first_name} est arrivé à l'heure **{$daysWithEntry} jour(s) sur {$schoolDaysThisWeek}** cette semaine. "
            . ($daysLate > 0 ? "**{$daysLate} retard(s)** enregistré(s) ({$lateDayLabels})." : "Aucun retard cette semaine, excellent !")
            . " Dernière sortie détectée à **" . ($lastScan && $lastScan->action === 'exit' ? $lastScan->occurred_at->format('H:i') : '—') . "**.";

        return compact(
            'children',
            'selectedChild',
            'accessLogs',
            'currentStatus',
            'lastScan',
            'weeklyStats',
            'accessPoints',
            'aiSecurityInsight'
        );
    }
}

