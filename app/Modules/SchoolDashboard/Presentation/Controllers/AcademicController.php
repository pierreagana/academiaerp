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
            'level' => ['required', 'string', 'max:100'],
            'cycle' => ['nullable', 'string', 'in:Cycle 1,Cycle 2,Cycle 3'],
        ], $messages);

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
            'level' => ['required', 'string', 'max:100'],
            'cycle' => ['nullable', 'string', 'in:Cycle 1,Cycle 2,Cycle 3'],
        ], $messages);

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
        return view('SchoolDashboard::academic.semesters', compact('semesters', 'editSemester'));
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
            'academic_year' => ['nullable', 'string', 'max:20'],
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
            'academic_year' => ['nullable', 'string', 'max:20'],
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

    public function syllabuses(
        \Illuminate\Http\Request $request,
        \App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository,
        \App\Modules\Academic\Domain\Repositories\SemesterRepositoryInterface $semesterRepository
    ) {
        $classId = $request->get('class_id');
        $semesterId = $request->get('semester_id');

        $query = \App\Modules\Academic\Domain\Models\Syllabus::whereHas('academicClass', function ($q) {
            $q->where('school_id', auth()->user()->school_id);
        })->with(['academicClass', 'semester', 'subject']);

        if ($classId) {
            $query->where('academic_class_id', $classId);
        }
        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        $teacher = auth()->user()->teacher;
        if ($teacher) {
            $query->whereIn('subject_id', $teacher->subjects->pluck('id'));
        }

        $syllabuses = $query->get();
        $classes = $classRepository->all();
        $semesters = $semesterRepository->all();

        return view('SchoolDashboard::academic.syllabus', compact('syllabuses', 'classes', 'semesters', 'classId', 'semesterId'));
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
            $subjects = $subjects->filter(fn ($subject) => $teacher->teachesSubject($subject->id))->values();
        }

        return view('SchoolDashboard::academic.syllabus_create', compact('classes', 'semesters', 'subjects'));
    }

    public function storeSyllabus(
        \Illuminate\Http\Request $request,
        \App\Modules\Academic\Application\UseCases\Syllabus\CreateSyllabusUseCase $useCase
    ) {
        $request->validate([
            'academic_class_id' => 'required|exists:academic_classes,id',
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

        foreach ($request->subjects as $subjectId) {
            $exists = \App\Modules\Academic\Domain\Models\Syllabus::whereHas('academicClass', function ($q) {
                    $q->where('school_id', auth()->user()->school_id);
                })
                ->where('academic_class_id', $request->academic_class_id)
                ->where('semester_id', $request->semester_id)
                ->where('subject_id', $subjectId)
                ->exists();
                
            if (!$exists) {
                $useCase->execute(new \App\Modules\Academic\Application\DTOs\CreateSyllabusDTO([
                    'academic_class_id' => $request->academic_class_id,
                    'semester_id' => $request->semester_id,
                    'subject_id' => $subjectId,
                ]));
            }
        }
        
        return redirect()->route('school.academic.syllabuses')->with('success', 'Matières assignées avec succès au programme !');
    }

    public function destroySyllabus($id, \App\Modules\Academic\Domain\Repositories\SyllabusRepositoryInterface $repository)
    {
        $repository->delete($id);
        return redirect()->route('school.academic.syllabuses')->with('success', 'Matière retirée du programme avec succès !');
    }

    public function timetable(
        \Illuminate\Http\Request $request,
        \App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository,
        \App\Modules\Academic\Domain\Repositories\RoomRepositoryInterface $roomRepository
    ) {
        $classes = $classRepository->all();
        $classId = $request->get('class_id');

        $timetables = [];
        $totalHours = 0;
        $uniqueTeachers = [];

        // Ensures the requested class actually belongs to this school before using its id below.
        if ($classId && !$classes->contains('id', (int) $classId)) {
            $classId = null;
        }

        if ($classId) {
            $timetables = \App\Modules\Academic\Domain\Models\Timetable::with(['subject', 'teacher'])
                ->where('academic_class_id', $classId)
                ->where('status', 'published')
                ->get();

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

        foreach($allRooms as $room) {
            $roomSchedules = $todaysTimetables->get($room->id, collect());
            
            // Cours actuellement en cours
            $currentClass = $roomSchedules->first(function($t) use ($currentTime) {
                return $t->start_time <= $currentTime && $t->end_time > $currentTime;
            });

            if ($currentClass) {
                // Occupé actuellement
                $freeRoomsData[] = (object)[
                    'name' => $room->name,
                    'status' => 'Dès ' . substr($currentClass->end_time, 0, 5),
                    'is_free_now' => false
                ];
            } else {
                // Libre actuellement. Y a-t-il un cours plus tard ?
                $nextClass = $roomSchedules->first(function($t) use ($currentTime) {
                    return $t->start_time > $currentTime;
                });
                
                if ($nextClass) {
                    $freeRoomsData[] = (object)[
                        'name' => $room->name,
                        'status' => 'Jsq ' . substr($nextClass->start_time, 0, 5),
                        'is_free_now' => true
                    ];
                } else {
                    $freeRoomsData[] = (object)[
                        'name' => $room->name,
                        'status' => 'Libre',
                        'is_free_now' => true
                    ];
                }
            }
        }
        
        // Trier : Libres d'abord, puis occupées
        usort($freeRoomsData, function($a, $b) {
            if ($a->is_free_now && !$b->is_free_now) return -1;
            if (!$a->is_free_now && $b->is_free_now) return 1;
            return 0;
        });

        // Prendre les 4 premières
        $freeRooms = array_slice($freeRoomsData, 0, 4);

        return view('SchoolDashboard::academic.timetable', compact('classes', 'classId', 'timetables', 'stats', 'freeRooms'));
    }

    public function createTimetable(
        \Illuminate\Http\Request $request,
        \App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository,
        \App\Modules\Academic\Domain\Repositories\SemesterRepositoryInterface $semesterRepository,
        \App\Modules\Academic\Domain\Repositories\SubjectRepositoryInterface $subjectRepository,
        \App\Modules\Academic\Domain\Repositories\TeacherRepositoryInterface $teacherRepository,
        \App\Modules\Academic\Domain\Repositories\RoomRepositoryInterface $roomRepository
    ) {
        $classes = $classRepository->all();
        $semesters = $semesterRepository->all();
        $teachers = $teacherRepository->all();
        $rooms = $roomRepository->all();

        $classId = $request->get('class_id');
        $existingTimetables = collect();

        // Ensures the requested class actually belongs to this school before using its id below.
        if ($classId && !$classes->contains('id', (int) $classId)) {
            $classId = null;
        }

        if ($classId) {
            $selectedClass = \App\Modules\Academic\Domain\Models\AcademicClass::with('subjects.teachers')->find($classId);
            $subjects = $selectedClass ? $selectedClass->subjects : collect();

            $existingTimetables = \App\Modules\Academic\Domain\Models\Timetable::with(['subject', 'teacher', 'room'])
                ->where('academic_class_id', $classId)
                ->get();

            $otherTimetables = \App\Modules\Academic\Domain\Models\Timetable::with(['academicClass', 'room', 'teacher'])
                ->whereHas('academicClass', function ($q) {
                    $q->where('school_id', auth()->user()->school_id);
                })
                ->where('academic_class_id', '!=', $classId)
                ->get();
        } else {
            $subjects = collect();
            $otherTimetables = collect();
        }

        return view('SchoolDashboard::academic.timetable_create', compact('classes', 'semesters', 'subjects', 'teachers', 'rooms', 'classId', 'existingTimetables', 'otherTimetables'));
    }

    public function storeTimetable(\Illuminate\Http\Request $request, \App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository)
    {
        $request->validate([
            'class_id' => 'required|exists:academic_classes,id',
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
        $blocks = $request->input('blocks', []);

        $status = $request->input('status', 'draft');

        // Throws 404 if the class doesn't belong to this school.
        $classRepository->find($classId);

        \App\Modules\Academic\Domain\Models\Timetable::where('academic_class_id', $classId)->delete();

        foreach ($blocks as $block) {
            \App\Modules\Academic\Domain\Models\Timetable::create([
                'academic_class_id' => $classId,
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

    public function students(\App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface $repository)
    {
        $students = $repository->paginate(10);
        $schoolId = auth()->user()->school_id;
        $branchId = auth()->user()->activeBranchId();
        $stats = [
            'total' => \App\Modules\Academic\Domain\Models\Student::where('school_id', $schoolId)->whereBranch($branchId)->count(),
            'active' => \App\Modules\Academic\Domain\Models\Student::where('school_id', $schoolId)->whereBranch($branchId)->where('status', 'active')->count(),
            'inactive' => \App\Modules\Academic\Domain\Models\Student::where('school_id', $schoolId)->whereBranch($branchId)->where('status', 'inactive')->count(),
            'recent' => \App\Modules\Academic\Domain\Models\Student::where('school_id', $schoolId)->whereBranch($branchId)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
        return view('SchoolDashboard::academic.students', compact('students', 'stats'));
    }

    public function createStudent(\App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface $classRepository, \App\Modules\Academic\Domain\Repositories\GuardianRepositoryInterface $guardianRepository)
    {
        $classes = $classRepository->all();
        $guardians = $guardianRepository->all();
        return view('SchoolDashboard::academic.student_create', compact('classes', 'guardians'));
    }

    public function storeStudent(Request $request, CreateStudentUseCase $useCase)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
            'gender' => ['required', 'string', 'in:male,female'],
            'email' => ['nullable', 'email', 'max:255'],
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'academic_year' => ['required', 'string', 'max:20'],
            'roll_number' => ['nullable', 'string', 'max:50', 'unique:students,roll_number'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'allergies' => ['nullable', 'string'],
            'medical_conditions' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'guardian_ids' => ['nullable', 'array'],
            'guardian_ids.*' => ['exists:guardians,id'],
        ]);

        $guardianIds = $data['guardian_ids'] ?? [];
        unset($data['guardian_ids']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('students/photos', 'public');
        }

        if (empty($data['roll_number'])) {
            $data['roll_number'] = 'MAT-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));
        }

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
        return view('SchoolDashboard::academic.student_create', compact('student', 'classes', 'guardians'));
    }

    public function updateStudent($id, Request $request, UpdateStudentUseCase $useCase)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
            'gender' => ['required', 'string', 'in:male,female'],
            'email' => ['nullable', 'email', 'max:255'],
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'academic_year' => ['required', 'string', 'max:20'],
            'roll_number' => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique('students')->ignore($id)],
            'status' => ['required', 'string', 'in:active,inactive'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'allergies' => ['nullable', 'string'],
            'medical_conditions' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'guardian_ids' => ['nullable', 'array'],
            'guardian_ids.*' => ['exists:guardians,id'],
        ]);

        $guardianIds = $data['guardian_ids'] ?? [];
        unset($data['guardian_ids']);

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
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:students,id'],
        ]);

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
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

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
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:students,id'],
        ]);

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
            'risk' => 3, // Simulate predictive AI for now
        ];
        return view('SchoolDashboard::academic.teachers', compact('teachers', 'stats', 'subjects', 'filters'));
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
            'phone' => ['nullable', 'string', 'max:20'],
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
            'phone' => ['nullable', 'string', 'max:20'],
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
            'phone' => ['nullable', 'string', 'max:20'],
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
            'phone' => ['nullable', 'string', 'max:20'],
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
