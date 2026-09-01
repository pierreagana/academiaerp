<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Academic\Application\Services\ParentPortalAccountService;
use App\Modules\Academic\Application\UseCases\Semester\CreateSemesterUseCase;
use App\Modules\Academic\Application\UseCases\Semester\UpdateSemesterUseCase;
use App\Modules\Academic\Application\UseCases\Semester\DeleteSemesterUseCase;
use App\Modules\Academic\Application\DTOs\CreateSemesterDTO;
use App\Modules\Academic\Application\DTOs\UpdateSemesterDTO;

use App\Modules\Academic\Application\UseCases\AcademicClass\CreateAcademicClassUseCase;
use App\Modules\Academic\Application\UseCases\AcademicClass\UpdateAcademicClassUseCase;
use App\Modules\Academic\Application\UseCases\AcademicClass\DeleteAcademicClassUseCase;
use App\Modules\Academic\Application\DTOs\CreateAcademicClassDTO;
use App\Modules\Academic\Application\DTOs\UpdateAcademicClassDTO;

use App\Modules\Academic\Application\UseCases\Language\CreateLanguageUseCase;
use App\Modules\Academic\Application\UseCases\Language\UpdateLanguageUseCase;
use App\Modules\Academic\Application\UseCases\Language\DeleteLanguageUseCase;
use App\Modules\Academic\Application\DTOs\CreateLanguageDTO;
use App\Modules\Academic\Application\DTOs\UpdateLanguageDTO;

use App\Modules\Academic\Application\UseCases\Subject\CreateSubjectUseCase;
use App\Modules\Academic\Application\UseCases\Subject\UpdateSubjectUseCase;
use App\Modules\Academic\Application\UseCases\Subject\DeleteSubjectUseCase;
use App\Modules\Academic\Application\DTOs\CreateSubjectDTO;
use App\Modules\Academic\Application\DTOs\UpdateSubjectDTO;

use App\Modules\Academic\Application\UseCases\TimetableBreak\CreateTimetableBreakUseCase;
use App\Modules\Academic\Application\UseCases\TimetableBreak\UpdateTimetableBreakUseCase;
use App\Modules\Academic\Application\UseCases\TimetableBreak\DeleteTimetableBreakUseCase;
use App\Modules\Academic\Application\DTOs\CreateTimetableBreakDTO;
use App\Modules\Academic\Application\DTOs\UpdateTimetableBreakDTO;

use App\Modules\Academic\Application\UseCases\Guardian\CreateGuardianUseCase;
use App\Modules\Academic\Application\UseCases\Guardian\UpdateGuardianUseCase;
use App\Modules\Academic\Application\UseCases\Guardian\DeleteGuardianUseCase;
use App\Modules\Academic\Application\DTOs\CreateTeacherDTO;
use App\Modules\Academic\Application\DTOs\UpdateTeacherDTO;
use App\Modules\Academic\Application\UseCases\CreateTeacherUseCase;
use App\Modules\Academic\Application\UseCases\UpdateTeacherUseCase;
use App\Modules\Academic\Application\UseCases\DeleteTeacherUseCase;
use App\Modules\Academic\Application\DTOs\CreateStaffDTO;
use App\Modules\Academic\Application\DTOs\UpdateStaffDTO;
use App\Modules\Academic\Application\UseCases\CreateStaffUseCase;
use App\Modules\Academic\Application\UseCases\UpdateStaffUseCase;
use App\Modules\Academic\Application\UseCases\DeleteStaffUseCase;
use App\Modules\Academic\Application\DTOs\CreateGuardianDTO;
use App\Modules\Academic\Application\DTOs\UpdateGuardianDTO;

use App\Modules\Academic\Application\UseCases\Student\CreateStudentUseCase;
use App\Modules\Academic\Application\UseCases\Student\UpdateStudentUseCase;
use App\Modules\Academic\Application\UseCases\Student\DeleteStudentUseCase;
use App\Modules\Academic\Application\DTOs\CreateStudentDTO;
use App\Modules\Academic\Application\DTOs\UpdateStudentDTO;

use App\Modules\Academic\Application\UseCases\StudentClassMovement\RecordStudentClassMovementUseCase;
use App\Modules\Academic\Domain\Models\StudentClassMovement;
use App\Modules\Academic\Domain\Repositories\StudentClassMovementRepositoryInterface;
use App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface;
use App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface;
use App\Modules\Academic\Domain\Models\Student;

class AcademicController extends Controller
{
    public function languages(Request $request, \App\Modules\Academic\Domain\Repositories\LanguageRepositoryInterface $repository)
    {
        $languages = $repository->all();
        $editLanguage = null;
        if ($request->has('edit')) {
            $editLanguage = $repository->find($request->query('edit'));
        }
        return view('SchoolDashboard::academic.languages', compact('languages', 'editLanguage'));
    }

    public function storeLanguage(Request $request, CreateLanguageUseCase $useCase)
    {
        $messages = [
            'name.required' => 'Le nom de la langue est obligatoire.',
            'name.unique' => 'Cette langue existe déjà.',
            'code.required' => 'Le code de la langue est obligatoire.',
            'code.unique' => 'Ce code de langue existe déjà.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('languages', 'name')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:10', \Illuminate\Validation\Rule::unique('languages', 'code')->whereNull('deleted_at')],
        ], $messages);

        $dto = new CreateLanguageDTO($data);
        $useCase->execute($dto);

        return redirect()->route('school.academic.languages')->with('success', 'Langue créée avec succès !');
    }

    public function updateLanguage($id, Request $request, UpdateLanguageUseCase $useCase)
    {
        $messages = [
            'name.required' => 'Le nom de la langue est obligatoire.',
            'name.unique' => 'Cette langue existe déjà.',
            'code.required' => 'Le code de la langue est obligatoire.',
            'code.unique' => 'Ce code de langue existe déjà.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('languages', 'name')->ignore($id)->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:10', \Illuminate\Validation\Rule::unique('languages', 'code')->ignore($id)->whereNull('deleted_at')],
        ], $messages);

        $dto = new UpdateLanguageDTO($data);
        $useCase->execute($id, $dto);

        return redirect()->route('school.academic.languages')->with('success', 'Langue mise à jour avec succès !');
    }

    public function destroyLanguage($id, DeleteLanguageUseCase $useCase)
    {
        $useCase->execute($id);
        return redirect()->route('school.academic.languages')->with('success', 'Langue supprimée avec succès !');
    }

    public function classes(Request $request, \App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $repository)
    {
        $classes = $repository->all();
        $editClass = null;
        if ($request->has('edit')) {
            $editClass = $repository->find($request->query('edit'));
        }
        return view('SchoolDashboard::academic.classes', compact('classes', 'editClass'));
    }

    public function storeClass(Request $request, CreateAcademicClassUseCase $useCase)
    {
        $messages = [
            'name.required' => 'Le nom de la classe est obligatoire.',
            'name.unique' => 'Ce nom de classe existe déjà.',
            'level.required' => 'Le niveau de la classe est obligatoire.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('academic_classes', 'name')->whereNull('deleted_at')],
            'level' => ['required', 'string', 'in:' . implode(',', \App\Modules\Academic\Domain\Models\AcademicClass::allLevels())],
        ], $messages);

        // Cycle is always derived from the chosen level (never set independently), so
        // it can never drift out of sync with it — see AcademicClass::cycleForLevel().
        $data['cycle'] = \App\Modules\Academic\Domain\Models\AcademicClass::cycleForLevel($data['level']);

        $dto = new CreateAcademicClassDTO($data);

        try {
            $useCase->execute($dto);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['name' => $e->getMessage()])->withInput();
        }

        return redirect()->route('school.academic.classes')->with('success', 'Classe créée avec succès !');
    }

    public function updateClass($id, Request $request, UpdateAcademicClassUseCase $useCase)
    {
        $messages = [
            'name.required' => 'Le nom de la classe est obligatoire.',
            'name.unique' => 'Ce nom de classe existe déjà.',
            'level.required' => 'Le niveau de la classe est obligatoire.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('academic_classes', 'name')->ignore($id)->whereNull('deleted_at')],
            'level' => ['required', 'string', 'in:' . implode(',', \App\Modules\Academic\Domain\Models\AcademicClass::allLevels())],
        ], $messages);

        $data['cycle'] = \App\Modules\Academic\Domain\Models\AcademicClass::cycleForLevel($data['level']);

        $dto = new UpdateAcademicClassDTO($data);
        $useCase->execute($id, $dto);

        return redirect()->route('school.academic.classes')->with('success', 'Classe mise à jour avec succès !');
    }

    public function destroyClass($id, DeleteAcademicClassUseCase $useCase)
    {
        $useCase->execute($id);
        return redirect()->route('school.academic.classes')->with('success', 'Classe supprimée avec succès !');
    }

    public function semesters(Request $request, \App\Modules\Academic\Domain\Repositories\SemesterRepositoryInterface $repository)
    {
        $semesters = $repository->all();
        $editSemester = null;
        if ($request->has('edit')) {
            $editSemester = $repository->find($request->query('edit'));
        }
        $academicYears = \App\Modules\SuperAdmin\Domain\Models\School::getAvailableAcademicYears();
        $currentAcademicYear = \App\Modules\SuperAdmin\Domain\Models\School::currentAcademicYear();
        return view('SchoolDashboard::academic.semesters', compact('semesters', 'editSemester', 'academicYears', 'currentAcademicYear'));
    }

    public function storeSemester(Request $request, CreateSemesterUseCase $useCase)
    {
        $messages = [
            'name.required' => 'Le nom du semestre est obligatoire.',
            'name.unique' => 'Ce nom de semestre existe déjà. Veuillez en choisir un autre.',
            'start_date.required' => 'La date de début est obligatoire.',
            'end_date.required' => 'La date de fin est obligatoire.',
            'end_date.after' => 'La date de fin doit être strictement postérieure à la date de début.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('semesters', 'name')->whereNull('deleted_at')],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'academic_year' => ['nullable', 'string', 'max:20', \Illuminate\Validation\Rule::in(\App\Modules\SuperAdmin\Domain\Models\School::getAvailableAcademicYears())],
            'term_number' => ['nullable', 'integer', 'min:1', 'max:3'],
        ], $messages);
        $today = \Carbon\Carbon::now()->startOfDay();
        $startDate = \Carbon\Carbon::parse($data['start_date'])->startOfDay();
        $endDate = \Carbon\Carbon::parse($data['end_date'])->startOfDay();
        $data['is_current'] = $today->between($startDate, $endDate);

        $dto = new CreateSemesterDTO($data);
        $useCase->execute($dto);

        return redirect()->route('school.academic.semesters')->with('success', 'Semestre créé avec succès !');
    }

    public function updateSemester($id, Request $request, UpdateSemesterUseCase $useCase)
    {
        $messages = [
            'name.required' => 'Le nom du semestre est obligatoire.',
            'name.unique' => 'Ce nom de semestre existe déjà. Veuillez en choisir un autre.',
            'start_date.required' => 'La date de début est obligatoire.',
            'end_date.required' => 'La date de fin est obligatoire.',
            'end_date.after' => 'La date de fin doit être strictement postérieure à la date de début.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('semesters', 'name')->ignore($id)->whereNull('deleted_at')],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'academic_year' => ['nullable', 'string', 'max:20', \Illuminate\Validation\Rule::in(\App\Modules\SuperAdmin\Domain\Models\School::getAvailableAcademicYears())],
            'term_number' => ['nullable', 'integer', 'min:1', 'max:3'],
        ], $messages);
        $today = \Carbon\Carbon::now()->startOfDay();
        $startDate = \Carbon\Carbon::parse($data['start_date'])->startOfDay();
        $endDate = \Carbon\Carbon::parse($data['end_date'])->startOfDay();
        $data['is_current'] = $today->between($startDate, $endDate);

        $dto = new UpdateSemesterDTO($data);
        $useCase->execute($id, $dto);

        return redirect()->route('school.academic.semesters')->with('success', 'Semestre mis à jour avec succès !');
    }

    public function destroySemester($id, DeleteSemesterUseCase $useCase)
    {
        $useCase->execute($id);
        return redirect()->route('school.academic.semesters')->with('success', 'Semestre supprimé avec succès !');
    }

    public function subjects(Request $request, \App\Modules\Academic\Domain\Repositories\SubjectRepositoryInterface $repository, \App\Modules\Academic\Domain\Repositories\LanguageRepositoryInterface $languageRepository)
    {
        $subjects = $repository->all();
        $languages = $languageRepository->all();
        $editSubject = null;
        if ($request->has('edit')) {
            $editSubject = $repository->find($request->query('edit'));
        }
        return view('SchoolDashboard::academic.subjects', compact('subjects', 'languages', 'editSubject'));
    }

    public function storeSubject(Request $request, CreateSubjectUseCase $useCase)
    {
        $messages = [
            'name.required' => 'Le nom de la matière est obligatoire.',
            'name.unique' => 'Cette matière existe déjà.',
            'code.required' => 'Le code de la matière est obligatoire.',
            'code.unique' => 'Ce code de matière existe déjà.',
            'type.required' => 'Le type de matière est obligatoire.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('subjects', 'name')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique('subjects', 'code')->whereNull('deleted_at')],
            'type' => ['required', 'string', 'in:theory,practical,both'],
            'color' => ['nullable', 'string', 'max:20'],
            'coefficient' => ['required', 'numeric', 'min:0.5'],
            'language_id' => ['required', 'exists:languages,id'],
        ], $messages);

        $dto = new CreateSubjectDTO($data);
        $useCase->execute($dto);

        return redirect()->route('school.academic.subjects')->with('success', 'Matière créée avec succès !');
    }

    public function updateSubject($id, Request $request, UpdateSubjectUseCase $useCase)
    {
        $messages = [
            'name.required' => 'Le nom de la matière est obligatoire.',
            'name.unique' => 'Cette matière existe déjà.',
            'code.required' => 'Le code de la matière est obligatoire.',
            'code.unique' => 'Ce code de matière existe déjà.',
            'type.required' => 'Le type de matière est obligatoire.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('subjects', 'name')->ignore($id)->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique('subjects', 'code')->ignore($id)->whereNull('deleted_at')],
            'type' => ['required', 'string', 'in:theory,practical,both'],
            'color' => ['nullable', 'string', 'max:20'],
            'coefficient' => ['required', 'numeric', 'min:0.5'],
            'language_id' => ['required', 'exists:languages,id'],
        ], $messages);

        $dto = new UpdateSubjectDTO($data);
        $useCase->execute($id, $dto);

        return redirect()->route('school.academic.subjects')->with('success', 'Matière mise à jour avec succès !');
    }

    public function destroySubject($id, DeleteSubjectUseCase $useCase)
    {
        $useCase->execute($id);
        return redirect()->route('school.academic.subjects')->with('success', 'Matière supprimée avec succès !');
    }

    public function timetableBreaks(
        Request $request,
        \App\Modules\Academic\Domain\Repositories\TimetableBreakRepositoryInterface $repository,
        \App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository
    ) {
        $classes = $classRepository->all();
        $classId = $request->get('class_id');
        if ($classId && !$classes->contains('id', (int) $classId)) {
            $classId = null;
        }
        if (!$classId && $classes->isNotEmpty()) {
            $classId = (string) $classes->first()->id;
        }

        $breaks = $repository->allForClass($classId);
        $editBreak = null;
        if ($request->has('edit')) {
            $editBreak = $repository->find($request->query('edit'));
        }
        $colors = \App\Modules\Academic\Domain\Models\TimetableBreak::availableColors();
        $days = \App\Modules\Academic\Domain\Models\TimetableBreak::days();
        return view('SchoolDashboard::academic.timetable_breaks', compact('breaks', 'editBreak', 'colors', 'days', 'classes', 'classId'));
    }

    public function storeTimetableBreak(Request $request, CreateTimetableBreakUseCase $useCase)
    {
        $data = $this->validateTimetableBreak($request);

        $dto = new CreateTimetableBreakDTO($data);
        $useCase->execute($dto);

        return redirect()->route('school.academic.timetable.breaks', ['class_id' => $data['academic_class_id']])->with('success', 'Pause créée avec succès !');
    }

    public function updateTimetableBreak($id, Request $request, UpdateTimetableBreakUseCase $useCase)
    {
        $data = $this->validateTimetableBreak($request, $id);

        $dto = new UpdateTimetableBreakDTO($data);
        $useCase->execute($id, $dto);

        return redirect()->route('school.academic.timetable.breaks', ['class_id' => $data['academic_class_id']])->with('success', 'Pause mise à jour avec succès !');
    }

    public function destroyTimetableBreak($id, DeleteTimetableBreakUseCase $useCase)
    {
        $classId = \App\Modules\Academic\Domain\Models\TimetableBreak::where('school_id', auth()->user()->school_id)->findOrFail($id)->academic_class_id;
        $useCase->execute($id);
        return redirect()->route('school.academic.timetable.breaks', ['class_id' => $classId])->with('success', 'Pause supprimée avec succès !');
    }

    private function validateTimetableBreak(Request $request, $ignoreId = null): array
    {
        $messages = [
            'name.required' => 'Le nom de la pause est obligatoire.',
            'academic_class_id.required' => 'La classe est obligatoire.',
            'day_of_week.required' => 'Le jour est obligatoire.',
            'color.in' => 'Couleur invalide.',
            'end_time.after' => 'L\'heure de fin doit être postérieure à l\'heure de début.',
        ];

        $classIds = \App\Modules\Academic\Domain\Models\AcademicClass::where('school_id', auth()->user()->school_id)->pluck('id');

        return $request->validate([
            'academic_class_id' => ['required', \Illuminate\Validation\Rule::in($classIds)],
            'day_of_week' => ['required', \Illuminate\Validation\Rule::in(array_keys(\App\Modules\Academic\Domain\Models\TimetableBreak::days()))],
            'name' => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('timetable_breaks', 'name')
                    ->where('school_id', auth()->user()->school_id)
                    ->where('academic_class_id', $request->input('academic_class_id'))
                    ->where('day_of_week', $request->input('day_of_week'))
                    ->ignore($ignoreId)
                    ->whereNull('deleted_at'),
            ],
            'color' => ['required', 'string', \Illuminate\Validation\Rule::in(\App\Modules\Academic\Domain\Models\TimetableBreak::availableColors())],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ], $messages);
    }

    public function syllabuses(
        \Illuminate\Http\Request $request,
        \App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository,
        \App\Modules\Academic\Domain\Repositories\SemesterRepositoryInterface $semesterRepository
    ) {
        $query = \App\Modules\Academic\Domain\Models\Syllabus::whereHas('academicClass', function ($q) {
            $q->where('school_id', auth()->user()->school_id);
        })->with(['academicClass', 'semester', 'subject'])->withCount('lessons');

        $teacher = auth()->user()->teacher;
        if ($teacher) {
            $query->whereIn('subject_id', $teacher->subjects->pluck('id'));
        }

        $syllabuses = $query->get();
        $classes = $classRepository->all()->loadCount('students');
        $semesters = $semesterRepository->all()->sortBy('term_number')->values();

        return view('SchoolDashboard::academic.syllabus', compact('syllabuses', 'classes', 'semesters'));
    }

    public function createSyllabus(
        \App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository,
        \App\Modules\Academic\Domain\Repositories\SemesterRepositoryInterface $semesterRepository,
        \App\Modules\Academic\Domain\Repositories\SubjectRepositoryInterface $subjectRepository
    ) {
        $classes = $classRepository->all();
        $semesters = $semesterRepository->all();
        $subjects = $subjectRepository->all();

        if ($teacher = auth()->user()->teacher) {
            $subjects = $subjects->filter(fn($subject) => $teacher->teachesSubject($subject->id))->values();
        }

        return view('SchoolDashboard::academic.syllabus_create', compact('classes', 'semesters', 'subjects'));
    }

    public function storeSyllabus(
        \Illuminate\Http\Request $request,
        \App\Modules\Academic\Application\UseCases\Syllabus\CreateSyllabusUseCase $useCase
    ) {
        $request->validate([
            'academic_class_ids' => 'required|array',
            'academic_class_ids.*' => 'exists:academic_classes,id',
            'semester_id' => 'required|exists:semesters,id',
            'subjects' => 'required|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        $teacher = auth()->user()->teacher;
        if ($teacher) {
            foreach ($request->subjects as $subjectId) {
                abort_if(!$teacher->teachesSubject((int) $subjectId), 403, "Vous ne pouvez assigner que vos propres matières.");
            }
        }

        $schoolId = auth()->user()->school_id;
        $created = 0;

        foreach ($request->academic_class_ids as $classId) {
            foreach ($request->subjects as $subjectId) {
                $exists = \App\Modules\Academic\Domain\Models\Syllabus::whereHas('academicClass', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                    ->where('academic_class_id', $classId)
                    ->where('semester_id', $request->semester_id)
                    ->where('subject_id', $subjectId)
                    ->exists();

                if (!$exists) {
                    $useCase->execute(new \App\Modules\Academic\Application\DTOs\CreateSyllabusDTO([
                        'academic_class_id' => $classId,
                        'semester_id' => $request->semester_id,
                        'subject_id' => $subjectId,
                    ]));
                    $created++;
                }
            }
        }

        return redirect()->route('school.academic.syllabuses')->with('success', "{$created} matière(s) assignée(s) avec succès au programme !");
    }

    public function destroySyllabus($id, \App\Modules\Academic\Domain\Repositories\SyllabusRepositoryInterface $repository)
    {
        $repository->delete($id);
        return redirect()->route('school.academic.syllabuses')->with('success', 'Matière retirée du programme avec succès !');
    }

    /**
     * Real cross-class room double-booking + teacher overload detection —
     * replaces the "Optimiseur IA" card that used to always show the same
     * 2 hardcoded fake alerts ("Salle 204", "Dr. Koffi") regardless of the
     * class being viewed, with a button that only toggled a local flag.
     */
    public function aiOptimizerCheck(
        \Illuminate\Http\Request $request,
        \App\Modules\SuperAdmin\Application\Services\AIService $aiService,
        \App\Modules\Academic\Domain\Repositories\RoomRepositoryInterface $roomRepository
    ) {
        $schoolId = auth()->user()->school_id;

        $rawSlots = \App\Modules\Academic\Domain\Models\Timetable::with(['room', 'teacher', 'academicClass'])
            ->whereHas('academicClass', fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', 'published')
            ->orderByDesc('valid_from')
            ->orderByDesc('id')
            ->get();

        // Each (class, day, time) slot can have multiple versioned rows
        // (valid_from-dated schedule changes) — keep only the newest one per
        // class, exactly like the main timetable view does. Without this,
        // an old superseded version of a class's own slot gets flagged as a
        // "conflict" against its own current version.
        $slots = $rawSlots->unique(fn ($t) => $t->academic_class_id . '|' . $t->day_of_week . '|' . $t->start_time)->values();

        $allRooms = $roomRepository->all();

        $roomConflicts = [];
        foreach ($slots->whereNotNull('room_id')->groupBy(fn ($t) => $t->room_id . '|' . $t->day_of_week) as $group) {
            if ($group->count() < 2) {
                continue;
            }
            $sorted = $group->sortBy('start_time')->values();
            for ($i = 0; $i < $sorted->count() - 1; $i++) {
                $entryA = $sorted[$i];
                $entryB = $sorted[$i + 1];
                $endA = \Carbon\Carbon::parse($entryA->end_time);
                $startB = \Carbon\Carbon::parse($entryB->start_time);
                if ($endA->gt($startB)) {
                    // Propose moving the second (later-created) entry into any
                    // room genuinely free for that exact class's whole slot,
                    // across every class's published schedule — not just a
                    // guess, a real lookup against the same $slots data.
                    $roomsBusyAtSlot = $slots
                        ->where('day_of_week', $entryB->day_of_week)
                        ->where('start_time', $entryB->start_time)
                        ->pluck('room_id')
                        ->filter()
                        ->unique();
                    $freeRoom = $allRooms->first(fn ($r) => !$roomsBusyAtSlot->contains($r->id));

                    $roomConflicts[] = [
                        'salle' => $entryA->room?->name ?? 'Salle',
                        'jour' => $entryA->day_of_week,
                        'classes' => [$entryA->academicClass?->name, $entryB->academicClass?->name],
                        'horaires' => [$entryA->start_time . '-' . $entryA->end_time, $entryB->start_time . '-' . $entryB->end_time],
                        'suggestion' => $freeRoom
                            ? "Déplacer {$entryB->academicClass?->name} vers {$freeRoom->name} ({$entryB->day_of_week} {$entryB->start_time}-{$entryB->end_time})"
                            : "Aucune salle libre sur ce créneau — décaler l'horaire de {$entryB->academicClass?->name}.",
                        'move_timetable_id' => $entryB->id,
                        'suggested_room_id' => $freeRoom?->id,
                    ];
                }
            }
        }

        $teacherOverload = [];
        foreach ($slots->whereNotNull('teacher_id')->groupBy(fn ($t) => $t->teacher_id . '|' . $t->day_of_week) as $group) {
            $totalHours = $group->sum(function ($t) {
                if (!$t->start_time || !$t->end_time) {
                    return 0;
                }
                return abs(\Carbon\Carbon::parse($t->end_time)->diffInMinutes(\Carbon\Carbon::parse($t->start_time))) / 60;
            });
            if ($totalHours > 6) {
                $teacherOverload[] = [
                    'enseignant' => $group->first()->teacher?->first_name . ' ' . $group->first()->teacher?->last_name,
                    'jour' => $group->first()->day_of_week,
                    'heures' => round($totalHours, 1),
                ];
            }
        }

        $hasIssues = !empty($roomConflicts) || !empty($teacherOverload);
        $summary = null;

        if ($hasIssues) {
            $systemPrompt = "Tu es un assistant de planification scolaire pour AcademiaERP. Tu résumes de vrais conflits d'emploi du temps détectés par une vérification réelle (pas une prédiction) — reste factuel, une phrase par problème.";
            $userPrompt = "Voici les vrais conflits détectés dans l'emploi du temps de l'établissement :\nConflits de salle : " . json_encode($roomConflicts, JSON_UNESCAPED_UNICODE)
                . "\nSurcharges enseignants (>6h le même jour) : " . json_encode($teacherOverload, JSON_UNESCAPED_UNICODE)
                . "\n\nRédige un résumé court (2-3 phrases max) en français.";

            $result = $aiService->generateText($systemPrompt, $userPrompt, 220);
            $summary = $result['success'] ? $result['text'] : null;
        }

        return response()->json([
            'has_issues' => $hasIssues,
            'room_conflicts' => $roomConflicts,
            'teacher_overload' => $teacherOverload,
            'summary' => $summary,
        ]);
    }

    public function timetable(
        \Illuminate\Http\Request $request,
        \App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository,
        \App\Modules\Academic\Domain\Repositories\RoomRepositoryInterface $roomRepository,
        \App\Modules\Academic\Domain\Repositories\TimetableBreakRepositoryInterface $timetableBreakRepository
    ) {
        $classes = $classRepository->all();
        $classId = $request->get('class_id');
        $schoolId = auth()->user()->school_id;

        $semesters = \App\Modules\Academic\Domain\Models\Semester::where('school_id', $schoolId)
            ->orderBy('start_date')
            ->get();

        $semesterId = $request->get('semester_id');
        $selectedSemester = null;
        if ($semesterId) {
            $selectedSemester = $semesters->firstWhere('id', (int) $semesterId);
        }
        if (!$selectedSemester) {
            $selectedSemester = $semesters->firstWhere('is_current', true) ?? $semesters->first();
        }

        // Build list of months for the selected semester
        $months = [];
        if ($selectedSemester) {
            $start = $selectedSemester->start_date ? \Carbon\Carbon::parse($selectedSemester->start_date) : now();
            $end = $selectedSemester->end_date ? \Carbon\Carbon::parse($selectedSemester->end_date) : $start->copy()->addMonths(4);

            $currentMonth = $start->copy()->startOfMonth();
            $endMonth = $end->copy()->startOfMonth();
            while ($currentMonth->lte($endMonth)) {
                $months[] = [
                    'label' => ucfirst($currentMonth->translatedFormat('F Y')),
                    'short' => ucfirst($currentMonth->translatedFormat('M')),
                    'value' => $currentMonth->format('Y-m'),
                    'date' => $currentMonth->format('Y-m-01'),
                ];
                $currentMonth->addMonth();
            }
        }

        // Determine selected month
        $requestedMonth = $request->get('month');
        $selectedMonth = null;
        if ($requestedMonth && collect($months)->contains('value', $requestedMonth)) {
            $selectedMonth = $requestedMonth;
        } else {
            $nowMonth = now()->format('Y-m');
            if (collect($months)->contains('value', $nowMonth)) {
                $selectedMonth = $nowMonth;
            } else {
                $selectedMonth = !empty($months) ? $months[0]['value'] : $nowMonth;
            }
        }

        // Target date for versioning lookup: 1st of the selected month
        $targetDate = $selectedMonth ? \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth() : now()->startOfMonth();

        // Initial date for week display: aligns the calendar week with the selected month
        if ($selectedMonth === now()->format('Y-m')) {
            $initialDate = now();
        } else {
            if ($targetDate->isWeekend()) {
                $initialDate = $targetDate->copy()->next(\Carbon\Carbon::MONDAY);
            } else {
                $initialDate = $targetDate->copy();
            }
        }

        $timetables = collect();
        $totalHours = 0;
        $uniqueTeachers = [];

        // Ensures the requested class actually belongs to this school before using its id below.
        if ($classId && !$classes->contains('id', (int) $classId)) {
            $classId = null;
        }

        $breaks = $timetableBreakRepository->allForClass($classId);

        if ($classId && $selectedSemester) {
            // Versioned query: load published entries valid for the chosen month (valid_from <= targetDate).
            $rawTimetables = \App\Modules\Academic\Domain\Models\Timetable::with(['subject', 'teacher', 'room'])
                ->where('academic_class_id', $classId)
                ->where('status', 'published')
                ->where(function ($q) use ($selectedSemester) {
                    $q->where('semester_id', $selectedSemester->id)
                        ->orWhereNull('semester_id');
                })
                ->where(function ($q) use ($targetDate) {
                    $q->whereNull('valid_from')
                        ->orWhere('valid_from', '<=', $targetDate->format('Y-m-d'));
                })
                ->orderByDesc('valid_from')
                ->orderByDesc('id')
                ->get();

            // If entries with the specific semester exist, prefer them over generic entries.
            $hasSpecificSemester = $rawTimetables->contains(fn($t) => $t->semester_id == $selectedSemester->id);
            if ($hasSpecificSemester) {
                $rawTimetables = $rawTimetables->filter(fn($t) => $t->semester_id == $selectedSemester->id);
            }

            // Deduplicate per (day_of_week, start_time): newest valid_from wins
            $timetables = $rawTimetables->unique(fn($t) => $t->day_of_week . '|' . $t->start_time)->values();

            foreach ($timetables as $t) {
                if ($t->start_time && $t->end_time) {
                    $start = \Carbon\Carbon::parse($t->start_time);
                    $end = \Carbon\Carbon::parse($t->end_time);
                    $totalHours += $start->diffInMinutes($end) / 60;
                }
                if ($t->teacher_id) {
                    $uniqueTeachers[$t->teacher_id] = true;
                }
            }
        }

        $teacherCount = count($uniqueTeachers);
        // Base de 35h par semaine (ex: 5 jours x 7h)
        $fillRate = min(100, round(($totalHours / 35) * 100));

        $stats = [
            'hours' => round($totalHours, 1),
            'fill_rate' => $fillRate,
            'teachers' => $teacherCount
        ];

        // Salles libres actuelles
        $currentDayIndex = date('N'); // 1 (Mon) to 7 (Sun)
        $days = [1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi', 7 => 'dimanche'];
        $currentDay = $days[$currentDayIndex] ?? 'lundi';
        $currentTime = date('H:i:s');

        $allRooms = $roomRepository->all();
        $todaysTimetables = \App\Modules\Academic\Domain\Models\Timetable::whereHas('academicClass', function ($q) {
            $q->where('school_id', auth()->user()->school_id);
        })
            ->where('day_of_week', $currentDay)
            ->whereNotNull('room_id')
            ->orderBy('start_time')
            ->get()
            ->groupBy('room_id');

        $freeRoomsData = [];

        foreach ($allRooms as $room) {
            $roomSchedules = $todaysTimetables->get($room->id, collect());

            // Cours actuellement en cours
            $currentClass = $roomSchedules->first(function ($t) use ($currentTime) {
                return $t->start_time <= $currentTime && $t->end_time > $currentTime;
            });

            if ($currentClass) {
                // Occupé actuellement
                $freeRoomsData[] = (object) [
                    'name' => $room->name,
                    'status' => 'Dès ' . substr($currentClass->end_time, 0, 5),
                    'is_free_now' => false
                ];
            } else {
                // Libre actuellement. Y a-t-il un cours plus tard ?
                $nextClass = $roomSchedules->first(function ($t) use ($currentTime) {
                    return $t->start_time > $currentTime;
                });

                if ($nextClass) {
                    $freeRoomsData[] = (object) [
                        'name' => $room->name,
                        'status' => 'Jsq ' . substr($nextClass->start_time, 0, 5),
                        'is_free_now' => true
                    ];
                } else {
                    $freeRoomsData[] = (object) [
                        'name' => $room->name,
                        'status' => 'Libre',
                        'is_free_now' => true
                    ];
                }
            }
        }

        // Trier : Libres d'abord, puis occupées
        usort($freeRoomsData, function ($a, $b) {
            if ($a->is_free_now && !$b->is_free_now)
                return -1;
            if (!$a->is_free_now && $b->is_free_now)
                return 1;
            return 0;
        });

        // Prendre les 4 premières
        $freeRooms = array_slice($freeRoomsData, 0, 4);

        return view('SchoolDashboard::academic.timetable', compact(
            'classes',
            'classId',
            'semesters',
            'selectedSemester',
            'months',
            'selectedMonth',
            'targetDate',
            'initialDate',
            'timetables',
            'stats',
            'freeRooms',
            'breaks'
        ));
    }

    public function createTimetable(
        \Illuminate\Http\Request $request,
        \App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository,
        \App\Modules\Academic\Domain\Repositories\SemesterRepositoryInterface $semesterRepository,
        \App\Modules\Academic\Domain\Repositories\SubjectRepositoryInterface $subjectRepository,
        \App\Modules\Academic\Domain\Repositories\TeacherRepositoryInterface $teacherRepository,
        \App\Modules\Academic\Domain\Repositories\RoomRepositoryInterface $roomRepository,
        \App\Modules\Academic\Domain\Repositories\TimetableBreakRepositoryInterface $timetableBreakRepository
    ) {
        $classes = $classRepository->all();
        $semesters = $semesterRepository->all();
        $teachers = $teacherRepository->all();
        $rooms = $roomRepository->all();

        $classId = $request->get('class_id');
        $semesterId = $request->get('semester_id');
        $month = $request->get('month');

        // Default to the current semester when none is explicitly requested.
        if (!$semesterId && $semesters->isNotEmpty()) {
            $current = $semesters->firstWhere('is_current', true) ?? $semesters->first();
            $semesterId = $current ? (string) $current->id : null;
        }

        $selectedSemester = $semesters->firstWhere('id', (int) $semesterId);

        // Build list of months for selected semester
        $months = [];
        if ($selectedSemester) {
            $start = $selectedSemester->start_date ? \Carbon\Carbon::parse($selectedSemester->start_date) : now();
            $end = $selectedSemester->end_date ? \Carbon\Carbon::parse($selectedSemester->end_date) : $start->copy()->addMonths(4);

            $currentMonth = $start->copy()->startOfMonth();
            $endMonth = $end->copy()->startOfMonth();
            while ($currentMonth->lte($endMonth)) {
                $months[] = [
                    'label' => ucfirst($currentMonth->translatedFormat('F Y')),
                    'short' => ucfirst($currentMonth->translatedFormat('M')),
                    'value' => $currentMonth->format('Y-m'),
                    'date' => $currentMonth->format('Y-m-01'),
                ];
                $currentMonth->addMonth();
            }
        }

        if (!$month && !empty($months)) {
            $nowMonth = now()->format('Y-m');
            $month = collect($months)->contains('value', $nowMonth) ? $nowMonth : $months[0]['value'];
        }

        $validFrom = $month ? \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth()->format('Y-m-d') : null;

        // Ensures the requested class actually belongs to this school before using its id below.
        if ($classId && !$classes->contains('id', (int) $classId)) {
            $classId = null;
        }

        $breaks = $timetableBreakRepository->allForClass($classId);

        $existingTimetables = collect();
        $otherTimetables = collect();

        if ($classId) {
            $selectedClass = \App\Modules\Academic\Domain\Models\AcademicClass::with('subjects.teachers')->find($classId);
            $subjects = $selectedClass ? $selectedClass->subjects : collect();

            // 1. Check if there are already entries saved specifically for this valid_from date
            $exactTimetables = \App\Modules\Academic\Domain\Models\Timetable::with(['subject', 'teacher', 'room'])
                ->where('academic_class_id', $classId)
                ->where('semester_id', $semesterId)
                ->where('valid_from', $validFrom)
                ->get();

            if ($exactTimetables->isNotEmpty()) {
                $existingTimetables = $exactTimetables;
            } else {
                // 2. Pre-populate from the currently effective schedule at this target date
                $targetDate = $validFrom ? \Carbon\Carbon::parse($validFrom) : now();
                $baseTimetables = \App\Modules\Academic\Domain\Models\Timetable::with(['subject', 'teacher', 'room'])
                    ->where('academic_class_id', $classId)
                    ->where(function ($q) use ($semesterId) {
                        $q->where('semester_id', $semesterId)
                            ->orWhereNull('semester_id');
                    })
                    ->where(function ($q) use ($targetDate) {
                        $q->whereNull('valid_from')
                            ->orWhere('valid_from', '<=', $targetDate->format('Y-m-d'));
                    })
                    ->orderByDesc('valid_from')
                    ->orderByDesc('id')
                    ->get();

                $hasSpecific = $baseTimetables->contains(fn($t) => $t->semester_id == $semesterId);
                if ($hasSpecific) {
                    $baseTimetables = $baseTimetables->filter(fn($t) => $t->semester_id == $semesterId);
                }

                $existingTimetables = $baseTimetables->unique(fn($t) => $t->day_of_week . '|' . $t->start_time)->values();
            }

            // Conflicts: other classes' timetables for the same semester (room / teacher overlap detection).
            $otherTimetables = \App\Modules\Academic\Domain\Models\Timetable::with(['academicClass', 'room', 'teacher'])
                ->whereHas('academicClass', function ($q) {
                    $q->where('school_id', auth()->user()->school_id);
                })
                ->where('academic_class_id', '!=', $classId)
                ->where('semester_id', $semesterId)
                ->get();
        } else {
            $subjects = collect();
            $otherTimetables = collect();
        }

        $school = auth()->user()->school;

        // Base grid rows (breaks shown as their own row) plus every real start
        // time actually used by a block being displayed — without this, a
        // block starting between two listed rows gets anchored to the row
        // before it and visually overflows into the next row's block.
        $times = collect(['08:00', '09:00', '10:00', '10:30', '11:30', '12:30', '14:00', '15:00', '16:00', '17:00'])
            ->merge($existingTimetables->pluck('start_time')->map(fn ($t) => substr($t, 0, 5)))
            ->merge($breaks->pluck('start_time')->map(fn ($t) => substr($t, 0, 5)))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('SchoolDashboard::academic.timetable_create', compact(
            'classes',
            'semesters',
            'selectedSemester',
            'months',
            'month',
            'validFrom',
            'subjects',
            'teachers',
            'rooms',
            'classId',
            'semesterId',
            'existingTimetables',
            'otherTimetables',
            'school',
            'times',
            'breaks'
        ));
    }

    public function storeTimetable(\Illuminate\Http\Request $request, \App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository)
    {
        $request->validate([
            'class_id' => 'required|exists:academic_classes,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'valid_from' => 'nullable|date',
            'blocks' => 'nullable|array',
            'blocks.*.subject_id' => 'required|exists:subjects,id',
            'blocks.*.teacher_id' => 'nullable',
            'blocks.*.room_id' => 'nullable',
            'blocks.*.day' => 'required|string',
            'blocks.*.start_time' => 'required',
            'blocks.*.end_time' => 'required',
            'status' => 'required|in:draft,published',
        ]);

        $classId = $request->input('class_id');
        $semesterId = $request->input('semester_id');
        $validFrom = $request->input('valid_from'); // e.g. 2026-10-01
        $blocks = $request->input('blocks', []);
        $status = $request->input('status', 'draft');

        // Throws 404 if the class doesn't belong to this school.
        $classRepository->find($classId);

        $school = auth()->user()->school;
        if ($school && $school->day_start_time && $school->day_end_time) {
            $dayStart = \Illuminate\Support\Carbon::parse($school->day_start_time)->format('H:i');
            $dayEnd = \Illuminate\Support\Carbon::parse($school->day_end_time)->format('H:i');

            foreach ($blocks as $block) {
                $blockStart = substr($block['start_time'], 0, 5);
                $blockEnd = substr($block['end_time'], 0, 5);

                if ($blockStart < $dayStart || $blockEnd > $dayEnd) {
                    return response()->json([
                        'success' => false,
                        'message' => "Le créneau {$blockStart}-{$blockEnd} sort des heures de cours de l'établissement ({$dayStart}-{$dayEnd}).",
                    ], 422);
                }
            }
        }

        // Delete ONLY the timetable entries for this exact class, semester, and valid_from date.
        // This ensures past months and weeks are completely unaffected.
        $deleteQuery = \App\Modules\Academic\Domain\Models\Timetable::where('academic_class_id', $classId)
            ->where('semester_id', $semesterId);

        if ($validFrom) {
            $deleteQuery->where('valid_from', $validFrom);
        } else {
            $deleteQuery->whereNull('valid_from');
        }
        $deleteQuery->delete();

        foreach ($blocks as $block) {
            \App\Modules\Academic\Domain\Models\Timetable::create([
                'academic_class_id' => $classId,
                'semester_id' => $semesterId,
                'valid_from' => $validFrom,
                'subject_id' => $block['subject_id'],
                'teacher_id' => $block['teacher_id'] ?? null,
                'room_id' => $block['room_id'] ?? null,
                'day_of_week' => $block['day'],
                'start_time' => $block['start_time'],
                'end_time' => $block['end_time'],
                'status' => $status,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Emploi du temps enregistré avec succès']);
    }

    /**
     * Real analysis of the draft currently being built on screen (gaps
     * between consecutive slots per day, hours per teacher) — replaces two
     * buttons that used to fake a 2-second "optimization" with a setTimeout
     * and never touched the schedule at all. This doesn't auto-rearrange
     * anything (that's a real constraint-solving problem, out of scope for
     * a text-generation call) — it surfaces real numbers and lets the AI
     * phrase observations/suggestions.
     */
    public function aiAnalyzeTimetableDraft(\Illuminate\Http\Request $request, \App\Modules\SuperAdmin\Application\Services\AIService $aiService)
    {
        $request->validate([
            'blocks' => 'nullable|array',
            'blocks.*.teacher_id' => 'nullable',
            'blocks.*.day' => 'required_with:blocks|string',
            'blocks.*.start_time' => 'required_with:blocks',
            'blocks.*.end_time' => 'required_with:blocks',
        ]);

        $blocks = collect($request->input('blocks', []));

        if ($blocks->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => "Aucun cours n'a encore été placé dans l'emploi du temps.",
                'stats' => null,
            ]);
        }

        // Hours per teacher, from the real draft.
        $hoursByTeacher = $blocks->filter(fn ($b) => !empty($b['teacher_id']))
            ->groupBy('teacher_id')
            ->map(function ($group) {
                return round($group->sum(function ($b) {
                    return abs(\Carbon\Carbon::parse($b['end_time'])->diffInMinutes(\Carbon\Carbon::parse($b['start_time']))) / 60;
                }), 1);
            });

        // Gaps between consecutive slots on the same day.
        $gapsMinutes = 0;
        $gapCount = 0;
        foreach ($blocks->groupBy('day') as $dayBlocks) {
            $sorted = $dayBlocks->sortBy('start_time')->values();
            for ($i = 0; $i < $sorted->count() - 1; $i++) {
                $gap = \Carbon\Carbon::parse($sorted[$i + 1]['start_time'])
                    ->diffInMinutes(\Carbon\Carbon::parse($sorted[$i]['end_time']));
                $gap = abs($gap);
                if ($gap > 0) {
                    $gapsMinutes += $gap;
                    $gapCount++;
                }
            }
        }

        $stats = [
            'total_creneaux' => $blocks->count(),
            'heures_par_enseignant' => $hoursByTeacher->toArray(),
            'nombre_de_trous_detectes' => $gapCount,
            'total_minutes_de_trous' => $gapsMinutes,
        ];

        $systemPrompt = "Tu es un assistant de planification scolaire pour AcademiaERP. Tu commentes un brouillon d'emploi du temps réel, sans jamais prétendre l'avoir automatiquement réorganisé — tu donnes des observations et suggestions concrètes que l'utilisateur peut appliquer lui-même.";
        $userPrompt = "Voici les statistiques réelles du brouillon d'emploi du temps actuellement affiché à l'écran :\n"
            . json_encode($stats, JSON_UNESCAPED_UNICODE)
            . "\n\nRédige un commentaire court (2 à 4 phrases) : signale les trous et déséquilibres de charge entre enseignants s'il y en a, sinon dis simplement que la répartition est équilibrée.";

        $result = $aiService->generateText($systemPrompt, $userPrompt, 250);

        return response()->json([
            'success' => $result['success'],
            'analysis' => $result['text'],
            'error' => $result['error'],
            'stats' => $stats,
        ]);
    }

    public function students(\Illuminate\Http\Request $request, \App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface $repository)
    {
        $filters = [
            'search' => $request->get('search'),
            'academic_class_id' => $request->get('academic_class_id'),
            'status' => $request->get('status'),
        ];
        $students = $repository->paginate(10, $filters);
        $schoolId = auth()->user()->school_id;
        $branchId = auth()->user()->activeBranchId();
        $stats = [
            'total' => \App\Modules\Academic\Domain\Models\Student::where('school_id', $schoolId)->whereBranch($branchId)->count(),
            'active' => \App\Modules\Academic\Domain\Models\Student::where('school_id', $schoolId)->whereBranch($branchId)->where('status', 'active')->count(),
            'inactive' => \App\Modules\Academic\Domain\Models\Student::where('school_id', $schoolId)->whereBranch($branchId)->where('status', 'inactive')->count(),
            'recent' => \App\Modules\Academic\Domain\Models\Student::where('school_id', $schoolId)->whereBranch($branchId)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
        $classes = \App\Modules\Academic\Domain\Models\AcademicClass::where('school_id', $schoolId)->whereBranch($branchId)->orderBy('name')->get();
        return view('SchoolDashboard::academic.students', compact('students', 'stats', 'classes', 'filters'));
    }

    public function showStudent(
        $id,
        \App\Modules\Bulletin\Domain\Repositories\BulletinGradeRepositoryInterface $gradeRepository,
        \App\Modules\Bulletin\Domain\Repositories\BulletinPublicationRepositoryInterface $publicationRepository,
        \App\Modules\Bulletin\Application\Services\BulletinStatsService $bulletinStats,
        \App\Modules\Finance\Application\Services\StudentFeeService $feeService
    ) {
        $schoolId = auth()->user()->school_id;
        $student = \App\Modules\Academic\Domain\Models\Student::where('school_id', $schoolId)
            ->with(['academicClass.subjects', 'branch', 'guardians'])
            ->findOrFail($id);

        $currentSemester = $bulletinStats->currentSemester($schoolId);
        $average = null;
        $rank = null;
        $classSize = 0;
        $subjectGrades = collect();
        $bulletinPublished = false;

        if ($currentSemester && $student->academic_class_id) {
            $classGrades = $gradeRepository->forClassAndSemester($student->academic_class_id, $currentSemester->id);
            $classGrades = $bulletinStats->mergeHomeworkGrades($classGrades, $student->academic_class_id, $currentSemester->id);
            $rawStudentGrades = $classGrades->where('student_id', $student->id);
            $subjectGrades = $bulletinStats->aggregateToSubjectGrades($rawStudentGrades);
            $average = $bulletinStats->studentAverage($subjectGrades);

            $ranking = $bulletinStats->classRanking($student->academic_class_id, $currentSemester->id, $classGrades);
            $studentRow = collect($ranking)->firstWhere('student.id', $student->id);
            $rank = $studentRow['rank'] ?? null;
            $classSize = count($ranking);

            $publication = $publicationRepository->findOrCreate($student->academic_class_id, $currentSemester->id);
            $bulletinPublished = $publication->status === \App\Modules\Bulletin\Domain\Models\BulletinPublication::STATUS_PUBLISHED;
        }

        $since = $currentSemester?->start_date ?? now()->subMonths(4)->toDateString();
        $attendanceRecords = \App\Modules\Presence\Domain\Models\AttendanceRecord::where('student_id', $student->id)
            ->where('date', '>=', $since)
            ->get();
        $totalDays = $attendanceRecords->count();
        $justifiedAbsences = $attendanceRecords->where('status', 'absent')->filter(fn ($r) => $r->justified)->count();
        $unjustifiedAbsences = $attendanceRecords->where('status', 'absent')->filter(fn ($r) => !$r->justified)->count();
        $lateCount = $attendanceRecords->where('status', 'late')->count();
        $attendanceRate = $totalDays > 0
            ? round($attendanceRecords->whereIn('status', ['present', 'late'])->count() / $totalDays * 100)
            : null;

        $feeSummaries = collect(\App\Modules\Finance\Domain\Models\FeeLevel::TYPES)
            ->map(fn ($label, $type) => array_merge(['type' => $type, 'label' => $label], $feeService->summaryFor($student, $type)))
            ->values();
        $totalPaid = $feeSummaries->sum('paid');
        $totalRemaining = $feeSummaries->sum('remaining');

        $movements = \App\Modules\Academic\Domain\Models\StudentClassMovement::where('student_id', $student->id)
            ->with(['fromClass', 'toClass'])
            ->orderByDesc('created_at')
            ->get();

        $documents = \App\Modules\Academic\Domain\Models\StudentDocument::where('student_id', $student->id)
            ->orderByDesc('deposited_at')
            ->get();

        $disciplinaryRecords = \App\Modules\Academic\Domain\Models\StudentDisciplinaryRecord::where('student_id', $student->id)
            ->orderByDesc('recorded_date')
            ->get();

        $scholarships = \App\Modules\Finance\Domain\Models\Scholarship::where('student_id', $student->id)
            ->with('type')
            ->orderByDesc('created_at')
            ->get();

        $awards = \App\Modules\Academic\Domain\Models\Award::where('school_id', $student->school_id)
            ->where('recipient_type', 'student')
            ->where('recipient_id', $student->id)
            ->with('type')
            ->orderByDesc('awarded_date')
            ->get();

        return view('SchoolDashboard::academic.student_profile', compact(
            'student', 'currentSemester', 'subjectGrades', 'average', 'rank', 'classSize', 'bulletinPublished',
            'attendanceRecords', 'totalDays', 'justifiedAbsences', 'unjustifiedAbsences', 'lateCount', 'attendanceRate',
            'feeSummaries', 'totalPaid', 'totalRemaining', 'movements', 'documents', 'disciplinaryRecords', 'scholarships', 'awards'
        ));
    }

    public function storeStudentDocument(Request $request, $studentId)
    {
        $student = \App\Modules\Academic\Domain\Models\Student::where('school_id', auth()->user()->school_id)->findOrFail($studentId);

        $data = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', array_keys(\App\Modules\Academic\Domain\Models\StudentDocument::TYPES))],
            'label' => ['required', 'string', 'max:255'],
            'deposited_at' => ['required', 'date'],
            'file' => ['required', 'file', 'max:5120'],
        ]);

        $path = $request->file('file')->store('students/documents', 'public');

        \App\Modules\Academic\Domain\Models\StudentDocument::create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'type' => $data['type'],
            'label' => $data['label'],
            'file_path' => $path,
            'deposited_at' => $data['deposited_at'],
            'status' => \App\Modules\Academic\Domain\Models\StudentDocument::STATUS_PENDING,
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Document ajouté avec succès.');
    }

    public function updateStudentDocumentStatus(Request $request, $id)
    {
        $document = \App\Modules\Academic\Domain\Models\StudentDocument::whereHas('student', function ($q) {
            $q->where('school_id', auth()->user()->school_id);
        })->findOrFail($id);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:pending,validated,rejected'],
        ]);

        $document->update(['status' => $data['status']]);

        return back()->with('success', 'Statut du document mis à jour.');
    }

    public function destroyStudentDocument($id)
    {
        $document = \App\Modules\Academic\Domain\Models\StudentDocument::whereHas('student', function ($q) {
            $q->where('school_id', auth()->user()->school_id);
        })->findOrFail($id);

        if ($document->file_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Document supprimé avec succès.');
    }

    public function storeDisciplinaryRecord(Request $request, $studentId)
    {
        $student = \App\Modules\Academic\Domain\Models\Student::where('school_id', auth()->user()->school_id)->findOrFail($studentId);

        $allTypes = \App\Modules\Academic\Domain\Models\StudentDisciplinaryRecord::DISTINCTION_TYPES
            + \App\Modules\Academic\Domain\Models\StudentDisciplinaryRecord::SANCTION_TYPES;

        $data = $request->validate([
            'category' => ['required', 'string', 'in:distinction,sanction'],
            'type' => ['required', 'string', 'in:' . implode(',', array_keys($allTypes))],
            'description' => ['nullable', 'string', 'max:2000'],
            'recorded_date' => ['required', 'date'],
        ]);

        $expectedTypes = $data['category'] === 'distinction'
            ? \App\Modules\Academic\Domain\Models\StudentDisciplinaryRecord::DISTINCTION_TYPES
            : \App\Modules\Academic\Domain\Models\StudentDisciplinaryRecord::SANCTION_TYPES;
        if (!array_key_exists($data['type'], $expectedTypes)) {
            return back()->withErrors(['type' => "Ce type ne correspond pas à la catégorie sélectionnée."])->withInput();
        }

        \App\Modules\Academic\Domain\Models\StudentDisciplinaryRecord::create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'category' => $data['category'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'recorded_date' => $data['recorded_date'],
            'recorded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Enregistré avec succès.');
    }

    public function destroyDisciplinaryRecord($id)
    {
        $record = \App\Modules\Academic\Domain\Models\StudentDisciplinaryRecord::whereHas('student', function ($q) {
            $q->where('school_id', auth()->user()->school_id);
        })->findOrFail($id);

        $record->delete();

        return back()->with('success', 'Supprimé avec succès.');
    }

    /** Real academic years in use at this school (Semesters, Students, FeeLevels), not a hardcoded pair — always includes $extra if given so an existing record's year is never dropped from its own edit form. */
    private function academicYearOptions(int $schoolId, ?string $extra = null)
    {
        $years = collect()
            ->merge(\App\Modules\Academic\Domain\Models\Semester::where('school_id', $schoolId)->pluck('academic_year'))
            ->merge(\App\Modules\Academic\Domain\Models\Student::where('school_id', $schoolId)->pluck('academic_year'))
            ->merge(\App\Modules\Finance\Domain\Models\FeeLevel::where('school_id', $schoolId)->pluck('academic_year'))
            ->filter()
            ->push($extra)
            ->filter()
            ->unique()
            ->sortByDesc(fn ($y) => $y)
            ->values();

        if ($years->isEmpty()) {
            $years = collect([now()->format('Y') . '-' . now()->addYear()->format('Y')]);
        }

        return $years;
    }

    public function createStudent(\App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository, \App\Modules\Academic\Domain\Repositories\GuardianRepositoryInterface $guardianRepository)
    {
        $classes = $classRepository->all();
        $guardians = $guardianRepository->all();
        $academicYears = $this->academicYearOptions(auth()->user()->school_id);
        return view('SchoolDashboard::academic.student_create', compact('classes', 'guardians', 'academicYears'));
    }

    public function storeStudent(Request $request, CreateStudentUseCase $useCase)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
            'birthplace' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:male,female'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_country_code' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'academic_year' => ['required', 'string', 'max:20'],
            'roll_number' => ['nullable', 'string', 'max:50', 'unique:students,roll_number'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'regime' => ['nullable', 'string', 'in:interne,externe'],
            'enrollment_type' => ['required', 'string', 'in:new,returning,transferred'],
            'enrollment_date' => ['nullable', 'date'],
            'entry_date' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'allergies' => ['nullable', 'string'],
            'medical_conditions' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'guardian_ids' => ['nullable', 'array'],
            'guardian_ids.*' => ['exists:guardians,id'],
        ]);

        $guardianIds = $data['guardian_ids'] ?? [];
        unset($data['guardian_ids']);

        $data['phone'] = \App\Modules\SuperAdmin\Domain\Models\Country::combinePhone($data['phone_country_code'] ?? null, $data['phone_number'] ?? null);
        unset($data['phone_country_code'], $data['phone_number']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('students/photos', 'public');
        }

        if (empty($data['roll_number'])) {
            $data['roll_number'] = 'MAT-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));
        }

        $data['dossier_number'] = 'DOS-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));

        $dto = new CreateStudentDTO($data);

        try {
            $student = $useCase->execute($dto);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['academic_class_id' => $e->getMessage()])->withInput();
        }

        $student->guardians()->sync($guardianIds);

        return redirect()->route('school.academic.students')->with('success', 'Étudiant enregistré avec succès !');
    }

    public function editStudent($id, \App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface $repository, \App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository, \App\Modules\Academic\Domain\Repositories\GuardianRepositoryInterface $guardianRepository)
    {
        $student = $repository->find($id);
        $classes = $classRepository->all();
        $guardians = $guardianRepository->all();
        $academicYears = $this->academicYearOptions(auth()->user()->school_id, $student->academic_year);
        return view('SchoolDashboard::academic.student_create', compact('student', 'classes', 'guardians', 'academicYears'));
    }

    public function updateStudent($id, Request $request, UpdateStudentUseCase $useCase)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
            'birthplace' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:male,female'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_country_code' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'academic_year' => ['required', 'string', 'max:20'],
            // No 'roll_number' rule here on purpose: it's auto-generated at
            // creation (see storeStudent()) and the edit form shows it
            // disabled — a disabled <input> is never submitted by the
            // browser, so requiring it here always failed validation.
            // Immutable on edit: since it's absent from $data, update()
            // below leaves the existing value untouched.
            // 'dossier_number' likewise absent on purpose — auto-generated at
            // creation only (see storeStudent()), shown disabled on edit.
            'status' => ['required', 'string', 'in:active,inactive'],
            'regime' => ['nullable', 'string', 'in:interne,externe'],
            'enrollment_type' => ['required', 'string', 'in:new,returning,transferred'],
            'enrollment_date' => ['nullable', 'date'],
            'entry_date' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'allergies' => ['nullable', 'string'],
            'medical_conditions' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'guardian_ids' => ['nullable', 'array'],
            'guardian_ids.*' => ['exists:guardians,id'],
        ]);

        $guardianIds = $data['guardian_ids'] ?? [];
        unset($data['guardian_ids']);

        $data['phone'] = \App\Modules\SuperAdmin\Domain\Models\Country::combinePhone($data['phone_country_code'] ?? null, $data['phone_number'] ?? null);
        unset($data['phone_country_code'], $data['phone_number']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('students/photos', 'public');
        }

        $dto = new UpdateStudentDTO($data);
        $student = $useCase->execute($id, $dto);
        $student->guardians()->sync($guardianIds);

        return redirect()->route('school.academic.students')->with('success', 'Informations mises à jour avec succès !');
    }

    public function destroyStudent($id, DeleteStudentUseCase $useCase)
    {
        $useCase->execute($id);
        return redirect()->route('school.academic.students')->with('success', 'Étudiant supprimé avec succès !');
    }

    public function transferStudents(Request $request, StudentRepositoryInterface $studentRepository, AcademicClassRepositoryInterface $classRepository, StudentClassMovementRepositoryInterface $movementRepository)
    {
        $schoolId = auth()->user()->school_id;
        $students = Student::where('school_id', $schoolId)->whereBranch(auth()->user()->activeBranchId())->where('status', 'active')->with('academicClass')->orderBy('first_name')->get();
        $classes = $classRepository->all();
        $history = $movementRepository->recent(StudentClassMovement::TYPE_TRANSFER, 15);

        return view('SchoolDashboard::academic.student_transfer', compact('students', 'classes', 'history'));
    }

    public function storeTransfer(Request $request, StudentRepositoryInterface $studentRepository, AcademicClassRepositoryInterface $classRepository, RecordStudentClassMovementUseCase $useCase)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'to_class_id' => ['required', 'exists:academic_classes,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $student = $studentRepository->find($data['student_id']);
        $toClass = $classRepository->find($data['to_class_id']);

        try {
            $useCase->execute($student, $toClass, StudentClassMovement::TYPE_TRANSFER, null, $data['reason'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['to_class_id' => $e->getMessage()])->withInput();
        }

        return redirect()->route('school.academic.students.transfer')->with('success', 'Élève transféré avec succès !');
    }

    public function promoteStudents(Request $request, AcademicClassRepositoryInterface $classRepository, StudentClassMovementRepositoryInterface $movementRepository)
    {
        $schoolId = auth()->user()->school_id;
        $classes = $classRepository->all();
        // Destination classes span every branch of the school (e.g. Primaire -> Collège, Collège -> Lycée),
        // since promotion is precisely the operation that can move a student across branches.
        $allClasses = \App\Modules\Academic\Domain\Models\AcademicClass::where('school_id', $schoolId)->with('branch')->orderBy('name')->get();
        $students = Student::where('school_id', $schoolId)->whereBranch(auth()->user()->activeBranchId())->where('status', 'active')->orderBy('first_name')->get();
        $history = $movementRepository->recent(StudentClassMovement::TYPE_PROMOTION, 15);

        return view('SchoolDashboard::academic.student_promotion', compact('classes', 'allClasses', 'students', 'history'));
    }

    public function storePromotion(Request $request, StudentRepositoryInterface $studentRepository, AcademicClassRepositoryInterface $classRepository, RecordStudentClassMovementUseCase $useCase)
    {
        $data = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['exists:students,id'],
            'from_class_id' => ['required', 'exists:academic_classes,id'],
            'to_class_id' => ['required', 'exists:academic_classes,id'],
            'to_academic_year' => ['required', 'string', 'max:20'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        // Destination class may belong to a different branch than the active one (e.g. Primaire -> Collège),
        // so it's looked up school-wide rather than through the branch-scoped repository.
        $toClass = \App\Modules\Academic\Domain\Models\AcademicClass::where('school_id', auth()->user()->school_id)->findOrFail($data['to_class_id']);
        $errors = [];
        $successCount = 0;

        foreach ($data['student_ids'] as $studentId) {
            $student = $studentRepository->find($studentId);
            try {
                $useCase->execute($student, $toClass, StudentClassMovement::TYPE_PROMOTION, $data['to_academic_year'], $data['reason'] ?? null);
                $successCount++;
            } catch (\InvalidArgumentException $e) {
                $errors[] = $student->first_name . ' ' . $student->last_name . ' : ' . $e->getMessage();
            }
        }

        return redirect()->route('school.academic.students.promote', ['class_id' => $data['from_class_id']])
            ->with('success', $successCount > 0 ? $successCount . ' élève(s) promu(s) avec succès !' : null)
            ->with('promotionErrors', $errors);
    }

    public function parents(\App\Modules\Academic\Domain\Repositories\GuardianRepositoryInterface $repository)
    {
        $guardians = $repository->paginate(10);
        $schoolId = auth()->user()->school_id;
        $stats = [
            'total' => \App\Modules\Academic\Domain\Models\Guardian::where('school_id', $schoolId)->count(),
            'active' => \App\Modules\Academic\Domain\Models\Guardian::where('school_id', $schoolId)->where('status', 'active')->count(),
            'pending' => \App\Modules\Academic\Domain\Models\Guardian::where('school_id', $schoolId)->where('status', 'pending')->count(),
        ];
        return view('SchoolDashboard::academic.parents', compact('guardians', 'stats'));
    }

    public function createParent(\App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface $studentRepository)
    {
        $students = $studentRepository->all();
        return view('SchoolDashboard::academic.parents_create', compact('students'));
    }

    public function storeParent(Request $request, CreateGuardianUseCase $useCase, ParentPortalAccountService $parentAccountService)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relation' => ['required', 'string', 'in:pere,mere,tuteur'],
            'phone_country_code' => ['nullable', 'string'],
            'phone_number' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:students,id'],
        ]);

        $data['phone'] = \App\Modules\SuperAdmin\Domain\Models\Country::combinePhone($data['phone_country_code'] ?? null, $data['phone_number']);
        unset($data['phone_country_code'], $data['phone_number']);

        $studentIds = $data['student_ids'] ?? [];
        unset($data['student_ids']);

        $dto = new CreateGuardianDTO($data);
        $guardian = $useCase->execute($dto);
        $guardian->students()->sync($studentIds);

        $generatedPassword = $parentAccountService->sync($guardian, $data['name'], $data['phone'], $data['email'] ?? null);

        $message = 'Parent enregistré avec succès !';
        if ($generatedPassword) {
            $message .= " Compte du portail parent créé — identifiant : {$data['phone']}, mot de passe temporaire : {$generatedPassword} (à communiquer à la famille).";
        }

        return redirect()->route('school.academic.parents')->with('success', $message);
    }

    /**
     * Quick-create a Guardian from within another form (e.g. student creation) without
     * leaving the page. Never attaches students here — the caller does that itself
     * (a new student may not have an id yet when this is called).
     */
    public function storeParentAjax(Request $request, CreateGuardianUseCase $useCase, ParentPortalAccountService $parentAccountService)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relation' => ['required', 'string', 'in:pere,mere,tuteur'],
            'phone_country_code' => ['nullable', 'string'],
            'phone_number' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $data['phone'] = \App\Modules\SuperAdmin\Domain\Models\Country::combinePhone($data['phone_country_code'] ?? null, $data['phone_number']);
        unset($data['phone_country_code'], $data['phone_number']);

        $dto = new CreateGuardianDTO($data);
        $guardian = $useCase->execute($dto);

        $generatedPassword = $parentAccountService->sync($guardian, $data['name'], $data['phone'], $data['email'] ?? null);

        return response()->json([
            'guardian' => [
                'id' => $guardian->id,
                'name' => $guardian->name,
                'relation' => $guardian->relation,
            ],
            'generated_password' => $generatedPassword,
        ], 201);
    }

    public function editParent($id, \App\Modules\Academic\Domain\Repositories\GuardianRepositoryInterface $repository, \App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface $studentRepository)
    {
        $guardian = $repository->find($id);
        $students = $studentRepository->all();
        return view('SchoolDashboard::academic.parents_create', compact('guardian', 'students'));
    }

    public function updateParent($id, Request $request, UpdateGuardianUseCase $useCase, ParentPortalAccountService $parentAccountService)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relation' => ['required', 'string', 'in:pere,mere,tuteur'],
            'phone_country_code' => ['nullable', 'string'],
            'phone_number' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:students,id'],
        ]);

        $data['phone'] = \App\Modules\SuperAdmin\Domain\Models\Country::combinePhone($data['phone_country_code'] ?? null, $data['phone_number']);
        unset($data['phone_country_code'], $data['phone_number']);

        $studentIds = $data['student_ids'] ?? [];
        unset($data['student_ids']);

        $dto = new UpdateGuardianDTO($data);
        $guardian = $useCase->execute($id, $dto);
        $guardian->students()->sync($studentIds);

        $generatedPassword = $parentAccountService->sync($guardian, $data['name'], $data['phone'], $data['email'] ?? null);

        $message = 'Informations mises à jour avec succès !';
        if ($generatedPassword) {
            $message .= " Compte du portail parent créé — identifiant : {$data['phone']}, mot de passe temporaire : {$generatedPassword} (à communiquer à la famille).";
        }

        return redirect()->route('school.academic.parents')->with('success', $message);
    }

    public function destroyParent($id, DeleteGuardianUseCase $useCase)
    {
        $useCase->execute($id);
        return redirect()->route('school.academic.parents')->with('success', 'Parent supprimé avec succès !');
    }

    public function teachers(\Illuminate\Http\Request $request, \App\Modules\Academic\Domain\Repositories\TeacherRepositoryInterface $repository, \App\Modules\Academic\Domain\Repositories\SubjectRepositoryInterface $subjectRepository)
    {
        $filters = [
            'subject_id' => $request->get('subject_id')
        ];

        $teachers = $repository->paginate(10, $filters);
        $subjects = $subjectRepository->all();

        $schoolId = auth()->user()->school_id;
        $branchId = auth()->user()->activeBranchId();
        $stats = [
            'total' => \App\Modules\Academic\Domain\Models\Teacher::where('school_id', $schoolId)->whereBranch($branchId)->count(),
            'present' => \App\Modules\Academic\Domain\Models\Teacher::where('school_id', $schoolId)->whereBranch($branchId)->where('status', 'active')->count(),
            // Real, computable HR signal (contract renewals due soon) — there is
            // no teacher attendance/leave tracking in the app, so an "absence
            // risk" prediction had zero real data behind it. This is not AI,
            // just a real date comparison.
            'contracts_expiring' => \App\Modules\Academic\Domain\Models\Teacher::where('school_id', $schoolId)
                ->whereBranch($branchId)
                ->whereNotNull('contract_end_date')
                ->whereBetween('contract_end_date', [now(), now()->addDays(30)])
                ->count(),
        ];
        return view('SchoolDashboard::academic.teachers', compact('teachers', 'stats', 'subjects', 'filters'));
    }

    /**
     * Real per-subject staffing snapshot (current teacher count + average
     * weekly hours from the actual timetable), narrated by AI into an hours
     * suggestion — replaces a static paragraph that had no data behind it.
     */
    public function aiSuggestTeacherHours(Request $request, \App\Modules\SuperAdmin\Application\Services\AIService $aiService)
    {
        $validated = $request->validate([
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'integer|exists:subjects,id',
        ]);

        $schoolId = auth()->user()->school_id;

        $subjects = \App\Modules\Academic\Domain\Models\Subject::whereIn('id', $validated['subject_ids'])
            ->where('school_id', $schoolId)
            ->get();

        $stats = [];
        foreach ($subjects as $subject) {
            $teacherCount = $subject->teachers()->count();

            $slots = \App\Modules\Academic\Domain\Models\Timetable::where('subject_id', $subject->id)->get();
            $totalWeeklyHours = $slots->sum(function ($slot) {
                if (!$slot->start_time || !$slot->end_time) {
                    return 0;
                }
                return abs(\Carbon\Carbon::parse($slot->end_time)->diffInMinutes(\Carbon\Carbon::parse($slot->start_time))) / 60;
            });

            $stats[] = [
                'matiere' => $subject->name,
                'enseignants_actuels' => $teacherCount,
                'heures_hebdo_actuelles_toutes_classes' => round($totalWeeklyHours, 1),
                'moyenne_heures_par_enseignant' => $teacherCount > 0 ? round($totalWeeklyHours / $teacherCount, 1) : null,
            ];
        }

        $systemPrompt = "Tu es un assistant RH pour AcademiaERP, un SaaS de gestion scolaire. Tu suggères une charge horaire hebdomadaire réaliste pour un nouvel enseignant, à partir de statistiques réelles de l'établissement — pas d'invention.";
        $userPrompt = "Voici les statistiques réelles actuelles (issues de l'emploi du temps) pour la ou les matières sélectionnées pour ce nouvel enseignant :\n"
            . json_encode($stats, JSON_UNESCAPED_UNICODE)
            . "\n\nSuggère une allocation d'heures hebdomadaires raisonnable pour ce nouvel enseignant (2-3 phrases, en français). Si aucun enseignant n'existe encore pour une matière, dis-le et propose une charge standard (ex: 18h/semaine) plutôt que d'inventer une moyenne.";

        $result = $aiService->generateText($systemPrompt, $userPrompt, 220);

        return response()->json([
            'success' => $result['success'],
            'suggestion' => $result['text'],
            'error' => $result['error'],
            'stats' => $stats,
        ]);
    }

    public function createTeacher(\App\Modules\Academic\Domain\Repositories\SubjectRepositoryInterface $subjectRepository, \App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository)
    {
        $subjects = $subjectRepository->all();
        $classes = $classRepository->all();
        $roles = \App\Modules\Academic\Domain\Models\Role::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
        return view('SchoolDashboard::academic.teacher_create', compact('subjects', 'classes', 'roles'));
    }

    public function storeTeacher(Request $request, CreateTeacherUseCase $useCase)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_country_code' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'subject_ids' => ['required', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
            'employment_type' => ['required', 'string', 'in:full_time,part_time'],
            'contract_type' => ['nullable', 'string', 'in:cdi,cdd,prestataire'],
            'contract_end_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:active,on_leave,inactive'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'academic_class_ids' => ['required', 'array'],
            'academic_class_ids.*' => ['exists:academic_classes,id'],
            'address' => ['nullable', 'string'],
            'hire_date' => ['nullable', 'date'],
            'role' => ['nullable', 'string'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'employee_id' => ['required', 'string', 'max:255', 'unique:teachers,employee_id'],
            'login_id' => ['nullable', 'string', 'max:255', 'unique:teachers,login_id', 'unique:users,login_id'],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'head_teacher_class_ids' => ['nullable', 'array'],
            'head_teacher_class_ids.*' => ['exists:academic_classes,id'],
        ]);

        $data['phone'] = \App\Modules\SuperAdmin\Domain\Models\Country::combinePhone($data['phone_country_code'] ?? null, $data['phone_number'] ?? null);
        unset($data['phone_country_code'], $data['phone_number']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('teachers/photos', 'public');
        }

        $dto = new CreateTeacherDTO($data);

        try {
            $useCase->execute($dto);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['employee_id' => $e->getMessage()])->withInput();
        }

        return redirect()->route('school.academic.teachers')->with('success', 'Enseignant enregistré avec succès !');
    }

    public function showTeacher($id, \App\Modules\Academic\Domain\Repositories\TeacherRepositoryInterface $repository)
    {
        $teacher = $repository->find($id);
        if (!$teacher) {
            return redirect()->route('school.academic.teachers')->with('error', 'Enseignant non trouvé.');
        }

        return view('SchoolDashboard::academic.teacher_show', compact('teacher'));
    }

    public function editTeacher($id, \App\Modules\Academic\Domain\Repositories\TeacherRepositoryInterface $repository, \App\Modules\Academic\Domain\Repositories\SubjectRepositoryInterface $subjectRepository, \App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository)
    {
        $teacher = $repository->find($id);
        $subjects = $subjectRepository->all();
        $classes = $classRepository->all();
        $roles = \App\Modules\Academic\Domain\Models\Role::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
        return view('SchoolDashboard::academic.teacher_create', compact('teacher', 'subjects', 'classes', 'roles'));
    }

    public function updateTeacher(Request $request, $id, UpdateTeacherUseCase $useCase)
    {
        $linkedPortalUserId = \App\Models\User::where('teacher_id', $id)->value('id');

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_country_code' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'subject_ids' => ['required', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
            'employment_type' => ['required', 'string', 'in:full_time,part_time'],
            'contract_type' => ['nullable', 'string', 'in:cdi,cdd,prestataire'],
            'contract_end_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:active,on_leave,inactive'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'academic_class_ids' => ['required', 'array'],
            'academic_class_ids.*' => ['exists:academic_classes,id'],
            'address' => ['nullable', 'string'],
            'hire_date' => ['nullable', 'date'],
            'role' => ['nullable', 'string'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'employee_id' => ['required', 'string', 'max:255', 'unique:teachers,employee_id,' . $id],
            'login_id' => ['nullable', 'string', 'max:255', 'unique:teachers,login_id,' . $id, 'unique:users,login_id,' . ($linkedPortalUserId ?? 'NULL')],
            'password' => ['nullable', 'string'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'head_teacher_class_ids' => ['nullable', 'array'],
            'head_teacher_class_ids.*' => ['exists:academic_classes,id'],
        ]);

        $data['phone'] = \App\Modules\SuperAdmin\Domain\Models\Country::combinePhone($data['phone_country_code'] ?? null, $data['phone_number'] ?? null);
        unset($data['phone_country_code'], $data['phone_number']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('teachers/photos', 'public');
        }

        $dto = new UpdateTeacherDTO($data);
        $useCase->execute($id, $dto);

        return redirect()->route('school.academic.teachers')->with('success', 'Enseignant mis à jour avec succès !');
    }

    public function destroyTeacher($id, DeleteTeacherUseCase $useCase)
    {
        $useCase->execute($id);
        return redirect()->route('school.academic.teachers')->with('success', 'Professeur supprimé avec succès.');
    }

    public function personnel(\Illuminate\Http\Request $request, \App\Modules\Academic\Domain\Repositories\StaffRepositoryInterface $repository)
    {
        $filters = [
            'role' => $request->get('role')
        ];

        $staffMembers = $repository->paginate(10, $filters);

        $schoolId = auth()->user()->school_id;
        $branchId = auth()->user()->activeBranchId();
        $stats = [
            'total' => \App\Modules\Academic\Domain\Models\Staff::where('school_id', $schoolId)->whereBranch($branchId)->count(),
            'active' => \App\Modules\Academic\Domain\Models\Staff::where('school_id', $schoolId)->whereBranch($branchId)->where('status', 'active')->count(),
            'recent' => \App\Modules\Academic\Domain\Models\Staff::where('school_id', $schoolId)->whereBranch($branchId)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];

        return view('SchoolDashboard::academic.personnel', compact('staffMembers', 'stats', 'filters'));
    }

    public function createPersonnel()
    {
        $roles = \App\Modules\Academic\Domain\Models\Role::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
        return view('SchoolDashboard::academic.personnel_create', compact('roles'));
    }

    public function storePersonnel(Request $request, CreateStaffUseCase $useCase)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_country_code' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'role' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'contract_type' => ['nullable', 'string', 'in:cdi,cdd,prestataire'],
            'contract_end_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:active,on_leave,inactive'],
            'hire_date' => ['nullable', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'employee_id' => ['required', 'string', 'max:255', 'unique:school_staff,employee_id'],
            'login_id' => ['nullable', 'string', 'max:255', 'unique:users,login_id'],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        $data['phone'] = \App\Modules\SuperAdmin\Domain\Models\Country::combinePhone($data['phone_country_code'] ?? null, $data['phone_number'] ?? null);
        unset($data['phone_country_code'], $data['phone_number']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('staff/photos', 'public');
        }

        $dto = new CreateStaffDTO($data);

        try {
            $useCase->execute($dto);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['employee_id' => $e->getMessage()])->withInput();
        }

        return redirect()->route('school.academic.personnel')->with('success', 'Membre du personnel enregistré avec succès !');
    }

    public function editPersonnel($id, \App\Modules\Academic\Domain\Repositories\StaffRepositoryInterface $repository)
    {
        $staffMember = $repository->find($id);
        $roles = \App\Modules\Academic\Domain\Models\Role::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
        return view('SchoolDashboard::academic.personnel_create', compact('staffMember', 'roles'));
    }

    public function updatePersonnel(Request $request, $id, UpdateStaffUseCase $useCase)
    {
        $linkedPortalUserId = \App\Models\User::where('staff_id', $id)->value('id');

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_country_code' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'role' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'contract_type' => ['nullable', 'string', 'in:cdi,cdd,prestataire'],
            'contract_end_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:active,on_leave,inactive'],
            'hire_date' => ['nullable', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'employee_id' => ['required', 'string', 'max:255', 'unique:school_staff,employee_id,' . $id],
            'login_id' => ['nullable', 'string', 'max:255', 'unique:users,login_id,' . ($linkedPortalUserId ?? 'NULL')],
            'password' => ['nullable', 'string'],
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        $data['phone'] = \App\Modules\SuperAdmin\Domain\Models\Country::combinePhone($data['phone_country_code'] ?? null, $data['phone_number'] ?? null);
        unset($data['phone_country_code'], $data['phone_number']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('staff/photos', 'public');
        }

        $dto = new UpdateStaffDTO($data);
        $useCase->execute($id, $dto);

        return redirect()->route('school.academic.personnel')->with('success', 'Membre du personnel mis à jour avec succès !');
    }

    public function destroyPersonnel($id, DeleteStaffUseCase $useCase)
    {
        $useCase->execute($id);
        return redirect()->route('school.academic.personnel')->with('success', 'Membre du personnel supprimé avec succès.');
    }

    public function rooms(
        \App\Modules\Academic\Domain\Repositories\BuildingRepositoryInterface $buildingRepository,
        \App\Modules\Academic\Domain\Repositories\RoomRepositoryInterface $roomRepository
    ) {
        $buildings = $buildingRepository->all();
        $rooms = $roomRepository->all();
        return view('SchoolDashboard::academic.rooms', compact('buildings', 'rooms'));
    }

    public function storeBuilding(\Illuminate\Http\Request $request, \App\Modules\Academic\Application\UseCases\Building\CreateBuildingUseCase $useCase)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);
        $dto = new \App\Modules\Academic\Application\DTOs\Building\CreateBuildingDTO($validated);
        $useCase->execute($dto);
        return redirect()->route('school.academic.rooms')->with('success', 'Bâtiment ajouté avec succès.');
    }

    public function updateBuilding($id, \Illuminate\Http\Request $request, \App\Modules\Academic\Application\UseCases\Building\UpdateBuildingUseCase $useCase)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);
        $dto = new \App\Modules\Academic\Application\DTOs\Building\UpdateBuildingDTO($validated);
        $useCase->execute($id, $dto);
        return redirect()->route('school.academic.rooms')->with('success', 'Bâtiment modifié avec succès.');
    }

    public function destroyBuilding($id, \App\Modules\Academic\Application\UseCases\Building\DeleteBuildingUseCase $useCase)
    {
        $useCase->execute($id);
        return redirect()->route('school.academic.rooms')->with('success', 'Bâtiment supprimé avec succès.');
    }

    public function storeRoom(\Illuminate\Http\Request $request, \App\Modules\Academic\Application\UseCases\Room\CreateRoomUseCase $useCase)
    {
        $validated = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1'
        ]);
        $dto = new \App\Modules\Academic\Application\DTOs\Room\CreateRoomDTO($validated);
        $useCase->execute($dto);
        return redirect()->route('school.academic.rooms')->with('success', 'Salle ajoutée avec succès.');
    }

    public function updateRoom($id, \Illuminate\Http\Request $request, \App\Modules\Academic\Application\UseCases\Room\UpdateRoomUseCase $useCase)
    {
        $validated = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1'
        ]);
        $dto = new \App\Modules\Academic\Application\DTOs\Room\UpdateRoomDTO($validated);
        $useCase->execute($id, $dto);
        return redirect()->route('school.academic.rooms')->with('success', 'Salle modifiée avec succès.');
    }

    public function destroyRoom($id, \App\Modules\Academic\Application\UseCases\Room\DeleteRoomUseCase $useCase)
    {
        $useCase->execute($id);
        return redirect()->route('school.academic.rooms')->with('success', 'Salle supprimée avec succès.');
    }
}
