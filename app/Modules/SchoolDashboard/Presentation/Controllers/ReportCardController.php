<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Semester;
use App\Modules\Academic\Domain\Models\Student;
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
    const CYCLES = ['Cycle 1', 'Cycle 2', 'Cycle 3'];

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
        $cycles = self::CYCLES;

        return view('SchoolDashboard::report-card.referentials', compact('domains', 'cycles'));
    }

    public function storeDomain(Request $request, CreateDomainUseCase $useCase)
    {
        $data = $request->validate([
            'cycle' => ['required', 'string', 'in:' . implode(',', self::CYCLES)],
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

    public function studentProfile($id, ReportCardAssessmentRepositoryInterface $assessmentRepository, ReportCardObservationRepositoryInterface $observationRepository, ReportCardStatsService $stats)
    {
        $student = Student::where('school_id', auth()->user()->school_id)->with(['academicClass', 'guardians'])->findOrFail($id);

        $currentSemester = $stats->currentSemester(auth()->user()->school_id);

        $competencyMap = $currentSemester
            ? $assessmentRepository->forStudentAndSemester($student->id, $currentSemester->id)
            : collect();

        $allCompetencies = ReportCardCompetency::whereHas('subdomain.domain', function ($q) use ($student) {
            $q->where('school_id', $student->school_id);
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

        return view('SchoolDashboard::report-card.student', compact(
            'student', 'competencyTree', 'competencyMap', 'radarData', 'attendanceRate', 'unjustifiedAbsences', 'recentRecords', 'observations', 'currentSemester'
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

    public function printLivret($id, ReportCardAssessmentRepositoryInterface $assessmentRepository, ReportCardObservationRepositoryInterface $observationRepository, ReportCardStatsService $stats)
    {
        $student = Student::where('school_id', auth()->user()->school_id)->with('academicClass')->findOrFail($id);
        $school = auth()->user()->school;
        $currentSemester = $stats->currentSemester(auth()->user()->school_id);

        $competencyMap = $currentSemester
            ? $assessmentRepository->forStudentAndSemester($student->id, $currentSemester->id)
            : collect();

        $allCompetencies = ReportCardCompetency::whereHas('subdomain.domain', function ($q) use ($student) {
            $q->where('school_id', $student->school_id);
        })->with('subdomain.domain')->get();

        $competencyTree = $allCompetencies->groupBy(fn ($c) => $c->subdomain->domain->name)
            ->map(fn ($comps) => $comps->groupBy(fn ($c) => $c->subdomain->name));

        $observations = $this->visibleObservations($observationRepository->forStudent($student->id), $student->academic_class_id);

        return view('SchoolDashboard::report-card.print', compact('student', 'school', 'currentSemester', 'competencyMap', 'competencyTree', 'observations'));
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
