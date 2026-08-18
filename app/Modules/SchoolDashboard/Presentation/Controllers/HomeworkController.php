<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Room;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Bulletin\Application\Services\BulletinStatsService;
use App\Modules\Homework\Application\DTOs\CreateHomeworkAssignmentDTO;
use App\Modules\Homework\Application\Services\HomeworkStatsService;
use App\Modules\Homework\Application\UseCases\CreateHomeworkAssignmentUseCase;
use App\Modules\Homework\Application\UseCases\MarkAttendanceUseCase;
use App\Modules\Homework\Application\UseCases\SaveSubmissionsUseCase;
use App\Modules\Homework\Application\UseCases\StartSessionUseCase;
use App\Modules\Homework\Application\UseCases\StopSessionUseCase;
use App\Modules\Homework\Domain\Models\HomeworkAssignment;
use App\Modules\Homework\Domain\Models\HomeworkAttendance;
use App\Modules\Homework\Domain\Models\HomeworkSubmission;
use Illuminate\Http\Request;

class HomeworkController extends Controller
{
    public function homeworkIndex(Request $request, HomeworkStatsService $stats)
    {
        $teacher = auth()->user()->teacher;
        $classes = $teacher ? $teacher->classes : AcademicClass::where('school_id', auth()->user()->school_id)->get();
        $classId = $request->get('class_id');
        $subjectId = $request->get('subject_id');

        $selectedClass = $classId ? $classes->firstWhere('id', (int) $classId) : null;
        $subjects = $selectedClass ? $selectedClass->subjects()->orderBy('name')->get() : ($teacher?->subjects ?? collect());
        if ($teacher) {
            $subjects = $subjects->filter(fn ($subject) => $teacher->teachesSubject($subject->id))->values();
        }

        $assignments = $stats->homeworkList($teacher, auth()->user()->school_id, $classId ? (int) $classId : null, $subjectId ? (int) $subjectId : null);
        $assignments->each(fn ($a) => $a->setAttribute('progress', $stats->submissionProgress($a)));
        $assignments->each(fn ($a) => $a->setAttribute('statusLabel', $stats->homeworkStatusLabel($a)));

        $toGrade = $stats->recentSubmissionsToGrade($teacher, auth()->user()->school_id);

        return view('SchoolDashboard::homework.homework_index', compact(
            'classes', 'subjects', 'selectedClass', 'classId', 'subjectId', 'assignments', 'toGrade'
        ));
    }

    public function homeworkCreate()
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 403);

        $classes = $teacher->classes;
        $subjects = $teacher->subjects;

        return view('SchoolDashboard::homework.homework_create', compact('classes', 'subjects'));
    }

    public function storeHomework(Request $request, CreateHomeworkAssignmentUseCase $useCase, BulletinStatsService $bulletinStats)
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 403);

        $data = $request->validate([
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['required', 'date'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:1000'],
        ]);

        abort_if(!$teacher->teachesSubject((int) $data['subject_id']), 403, "Vous ne pouvez créer un devoir que pour vos propres matières.");
        abort_if(!$teacher->classes->contains('id', (int) $data['academic_class_id']), 403, "Cette classe ne vous est pas assignée.");

        $semester = $bulletinStats->currentSemester(auth()->user()->school_id);
        abort_unless($semester, 422, 'Aucun semestre actif défini pour cette école.');

        $useCase->execute(new CreateHomeworkAssignmentDTO([
            'school_id' => auth()->user()->school_id,
            'branch_id' => auth()->user()->activeBranchId(),
            'academic_class_id' => $data['academic_class_id'],
            'subject_id' => $data['subject_id'],
            'teacher_id' => $teacher->id,
            'semester_id' => $semester->id,
            'title' => $data['title'],
            'type' => HomeworkAssignment::TYPE_HOMEWORK,
            'description' => $data['description'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'max_score' => $data['max_score'],
        ]));

        return redirect()->route('school.academic.homework.homework')->with('success', 'Devoir créé avec succès !');
    }

    public function testsIndex(HomeworkStatsService $stats)
    {
        $teacher = auth()->user()->teacher;
        $schoolId = auth()->user()->school_id;

        $assignments = $stats->testsList($teacher, $schoolId);
        $assignments->each(fn ($a) => $a->setAttribute('liveStatus', $a->liveStatus()));

        $upcomingWeek = $stats->upcomingWeek($teacher, $schoolId);
        $toCorrect = $stats->toCorrect($teacher, $schoolId);

        return view('SchoolDashboard::homework.tests_index', compact('assignments', 'upcomingWeek', 'toCorrect'));
    }

    public function testsCreate()
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 403);

        $classes = $teacher->classes;
        $subjects = $teacher->subjects;
        $rooms = Room::where('school_id', auth()->user()->school_id)->orderBy('name')->get();

        return view('SchoolDashboard::homework.tests_create', compact('classes', 'subjects', 'rooms'));
    }

    public function storeTest(Request $request, CreateHomeworkAssignmentUseCase $useCase, BulletinStatsService $bulletinStats)
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 403);

        $data = $request->validate([
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:1000'],
        ]);

        abort_if(!$teacher->teachesSubject((int) $data['subject_id']), 403, "Vous ne pouvez planifier une évaluation que pour vos propres matières.");
        abort_if(!$teacher->classes->contains('id', (int) $data['academic_class_id']), 403, "Cette classe ne vous est pas assignée.");

        $semester = $bulletinStats->currentSemester(auth()->user()->school_id);
        abort_unless($semester, 422, 'Aucun semestre actif défini pour cette école.');

        $useCase->execute(new CreateHomeworkAssignmentDTO([
            'school_id' => auth()->user()->school_id,
            'branch_id' => auth()->user()->activeBranchId(),
            'academic_class_id' => $data['academic_class_id'],
            'subject_id' => $data['subject_id'],
            'teacher_id' => $teacher->id,
            'semester_id' => $semester->id,
            'room_id' => $data['room_id'] ?? null,
            'title' => $data['title'],
            'type' => HomeworkAssignment::TYPE_TEST,
            'description' => $data['description'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'duration_minutes' => $data['duration_minutes'],
            'max_score' => $data['max_score'],
        ]));

        return redirect()->route('school.academic.homework.tests')->with('success', 'Évaluation planifiée avec succès !');
    }

    public function submissions(int $homework, HomeworkStatsService $stats)
    {
        $assignment = $this->resolveOwnedAssignment($homework);

        $students = Student::where('academic_class_id', $assignment->academic_class_id)->where('status', 'active')->orderBy('first_name')->get();
        $existingSubmissions = HomeworkSubmission::where('homework_assignment_id', $assignment->id)->get()->keyBy('student_id');

        return view('SchoolDashboard::homework.submissions', compact('assignment', 'students', 'existingSubmissions'));
    }

    public function storeSubmissions(int $homework, Request $request, SaveSubmissionsUseCase $useCase)
    {
        $assignment = $this->resolveOwnedAssignment($homework);

        $data = $request->validate([
            'entries' => ['required', 'array'],
            'entries.*.status' => ['required', 'in:non_remis,remis'],
            'entries.*.score' => ['nullable', 'numeric', 'min:0', 'max:' . $assignment->max_score],
            'entries.*.feedback' => ['nullable', 'string', 'max:1000'],
            'entries.*.file' => ['nullable', 'file', 'max:5120'],
        ]);

        $entries = $data['entries'];
        foreach ($entries as $studentId => &$entry) {
            if ($request->hasFile("entries.$studentId.file")) {
                $entry['file_path'] = $request->file("entries.$studentId.file")->store('homework/submissions', 'public');
            }
        }
        unset($entry);

        $useCase->execute($assignment->id, $entries);

        return redirect()->route('school.academic.homework.submissions', $assignment->id)->with('success', 'Rendus enregistrés avec succès !');
    }

    public function live(int $homework, HomeworkStatsService $stats)
    {
        $assignment = $this->resolveOwnedAssignment($homework);
        abort_unless($assignment->type === HomeworkAssignment::TYPE_TEST, 404);

        $students = Student::where('academic_class_id', $assignment->academic_class_id)->where('status', 'active')->orderBy('first_name')->get();
        $existingAttendance = HomeworkAttendance::where('homework_assignment_id', $assignment->id)->get()->keyBy('student_id');
        $existingSubmissions = HomeworkSubmission::where('homework_assignment_id', $assignment->id)->get()->keyBy('student_id');
        $counts = $stats->liveAttendanceCounts($assignment);
        $history = $stats->testsList(auth()->user()->teacher, auth()->user()->school_id);

        return view('SchoolDashboard::homework.live', compact('assignment', 'students', 'existingAttendance', 'existingSubmissions', 'counts', 'history'));
    }

    public function start(int $homework, StartSessionUseCase $useCase)
    {
        $assignment = $this->resolveOwnedAssignment($homework);
        $useCase->execute($assignment);

        return redirect()->route('school.academic.homework.live', $assignment->id);
    }

    public function stop(int $homework, StopSessionUseCase $useCase)
    {
        $assignment = $this->resolveOwnedAssignment($homework);
        $useCase->execute($assignment);

        return redirect()->route('school.academic.homework.live', $assignment->id);
    }

    public function storeAttendance(int $homework, Request $request, MarkAttendanceUseCase $useCase)
    {
        $assignment = $this->resolveOwnedAssignment($homework);

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'status' => ['required', 'in:present,absent,late'],
        ]);

        abort_unless(Student::where('id', $data['student_id'])->where('academic_class_id', $assignment->academic_class_id)->exists(), 404);

        $useCase->execute($assignment->id, (int) $data['student_id'], $data['status']);

        return response()->json(['ok' => true]);
    }

    public function storeSubmissionMark(int $homework, Request $request)
    {
        $assignment = $this->resolveOwnedAssignment($homework);

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'status' => ['required', 'in:non_remis,remis'],
        ]);

        abort_unless(Student::where('id', $data['student_id'])->where('academic_class_id', $assignment->academic_class_id)->exists(), 404);

        HomeworkSubmission::updateOrCreate(
            ['homework_assignment_id' => $assignment->id, 'student_id' => $data['student_id']],
            ['status' => $data['status'], 'submitted_at' => $data['status'] === 'remis' ? now() : null]
        );

        return response()->json(['ok' => true]);
    }

    public function attendanceCounts(int $homework, HomeworkStatsService $stats)
    {
        $assignment = $this->resolveOwnedAssignment($homework);

        return response()->json($stats->liveAttendanceCounts($assignment));
    }

    private function resolveOwnedAssignment(int $id): HomeworkAssignment
    {
        $assignment = HomeworkAssignment::with(['academicClass', 'subject', 'room'])->find($id);
        abort_unless($assignment, 404);

        $teacher = auth()->user()->teacher;
        abort_if($teacher && $assignment->teacher_id !== $teacher->id, 403, "Cette évaluation ne vous appartient pas.");

        return $assignment;
    }
}
