<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Semester;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\StudentClassMovement;
use App\Modules\Bulletin\Application\Services\BulletinStatsService;
use App\Modules\Bulletin\Domain\Repositories\BulletinGradeRepositoryInterface;
use App\Modules\Presence\Domain\Models\AttendanceRecord;
use App\Modules\ReportCard\Application\DTOs\CreateCompetencyDTO;
use App\Modules\ReportCard\Application\DTOs\CreateDomainDTO;
use App\Modules\ReportCard\Application\DTOs\CreateObservationDTO;
use App\Modules\ReportCard\Application\DTOs\CreateSubdomainDTO;
use App\Modules\ReportCard\Application\DTOs\SaveAssessmentsDTO;
use App\Modules\ReportCard\Application\Services\ReportCardStatsService;
use App\Modules\ReportCard\Application\UseCases\CreateCompetencyUseCase;
use App\Modules\ReportCard\Application\UseCases\CreateDomainUseCase;
use App\Modules\ReportCard\Application\UseCases\CreateObservationUseCase;
use App\Modules\ReportCard\Application\UseCases\CreateSubdomainUseCase;
use App\Modules\ReportCard\Application\UseCases\SaveAssessmentsUseCase;
use App\Modules\ReportCard\Domain\Models\ReportCardCompetency;
use App\Modules\ReportCard\Domain\Repositories\ReportCardAssessmentRepositoryInterface;
use App\Modules\ReportCard\Domain\Repositories\ReportCardCompetencyRepositoryInterface;
use App\Modules\ReportCard\Domain\Repositories\ReportCardDomainRepositoryInterface;
use App\Modules\ReportCard\Domain\Repositories\ReportCardObservationRepositoryInterface;
use App\Modules\ReportCard\Domain\Repositories\ReportCardSubdomainRepositoryInterface;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    /** Same taxonomy as AcademicClass::LEVELS_BY_CYCLE — a domain's cycle must match a real class cycle to be usable. */
    public static function CYCLES(): array
    {
        return array_keys(AcademicClass::LEVELS_BY_CYCLE);
    }

    public function dashboard(ReportCardStatsService $stats)
    {
        $schoolId = auth()->user()->school_id;
        $branchId = auth()->user()->activeBranchId();

        $current = $stats->currentSemester($schoolId);
        $previous = $stats->previousSemester($schoolId, $current);

        $acquisitionRate = $stats->acquisitionRate($schoolId, $current?->id);
        $acquisitionGrowth = $stats->acquisitionGrowth($schoolId, $current, $previous);
        $attendanceRate = $stats->attendanceRate30Days($schoolId);
        $masteryBreakdown = $stats->masteryBreakdown($schoolId, $current?->id);
        $domainsAtRisk = $stats->domainsAtRisk($schoolId, $current?->id);
        $classesActive = $stats->classesActive($schoolId, $current?->id, $branchId);
        $alerts = $stats->alerts($schoolId, $current?->id, $branchId);

        return view('SchoolDashboard::report-card.dashboard', compact(
            'current', 'acquisitionRate', 'acquisitionGrowth', 'attendanceRate',
            'masteryBreakdown', 'domainsAtRisk', 'classesActive', 'alerts'
        ));
    }

    public function referentials(ReportCardDomainRepositoryInterface $repository)
    {
        $domains = $repository->allWithTree();
        $cycles = self::CYCLES();

        return view('SchoolDashboard::report-card.referentials', compact('domains', 'cycles'));
    }

    public function storeDomain(Request $request, CreateDomainUseCase $useCase)
    {
        $data = $request->validate([
            'cycle' => ['required', 'string', 'in:' . implode(',', self::CYCLES())],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $useCase->execute(new CreateDomainDTO($data));

        return redirect()->route('school.report-card.referentials')->with('success', 'Domaine ajouté avec succès !');
    }

    public function destroyDomain($id, ReportCardDomainRepositoryInterface $repository)
    {
        $repository->delete($id);

        return redirect()->route('school.report-card.referentials')->with('success', 'Domaine supprimé.');
    }

    public function storeSubdomain(Request $request, CreateSubdomainUseCase $useCase)
    {
        $data = $request->validate([
            'domain_id' => ['required', 'exists:report_card_domains,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $useCase->execute(new CreateSubdomainDTO($data));

        return redirect()->route('school.report-card.referentials')->with('success', 'Sous-domaine ajouté avec succès !');
    }

    public function destroySubdomain($id, ReportCardSubdomainRepositoryInterface $repository)
    {
        $repository->delete($id);

        return redirect()->route('school.report-card.referentials')->with('success', 'Sous-domaine supprimé.');
    }

    public function storeCompetency(Request $request, CreateCompetencyUseCase $useCase)
    {
        $data = $request->validate([
            'subdomain_id' => ['required', 'exists:report_card_subdomains,id'],
            'statement' => ['required', 'string', 'max:500'],
        ]);

        $useCase->execute(new CreateCompetencyDTO($data));

        return redirect()->route('school.report-card.referentials')->with('success', 'Compétence ajoutée avec succès !');
    }

    public function destroyCompetency($id, ReportCardCompetencyRepositoryInterface $repository)
    {
        $repository->delete($id);

        return redirect()->route('school.report-card.referentials')->with('success', 'Compétence supprimée.');
    }

    public function evaluation(Request $request, ReportCardCompetencyRepositoryInterface $competencyRepository, ReportCardAssessmentRepositoryInterface $assessmentRepository, ReportCardStatsService $stats)
    {
        $schoolId = auth()->user()->school_id;
        $branchId = auth()->user()->activeBranchId();

        $classes = AcademicClass::where('school_id', $schoolId)->whereBranch($branchId)->orderBy('name')->get();
        $competencies = $competencyRepository->forSchool($schoolId);
        $currentSemester = $stats->currentSemester($schoolId);

        $classId = $request->get('class_id');
        $subjectId = $request->get('subject_id');
        $competencyId = $request->get('competency_id');

        $selectedClass = $classId ? $classes->firstWhere('id', (int) $classId) : null;

        // Once a class is picked, only show competencies from domains tagged for
        // that class's own cycle — a préscolaire class has no business being
        // evaluated against collège-level domains, and vice versa.
        if ($selectedClass && $selectedClass->cycle) {
            $competencies = $competencies->filter(
                fn ($competency) => $competency->subdomain->domain->cycle === $selectedClass->cycle
            )->values();
        }
        $subjects = $selectedClass ? $selectedClass->subjects()->orderBy('name')->get() : collect();
        if ($selectedClass && $teacher = auth()->user()->teacher) {
            $subjects = $subjects->filter(fn ($subject) => $teacher->teachesSubject($subject->id))->values();
        }
        $students = collect();
        $existingLevels = collect();

        if ($selectedClass && $competencyId && $currentSemester) {
            $students = Student::where('school_id', $schoolId)->where('academic_class_id', $selectedClass->id)->where('status', 'active')->orderBy('first_name')->get();

            foreach ($students as $student) {
                $assessments = $assessmentRepository->forStudentAndSemester($student->id, $currentSemester->id);
                if (isset($assessments[$competencyId])) {
                    $existingLevels[$student->id] = $assessments[$competencyId]->level;
                }
            }
        }

        return view('SchoolDashboard::report-card.evaluation', compact(
            'classes', 'competencies', 'selectedClass', 'subjects', 'students', 'existingLevels', 'currentSemester',
            'classId', 'subjectId', 'competencyId'
        ));
    }

    public function storeAssessments(Request $request, SaveAssessmentsUseCase $useCase, ReportCardStatsService $stats)
    {
        $data = $request->validate([
            'competency_id' => ['required', 'exists:report_card_competencies,id'],
            'class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'levels' => ['required', 'array', 'min:1'],
            'levels.*' => ['required', 'string', 'in:acquis,en_cours,non_acquis'],
        ]);

        $semester = $stats->currentSemester(auth()->user()->school_id);
        abort_unless($semester, 422, 'Aucun semestre actif défini pour cette école.');

        $teacher = auth()->user()->teacher;
        abort_if($teacher && !empty($data['subject_id']) && !$teacher->teachesSubject((int) $data['subject_id']), 403, "Vous ne pouvez évaluer que pour vos propres matières.");

        $dto = new SaveAssessmentsDTO(
            (int) $data['competency_id'],
            $semester->id,
            $data['subject_id'] ?? null,
            $teacher?->id,
            $data['levels']
        );

        $count = $useCase->execute($dto);

        return redirect()->route('school.report-card.evaluation', [
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'] ?? null,
            'competency_id' => $data['competency_id'],
        ])->with('success', "Évaluation enregistrée pour {$count} élève(s) !");
    }

    /**
     * A subject teacher only sees their own observations on a student; a
     * head teacher sees all of them, same as an admin. See Teacher::isHeadTeacherOf().
     */
    private function visibleObservations($observations, ?int $classId)
    {
        $teacher = auth()->user()->teacher;
        if (!$teacher || ($classId && $teacher->isHeadTeacherOf($classId))) {
            return $observations;
        }

        return $observations->where('teacher_id', $teacher->id)->values();
    }

    public function studentProfile(
        $id,
        ReportCardAssessmentRepositoryInterface $assessmentRepository,
        ReportCardObservationRepositoryInterface $observationRepository,
        ReportCardStatsService $stats,
        BulletinGradeRepositoryInterface $gradeRepository,
        BulletinStatsService $bulletinStats
    ) {
        $schoolId = auth()->user()->school_id;
        $student = Student::where('school_id', $schoolId)->with(['academicClass', 'guardians'])->findOrFail($id);

        $currentSemester = $stats->currentSemester(auth()->user()->school_id);

        $competencyMap = $currentSemester
            ? $assessmentRepository->forStudentAndSemester($student->id, $currentSemester->id)
            : collect();

        $studentCycle = $student->academicClass?->cycle;

        $allCompetencies = ReportCardCompetency::whereHas('subdomain.domain', function ($q) use ($student, $studentCycle) {
            $q->where('school_id', $student->school_id);
            if ($studentCycle) {
                $q->where('cycle', $studentCycle);
            }
        })->with('subdomain.domain')->get();

        $competencyTree = $allCompetencies->groupBy(fn ($c) => $c->subdomain->domain->name)
            ->map(fn ($comps) => $comps->groupBy(fn ($c) => $c->subdomain->name));

        $allAssessments = $assessmentRepository->forStudent($student->id);
        $radarData = $allAssessments->groupBy(fn ($a) => $a->semester?->name ?? '—')->map(function ($group) {
            $score = fn ($level) => $level === 'acquis' ? 100 : ($level === 'en_cours' ? 50 : 0);
            return $group->groupBy(fn ($a) => $a->competency?->subdomain?->domain?->name)->map(function ($domainGroup) use ($score) {
                return round($domainGroup->avg(fn ($a) => $score($a->level)));
            });
        });

        $records = AttendanceRecord::where('student_id', $student->id)->orderByDesc('date')->get();
        $attendanceRate = $records->isNotEmpty()
            ? round($records->whereIn('status', [AttendanceRecord::STATUS_PRESENT, AttendanceRecord::STATUS_LATE])->count() / $records->count() * 100)
            : null;
        $unjustifiedAbsences = $records->where('status', AttendanceRecord::STATUS_ABSENT)->filter(fn ($r) => !$r->justified)->count();
        $recentRecords = $records->take(5);

        $observations = $this->visibleObservations($observationRepository->forStudent($student->id), $student->academic_class_id);

        // Résultats par trimestre (année scolaire en cours) — même logique que le livret PDF.
        $termResults = collect();
        if ($currentSemester && $student->academic_class_id) {
            $semesters = $bulletinStats->academicYearSemesters($schoolId, $currentSemester);
            $termResults = $semesters->map(fn ($semester) => array_merge(
                ['semester' => $semester],
                $this->semesterResultFor($student->academic_class_id, $semester->id, $student->id, $gradeRepository, $bulletinStats)
            ));
        }

        $totalDays = $records->count();
        $justifiedAbsences = $records->where('status', AttendanceRecord::STATUS_ABSENT)->filter(fn ($r) => $r->justified)->count();
        $lateCount = $records->where('status', AttendanceRecord::STATUS_LATE)->count();

        $disciplinaryRecords = \App\Modules\Academic\Domain\Models\StudentDisciplinaryRecord::where('student_id', $student->id)
            ->orderByDesc('recorded_date')
            ->get();

        $attendanceHistory = $records;

        return view('SchoolDashboard::report-card.student', compact(
            'student', 'competencyTree', 'competencyMap', 'radarData', 'attendanceRate', 'unjustifiedAbsences', 'recentRecords', 'observations', 'currentSemester',
            'termResults', 'totalDays', 'justifiedAbsences', 'lateCount', 'disciplinaryRecords', 'attendanceHistory'
        ));
    }

    public function storeObservation(Request $request, CreateObservationUseCase $useCase, ReportCardStatsService $stats)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 422, "Seul un compte enseignant peut ajouter une observation.");

        $semester = $stats->currentSemester(auth()->user()->school_id);
        abort_unless($semester, 422, 'Aucun semestre actif défini pour cette école.');

        $useCase->execute(new CreateObservationDTO([
            'student_id' => $data['student_id'],
            'teacher_id' => $teacher->id,
            'semester_id' => $semester->id,
            'comment' => $data['comment'],
        ]));

        return redirect()->route('school.report-card.student', $data['student_id'])->with('success', 'Observation ajoutée avec succès !');
    }

    /** Real per-subject grades + moyenne + rang for one student, in one class, for one semester. Shared by the current-year term loop and the multi-year Parcours below. */
    private function semesterResultFor(int $classId, int $semesterId, int $studentId, BulletinGradeRepositoryInterface $gradeRepository, BulletinStatsService $bulletinStats): array
    {
        $classGrades = $gradeRepository->forClassAndSemester($classId, $semesterId);
        $classGrades = $bulletinStats->mergeHomeworkGrades($classGrades, $classId, $semesterId);
        $subjectGrades = $bulletinStats->aggregateToSubjectGrades($classGrades->where('student_id', $studentId));
        $average = $bulletinStats->studentAverage($subjectGrades);

        $ranking = $bulletinStats->classRanking($classId, $semesterId, $classGrades);
        $studentRow = collect($ranking)->firstWhere('student.id', $studentId);

        return [
            'subjectGrades' => $subjectGrades,
            'average' => $average,
            'rank' => $studentRow['rank'] ?? null,
            'classSize' => count($ranking),
        ];
    }

    public function printLivret(
        $id,
        ReportCardAssessmentRepositoryInterface $assessmentRepository,
        ReportCardObservationRepositoryInterface $observationRepository,
        ReportCardStatsService $stats,
        BulletinGradeRepositoryInterface $gradeRepository,
        BulletinStatsService $bulletinStats
    ) {
        $schoolId = auth()->user()->school_id;
        $student = Student::where('school_id', $schoolId)->with(['academicClass', 'branch'])->findOrFail($id);
        $school = auth()->user()->school;
        $currentSemester = $stats->currentSemester($schoolId);

        $competencyMap = $currentSemester
            ? $assessmentRepository->forStudentAndSemester($student->id, $currentSemester->id)
            : collect();

        $studentCycle = $student->academicClass?->cycle;

        $allCompetencies = ReportCardCompetency::whereHas('subdomain.domain', function ($q) use ($student, $studentCycle) {
            $q->where('school_id', $student->school_id);
            if ($studentCycle) {
                $q->where('cycle', $studentCycle);
            }
        })->with('subdomain.domain')->get();

        $competencyTree = $allCompetencies->groupBy(fn ($c) => $c->subdomain->domain->name)
            ->map(fn ($comps) => $comps->groupBy(fn ($c) => $c->subdomain->name));

        $observations = $observationRepository->forStudent($student->id);

        // Résultats par trimestre (année scolaire en cours)
        $termResults = collect();
        if ($currentSemester && $student->academic_class_id) {
            $semesters = $bulletinStats->academicYearSemesters($schoolId, $currentSemester);
            $termResults = $semesters->map(fn ($semester) => array_merge(
                ['semester' => $semester],
                $this->semesterResultFor($student->academic_class_id, $semester->id, $student->id, $gradeRepository, $bulletinStats)
            ));
        }

        $isLastTerm = $currentSemester ? $bulletinStats->isLastTermOfYear($schoolId, $currentSemester) : false;
        $annualAverage = $isLastTerm ? $bulletinStats->annualFinalAverage($schoolId, $student, $currentSemester) : null;

        // Parcours scolaire : année en cours + chaque année antérieure retracée via les mouvements de classe réels
        $movements = StudentClassMovement::where('student_id', $student->id)->with(['fromClass', 'toClass'])->orderByDesc('created_at')->get();

        $currentTerm = $termResults->last();
        $yearRows = collect([[
            'academic_year' => $student->academic_year,
            'class' => $student->academicClass->name ?? '—',
            'average' => $annualAverage ?? $currentTerm['average'] ?? null,
            'rank' => $currentTerm['rank'] ?? null,
            'classSize' => $currentTerm['classSize'] ?? null,
            'decision' => 'Année en cours',
        ]]);

        foreach ($movements as $movement) {
            $pastSemester = $movement->from_academic_year
                ? Semester::where('school_id', $schoolId)->where('academic_year', $movement->from_academic_year)->orderByDesc('term_number')->first()
                : null;

            $pastResult = ($pastSemester && $movement->fromClass)
                ? $this->semesterResultFor($movement->from_class_id, $pastSemester->id, $student->id, $gradeRepository, $bulletinStats)
                : ['average' => null, 'rank' => null, 'classSize' => null];

            $yearRows->push([
                'academic_year' => $movement->from_academic_year,
                'class' => $movement->fromClass->name ?? '—',
                'average' => $pastResult['average'],
                'rank' => $pastResult['rank'],
                'classSize' => $pastResult['classSize'],
                'decision' => $movement->type === StudentClassMovement::TYPE_PROMOTION ? 'Passage en classe supérieure' : 'Transfert',
            ]);
        }

        // Assiduité (année scolaire en cours)
        $since = $currentSemester?->start_date ?? now()->subMonths(4)->toDateString();
        $attendanceRecords = AttendanceRecord::where('student_id', $student->id)->where('date', '>=', $since)->get();
        $totalDays = $attendanceRecords->count();
        $justifiedAbsences = $attendanceRecords->where('status', AttendanceRecord::STATUS_ABSENT)->filter(fn ($r) => $r->justified)->count();
        $unjustifiedAbsences = $attendanceRecords->where('status', AttendanceRecord::STATUS_ABSENT)->filter(fn ($r) => !$r->justified)->count();
        $lateCount = $attendanceRecords->where('status', AttendanceRecord::STATUS_LATE)->count();

        $disciplinaryRecords = \App\Modules\Academic\Domain\Models\StudentDisciplinaryRecord::where('student_id', $student->id)
            ->orderByDesc('recorded_date')
            ->get();

        // Transport scolaire (résumé réel, module Transport)
        $transportStops = \Illuminate\Support\Facades\DB::table('transport_route_stop_student')
            ->join('transport_route_stops', 'transport_route_stops.id', '=', 'transport_route_stop_student.route_stop_id')
            ->join('transport_routes', 'transport_routes.id', '=', 'transport_route_stops.route_id')
            ->where('transport_route_stop_student.student_id', $student->id)
            ->select('transport_route_stop_student.period', 'transport_route_stops.name as stop_name', 'transport_routes.name as route_name')
            ->get();

        return view('SchoolDashboard::report-card.print', compact(
            'student', 'school', 'currentSemester', 'competencyMap', 'competencyTree', 'observations',
            'termResults', 'isLastTerm', 'annualAverage', 'yearRows',
            'totalDays', 'justifiedAbsences', 'unjustifiedAbsences', 'lateCount', 'transportStops', 'disciplinaryRecords'
        ));
    }

    public function printGlobalReport(ReportCardStatsService $stats)
    {
        $schoolId = auth()->user()->school_id;
        $branchId = auth()->user()->activeBranchId();
        $school = auth()->user()->school;

        $current = $stats->currentSemester($schoolId);
        $acquisitionRate = $stats->acquisitionRate($schoolId, $current?->id);
        $attendanceRate = $stats->attendanceRate30Days($schoolId);
        $masteryBreakdown = $stats->masteryBreakdown($schoolId, $current?->id);
        $classesActive = $stats->classesActive($schoolId, $current?->id, $branchId);
        $domainsAtRisk = $stats->domainsAtRisk($schoolId, $current?->id);

        return view('SchoolDashboard::report-card.print_global', compact(
            'school', 'current', 'acquisitionRate', 'attendanceRate', 'masteryBreakdown', 'classesActive', 'domainsAtRisk'
        ));
    }
}
