<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Bulletin\Application\DTOs\SaveGradesDTO;
use App\Modules\Bulletin\Application\DTOs\SaveTemplateDTO;
use App\Modules\Bulletin\Application\Services\BulletinStatsService;
use App\Modules\Bulletin\Application\UseCases\PublishClassUseCase;
use App\Modules\Bulletin\Application\UseCases\PublishSubjectGradesUseCase;
use App\Modules\Bulletin\Application\UseCases\SaveGradesUseCase;
use App\Modules\Bulletin\Application\UseCases\SaveTemplateUseCase;
use App\Modules\Bulletin\Application\UseCases\UnpublishSubjectGradesUseCase;
use App\Modules\Bulletin\Application\UseCases\ValidateClassUseCase;
use App\Modules\Bulletin\Domain\Models\BulletinPublication;
use App\Modules\Bulletin\Domain\Models\BulletinSubjectPublication;
use App\Modules\Bulletin\Domain\Repositories\BulletinGradeRepositoryInterface;
use App\Modules\Bulletin\Domain\Repositories\BulletinPublicationRepositoryInterface;
use App\Modules\Bulletin\Domain\Repositories\BulletinSubjectPublicationRepositoryInterface;
use App\Modules\Bulletin\Domain\Repositories\BulletinTemplateRepositoryInterface;
use App\Modules\Bulletin\Domain\Repositories\BulletinEvaluationTypeRepositoryInterface;
use App\Modules\Bulletin\Application\DTOs\CreateBulletinEvaluationTypeDTO;
use App\Modules\Bulletin\Application\DTOs\UpdateBulletinEvaluationTypeDTO;
use App\Modules\Bulletin\Application\UseCases\CreateBulletinEvaluationTypeUseCase;
use App\Modules\Bulletin\Application\UseCases\UpdateBulletinEvaluationTypeUseCase;
use App\Modules\Bulletin\Application\UseCases\DeleteBulletinEvaluationTypeUseCase;
use App\Modules\Presence\Domain\Models\AttendanceRecord;
use Illuminate\Http\Request;

class BulletinController extends Controller
{
    public function dashboard(BulletinStatsService $stats)
    {
        $schoolId = auth()->user()->school_id;
        $current = $stats->currentSemester($schoolId);

        $gradesEnteredRate = $stats->gradesEnteredRate($schoolId, $current?->id);
        $publicationRates = $stats->publicationRates($schoolId, $current?->id);
        $significantGaps = $stats->significantGaps($schoolId, $current?->id);
        $incompleteEntries = $stats->incompleteEntries($schoolId, $current?->id);

        $classes = AcademicClass::where('school_id', $schoolId)->whereBranch(auth()->user()->activeBranchId())
            ->with(['headTeacher', 'students' => fn ($q) => $q->where('status', 'active'), 'subjects'])
            ->get();

        $teacher = auth()->user()->teacher;
        if ($teacher) {
            $classes = $classes->filter(fn ($class) => $teacher->classes->contains('id', $class->id))->values();
        }

        $topRankings = collect();

        $classProgress = $classes->map(function ($class) use ($stats, $current, $teacher, &$topRankings) {
            $grades = $current ? app(BulletinGradeRepositoryInterface::class)->forClassAndSemester($class->id, $current->id) : collect();
            $grades = $current ? $stats->mergeHomeworkGrades($grades, $class->id, $current->id) : $grades;
            $ranking = $current ? $stats->classRanking($class->id, $current->id, $grades) : [];
            $graded = collect($ranking)->sum('graded_subjects');
            $expected = collect($ranking)->sum('expected_subjects');

            $publication = $current ? app(BulletinPublicationRepositoryInterface::class)->findOrCreate($class->id, $current->id) : null;

            if ($teacher) {
                $topRankings->push(['class' => $class, 'ranking' => array_slice($ranking, 0, 10)]);
            }

            return [
                'class' => $class,
                'teacher' => $class->headTeacher,
                'progress' => $expected > 0 ? round($graded / $expected * 100) : 0,
                'publication_status' => $publication?->status ?? 'draft',
            ];
        });

        return view('SchoolDashboard::bulletins.dashboard', compact(
            'current', 'gradesEnteredRate', 'publicationRates', 'significantGaps', 'incompleteEntries', 'classProgress', 'topRankings', 'teacher'
        ));
    }

    public function grades(Request $request, BulletinGradeRepositoryInterface $gradeRepository, BulletinEvaluationTypeRepositoryInterface $evaluationTypeRepository, BulletinSubjectPublicationRepositoryInterface $subjectPublicationRepository, BulletinStatsService $stats)
    {
        $schoolId = auth()->user()->school_id;
        $branchId = auth()->user()->activeBranchId();

        $classes = AcademicClass::where('school_id', $schoolId)->whereBranch($branchId)->orderBy('name')->get();
        $currentSemester = $stats->currentSemester($schoolId);
        $evaluationTypes = $evaluationTypeRepository->all();

        $classId = $request->get('class_id');
        $subjectId = $request->get('subject_id');

        $selectedClass = $classId ? $classes->firstWhere('id', (int) $classId) : null;
        $subjects = $selectedClass ? $selectedClass->subjects()->orderBy('name')->get() : collect();
        if ($selectedClass && $teacher = auth()->user()->teacher) {
            $subjects = $subjects->filter(fn ($subject) => $teacher->teachesSubject($subject->id))->values();
        }
        $students = collect();
        $existingGrades = collect();
        $columns = collect();
        $subjectPublicationStatus = null;

        if ($selectedClass && $subjectId && $currentSemester) {
            $subjectPublicationStatus = $subjectPublicationRepository->findOrCreate($selectedClass->id, (int) $subjectId, $currentSemester->id)->status;
            $students = Student::where('school_id', $schoolId)->where('academic_class_id', $selectedClass->id)->where('status', 'active')->orderBy('first_name')->get();
            $rawGrades = $gradeRepository->forClassAndSemester($selectedClass->id, $currentSemester->id)->where('subject_id', $subjectId);
            $mergedGrades = $stats->mergeHomeworkGrades($rawGrades, $selectedClass->id, $currentSemester->id, (int) $subjectId);
            $existingGrades = $mergedGrades
                ->groupBy('student_id')
                ->map(fn ($rows) => $rows->groupBy(fn ($r) => !empty($r->homework_assignment_id) ? 'hw_' . $r->homework_assignment_id : 'type_' . $r->evaluation_type_id));

            $linkedColumns = $stats->linkedHomeworkColumns($schoolId, $selectedClass->id, $currentSemester->id, (int) $subjectId);
            foreach ($evaluationTypes as $type) {
                if ($type->linked_homework_type) {
                    $columns = $columns->concat($linkedColumns->where('type.id', $type->id));
                } else {
                    $columns->push((object) ['key' => 'type_' . $type->id, 'label' => $type->name, 'type' => $type, 'assignment' => null, 'linked' => false]);
                }
            }
        }

        return view('SchoolDashboard::bulletins.grades', compact(
            'classes', 'selectedClass', 'subjects', 'students', 'existingGrades', 'evaluationTypes', 'columns', 'currentSemester', 'classId', 'subjectId', 'stats', 'subjectPublicationStatus'
        ));
    }

    public function publishSubjectGrades(Request $request, PublishSubjectGradesUseCase $useCase, BulletinStatsService $stats)
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
        ]);

        $teacher = auth()->user()->teacher;
        abort_if($teacher && !$teacher->teachesSubject((int) $data['subject_id']), 403, "Vous ne pouvez publier que vos propres matières.");

        $semester = $stats->currentSemester(auth()->user()->school_id);
        abort_unless($semester, 422, 'Aucun semestre actif défini pour cette école.');

        $useCase->execute((int) $data['class_id'], (int) $data['subject_id'], $semester->id, auth()->id());

        return redirect()->route('school.academic.bulletins.grades', [
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'],
        ])->with('success', 'Notes publiées pour cette matière — visibles sur le bulletin.');
    }

    public function unpublishSubjectGrades(Request $request, UnpublishSubjectGradesUseCase $useCase, BulletinStatsService $stats)
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
        ]);

        $teacher = auth()->user()->teacher;
        abort_if($teacher && !$teacher->teachesSubject((int) $data['subject_id']), 403, "Vous ne pouvez dépublier que vos propres matières.");

        $semester = $stats->currentSemester(auth()->user()->school_id);
        abort_unless($semester, 422, 'Aucun semestre actif défini pour cette école.');

        $useCase->execute((int) $data['class_id'], (int) $data['subject_id'], $semester->id);

        return redirect()->route('school.academic.bulletins.grades', [
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'],
        ])->with('success', 'Publication retirée — la matière redevient un brouillon sur le bulletin.');
    }

    public function storeGrades(Request $request, SaveGradesUseCase $useCase, BulletinEvaluationTypeRepositoryInterface $evaluationTypeRepository, BulletinStatsService $stats)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'exists:academic_classes,id'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.*.score' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'entries.*.*.remark' => ['nullable', 'string', 'max:1000'],
        ]);

        $semester = $stats->currentSemester(auth()->user()->school_id);
        abort_unless($semester, 422, 'Aucun semestre actif défini pour cette école.');

        $teacher = auth()->user()->teacher;
        abort_if($teacher && !$teacher->teachesSubject((int) $data['subject_id']), 403, "Vous ne pouvez saisir des notes que pour vos propres matières.");

        $validTypeIds = $evaluationTypeRepository->all()->pluck('id')->all();
        foreach ($data['entries'] as $typeEntries) {
            abort_if(array_diff(array_keys($typeEntries), $validTypeIds), 422, "Type d'évaluation invalide.");
        }

        $dto = new SaveGradesDTO((int) $data['subject_id'], $semester->id, $teacher?->id, $data['entries']);
        $count = $useCase->execute($dto);

        return redirect()->route('school.academic.bulletins.grades', [
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'],
        ])->with('success', "Notes enregistrées pour {$count} élève(s) !");
    }

    public function destroyGradeEntry($id, BulletinGradeRepositoryInterface $gradeRepository, Request $request)
    {
        $grade = $gradeRepository->find($id);
        abort_unless($grade, 404);

        $teacher = auth()->user()->teacher;
        abort_if($teacher && !$teacher->teachesSubject($grade->subject_id), 403, "Vous ne pouvez supprimer que des notes de vos propres matières.");

        $gradeRepository->delete($id);

        return redirect()->route('school.academic.bulletins.grades', [
            'class_id' => $request->get('class_id'),
            'subject_id' => $grade->subject_id,
        ])->with('success', 'Note supprimée avec succès.');
    }

    /**
     * Whole-class, cross-matière bulletin data (moyennes/rang/validation) is
     * head-teacher/admin territory — a subject teacher only sees their own
     * matière. See Teacher::isHeadTeacherOf().
     */
    private function ensureHeadTeacherAccess(int $classId): void
    {
        $teacher = auth()->user()->teacher;
        abort_if($teacher && !$teacher->isHeadTeacherOf($classId), 403, "Seul le professeur principal de cette classe peut accéder à cette page.");
    }

    public function validation(Request $request, BulletinGradeRepositoryInterface $gradeRepository, BulletinPublicationRepositoryInterface $publicationRepository, BulletinStatsService $stats)
    {
        $schoolId = auth()->user()->school_id;
        $branchId = auth()->user()->activeBranchId();

        $classes = AcademicClass::where('school_id', $schoolId)->whereBranch($branchId)->orderBy('name')->get();
        if ($teacher = auth()->user()->teacher) {
            $classes = $classes->filter(fn ($class) => $teacher->isHeadTeacherOf($class->id))->values();
        }
        $currentSemester = $stats->currentSemester($schoolId);

        $classId = $request->get('class_id', $classes->first()?->id);
        $selectedClass = $classId ? $classes->firstWhere('id', (int) $classId) : null;
        if ($classId) {
            $this->ensureHeadTeacherAccess((int) $classId);
        }

        $ranking = [];
        $publication = null;
        $classAverage = null;

        if ($selectedClass && $currentSemester) {
            $grades = $gradeRepository->forClassAndSemester($selectedClass->id, $currentSemester->id);
            $grades = $stats->mergeHomeworkGrades($grades, $selectedClass->id, $currentSemester->id);
            $ranking = $stats->classRanking($selectedClass->id, $currentSemester->id, $grades);
            $publication = $publicationRepository->findOrCreate($selectedClass->id, $currentSemester->id);

            $averages = collect($ranking)->pluck('average')->filter(fn ($a) => $a !== null);
            $classAverage = $averages->isNotEmpty() ? round($averages->avg(), 2) : null;

            foreach ($ranking as &$row) {
                $since = now()->subDays(30)->toDateString();
                $records = AttendanceRecord::where('student_id', $row['student']->id)->where('date', '>=', $since)->get();
                $row['attendance_rate'] = $records->isNotEmpty()
                    ? round($records->whereIn('status', ['present', 'late'])->count() / $records->count() * 100)
                    : null;
            }
            unset($row);
        }

        return view('SchoolDashboard::bulletins.validation', compact(
            'classes', 'selectedClass', 'classId', 'ranking', 'publication', 'classAverage', 'currentSemester'
        ));
    }

    public function validateClass(Request $request, ValidateClassUseCase $useCase, BulletinStatsService $stats)
    {
        $data = $request->validate(['class_id' => ['required', 'exists:academic_classes,id']]);
        $this->ensureHeadTeacherAccess((int) $data['class_id']);
        $semester = $stats->currentSemester(auth()->user()->school_id);
        abort_unless($semester, 422, 'Aucun semestre actif défini pour cette école.');

        $useCase->execute((int) $data['class_id'], $semester->id, auth()->id());

        return redirect()->route('school.academic.bulletins.validation', ['class_id' => $data['class_id']])
            ->with('success', 'Bulletins de la classe validés.');
    }

    public function publishClass(Request $request, PublishClassUseCase $useCase, BulletinStatsService $stats)
    {
        $data = $request->validate(['class_id' => ['required', 'exists:academic_classes,id']]);
        $this->ensureHeadTeacherAccess((int) $data['class_id']);
        $semester = $stats->currentSemester(auth()->user()->school_id);
        abort_unless($semester, 422, 'Aucun semestre actif défini pour cette école.');

        $useCase->execute((int) $data['class_id'], $semester->id);

        return redirect()->route('school.academic.bulletins.validation', ['class_id' => $data['class_id']])
            ->with('success', 'Bulletins de la classe marqués comme publiés.');
    }

    public function template(BulletinTemplateRepositoryInterface $repository)
    {
        $template = $repository->findOrDefault(auth()->user()->school_id);
        $previewStudent = Student::where('school_id', auth()->user()->school_id)->where('status', 'active')->first();

        return view('SchoolDashboard::bulletins.template', compact('template', 'previewStudent'));
    }

    public function storeTemplate(Request $request, SaveTemplateUseCase $useCase)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'show_coefficient' => ['nullable', 'boolean'],
            'show_class_average' => ['nullable', 'boolean'],
            'show_highest_lowest' => ['nullable', 'boolean'],
            'show_ranking' => ['nullable', 'boolean'],
            'show_signature_area' => ['nullable', 'boolean'],
            'suggested_remarks_enabled' => ['nullable', 'boolean'],
        ]);

        foreach (['show_coefficient', 'show_class_average', 'show_highest_lowest', 'show_ranking', 'show_signature_area', 'suggested_remarks_enabled'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        $useCase->execute(auth()->user()->school_id, new SaveTemplateDTO($data));

        return redirect()->route('school.academic.bulletins.template')->with('success', 'Modèle de bulletin enregistré avec succès !');
    }

    public function evaluationTypes(BulletinEvaluationTypeRepositoryInterface $repository)
    {
        $types = $repository->all();
        return view('SchoolDashboard::bulletins.evaluation_types', compact('types'));
    }

    public function storeEvaluationType(Request $request, CreateBulletinEvaluationTypeUseCase $useCase)
    {
        abort_if(auth()->user()->teacher, 403, "Seul un administrateur peut modifier les coefficients.");

        $data = $this->validateEvaluationType($request);
        $useCase->execute(new CreateBulletinEvaluationTypeDTO($data));

        return redirect()->route('school.academic.bulletins.evaluation-types')->with('success', "Type d'évaluation créé avec succès.");
    }

    public function updateEvaluationType(Request $request, $id, UpdateBulletinEvaluationTypeUseCase $useCase)
    {
        abort_if(auth()->user()->teacher, 403, "Seul un administrateur peut modifier les coefficients.");

        $data = $this->validateEvaluationType($request);
        $useCase->execute($id, new UpdateBulletinEvaluationTypeDTO($data));

        return redirect()->route('school.academic.bulletins.evaluation-types')->with('success', "Type d'évaluation mis à jour avec succès.");
    }

    public function destroyEvaluationType($id, DeleteBulletinEvaluationTypeUseCase $useCase)
    {
        abort_if(auth()->user()->teacher, 403, "Seul un administrateur peut modifier les coefficients.");

        $useCase->execute($id);
        return redirect()->route('school.academic.bulletins.evaluation-types')->with('success', "Type d'évaluation supprimé avec succès.");
    }

    private function validateEvaluationType(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'coefficient' => ['required', 'numeric', 'min:0.25', 'max:10'],
            'linked_homework_type' => ['nullable', 'in:devoir_maison,interrogation'],
        ]);
    }

    public function print($studentId, BulletinGradeRepositoryInterface $gradeRepository, BulletinTemplateRepositoryInterface $templateRepository, BulletinPublicationRepositoryInterface $publicationRepository, BulletinStatsService $stats)
    {
        $student = Student::where('school_id', auth()->user()->school_id)->with('academicClass')->findOrFail($studentId);
        if ($student->academic_class_id) {
            $this->ensureHeadTeacherAccess($student->academic_class_id);
        }
        $school = auth()->user()->school;
        $currentSemester = $stats->currentSemester(auth()->user()->school_id);
        $template = $templateRepository->findOrDefault(auth()->user()->school_id);

        $grades = collect();
        $displayGrades = collect();
        $classGrades = collect();
        $average = null;
        $rank = null;
        $classSize = 0;
        $isLastTerm = false;
        $annualAverage = null;
        $isPublished = false;

        if ($currentSemester && $student->academic_class_id) {
            $publication = $publicationRepository->findOrCreate($student->academic_class_id, $currentSemester->id);
            $isPublished = $publication->status === BulletinPublication::STATUS_PUBLISHED;
        }

        if ($currentSemester && $student->academic_class_id && $isPublished) {
            $classGrades = $gradeRepository->forClassAndSemester($student->academic_class_id, $currentSemester->id);
            $classGrades = $stats->mergeHomeworkGrades($classGrades, $student->academic_class_id, $currentSemester->id);
            $classGrades = $stats->filterPublishedSubjects($classGrades, $student->academic_class_id, $currentSemester->id);
            $rawStudentGrades = $classGrades->where('student_id', $student->id);
            $grades = $stats->aggregateToSubjectGrades($rawStudentGrades);
            $average = $stats->studentAverage($grades);

            // Every subject taught in the student's class appears on the printed bulletin,
            // even ones with no grade entered yet — shown blank rather than silently omitted,
            // so a parent can see which matières are still pending. Real moyenne/rang above
            // are computed only from actual grades, never padded by these blank rows.
            $displayGrades = $student->academicClass->subjects->map(function ($subject) use ($grades) {
                return $grades->firstWhere('subject_id', $subject->id) ?? (object) [
                    'subject_id' => $subject->id,
                    'subject' => $subject,
                    'teacher' => null,
                    'score' => null,
                    'remark' => null,
                ];
            });

            $ranking = $stats->classRanking($student->academic_class_id, $currentSemester->id, $classGrades);
            $studentRow = collect($ranking)->firstWhere('student.id', $student->id);
            $rank = $studentRow['rank'] ?? null;
            $classSize = count($ranking);

            $isLastTerm = $stats->isLastTermOfYear(auth()->user()->school_id, $currentSemester);
            $annualAverage = $isLastTerm ? $stats->annualFinalAverage(auth()->user()->school_id, $student, $currentSemester) : null;
        }

        $since = $currentSemester?->start_date ?? now()->subMonths(4)->toDateString();
        $records = AttendanceRecord::where('student_id', $student->id)->where('date', '>=', $since)->get();
        $unjustifiedAbsences = $records->where('status', 'absent')->filter(fn ($r) => !$r->justified)->count();
        $lateCount = $records->where('status', 'late')->count();

        return view('SchoolDashboard::bulletins.print', compact(
            'student', 'school', 'currentSemester', 'template', 'grades', 'displayGrades', 'classGrades', 'average', 'rank', 'classSize',
            'unjustifiedAbsences', 'lateCount', 'stats', 'isLastTerm', 'annualAverage', 'isPublished'
        ));
    }
}
