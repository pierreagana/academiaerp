<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\ExamSession;
use App\Modules\Academic\Domain\Models\ExamSessionStudent;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\SuperAdmin\Domain\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Self-service exam-results entry: the school picks an official exam
 * (bac/bepc/cepe/bts), the class(es) that sat it, and which of those
 * students passed. Everything else — presented count, admitted count, the
 * success rate shown to parents in School Track, and the school's annual
 * progression — is derived from this, never typed in as a raw percentage.
 */
class ExamResultsController extends Controller
{
    public function index(Request $request)
    {
        /** @var School $school */
        $school = auth()->user()->school;
        $year = School::currentAcademicYear();

        $availableTypes = $school->availableExamTypes();
        $sessions = $school->examSessions()
            ->where('academic_year', $year)
            ->get()
            ->keyBy('exam_type');

        $history = $school->examSessions()
            ->whereNotNull('validated_at')
            ->where('academic_year', '!=', $year)
            ->orderByDesc('academic_year')
            ->get();

        return view('SchoolDashboard::dashboard.exam_results_index', [
            'school' => $school,
            'year' => $year,
            'labels' => ExamSession::LABELS,
            'availableTypes' => $availableTypes,
            'sessions' => $sessions,
            'history' => $history,
            'progressionAnnuelle' => $school->computedProgressionAnnuelle(),
        ]);
    }

    public function create(Request $request)
    {
        /** @var School $school */
        $school = auth()->user()->school;
        $type = $request->query('type');

        abort_unless(in_array($type, array_keys(ExamSession::LABELS), true), 404);
        abort_unless(in_array($type, $school->availableExamTypes(), true), 403, "Aucune classe de {$type} n'a été trouvée pour cet établissement.");

        $year = School::currentAcademicYear();
        $levels = ExamSession::levelsForType($type);

        $classes = AcademicClass::where('school_id', $school->id)
            ->whereIn('level', $levels)
            ->withCount('students')
            ->orderBy('name')
            ->get();

        // Preload every candidate student (id, name, class) up front so the
        // class/student picker is fully client-side (Alpine) — no round
        // trips as the school checks classes and picks admitted students.
        $students = Student::whereIn('academic_class_id', $classes->pluck('id'))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'roll_number', 'academic_class_id']);

        $existingSession = $school->examSessions()
            ->where('exam_type', $type)
            ->where('academic_year', $year)
            ->first();

        $selectedClassIds = $existingSession ? $existingSession->classes()->pluck('academic_classes.id')->all() : [];
        $admittedStudentIds = $existingSession
            ? $existingSession->results()->where('is_admitted', true)->pluck('student_id')->all()
            : [];

        return view('SchoolDashboard::dashboard.exam_results_create', [
            'type' => $type,
            'label' => ExamSession::LABELS[$type],
            'year' => $year,
            'classes' => $classes,
            'students' => $students,
            'selectedClassIds' => $selectedClassIds,
            'admittedStudentIds' => $admittedStudentIds,
            'isEditing' => $existingSession !== null,
        ]);
    }

    public function store(Request $request)
    {
        /** @var School $school */
        $school = auth()->user()->school;

        $validated = $request->validate([
            'exam_type' => 'required|in:' . implode(',', array_keys(ExamSession::LABELS)),
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'integer|exists:academic_classes,id',
            'admitted_student_ids' => 'nullable|array',
            'admitted_student_ids.*' => 'integer|exists:students,id',
        ]);

        $classIds = AcademicClass::where('school_id', $school->id)
            ->whereIn('id', $validated['class_ids'])
            ->whereIn('level', ExamSession::levelsForType($validated['exam_type']))
            ->pluck('id');

        abort_if($classIds->isEmpty(), 422, "Aucune classe valide pour cet examen.");

        $roster = Student::where('school_id', $school->id)
            ->whereIn('academic_class_id', $classIds)
            ->pluck('id');

        abort_if($roster->isEmpty(), 422, "Les classes sélectionnées n'ont aucun élève.");

        $admittedIds = collect($validated['admitted_student_ids'] ?? [])
            ->intersect($roster)
            ->values();

        $year = School::currentAcademicYear();

        DB::transaction(function () use ($school, $validated, $classIds, $roster, $admittedIds, $year) {
            $session = ExamSession::updateOrCreate(
                ['school_id' => $school->id, 'exam_type' => $validated['exam_type'], 'academic_year' => $year],
                [
                    'presented_count' => $roster->count(),
                    'admitted_count' => $admittedIds->count(),
                    'validated_at' => now(),
                    'validated_by' => auth()->id(),
                ]
            );

            $session->classes()->sync($classIds);

            $session->results()->delete();
            $rows = $roster->map(fn ($studentId) => [
                'exam_session_id' => $session->id,
                'student_id' => $studentId,
                'is_admitted' => $admittedIds->contains($studentId),
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();
            ExamSessionStudent::insert($rows);
        });

        return redirect()->route('school.exam-results.index')->with('success', 'Résultats validés — le taux de réussite a été mis à jour.');
    }
}
