<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Academic\Domain\Models\Syllabus;
use App\Modules\Academic\Domain\Repositories\LessonRepositoryInterface;
use App\Modules\Academic\Application\UseCases\Lesson\CreateLessonUseCase;
use App\Modules\Academic\Application\UseCases\Lesson\UpdateLessonUseCase;
use App\Modules\Academic\Application\UseCases\Lesson\DeleteLessonUseCase;
use App\Modules\Academic\Application\DTOs\Lesson\CreateLessonDTO;
use App\Modules\Academic\Application\DTOs\Lesson\UpdateLessonDTO;

class LessonController extends Controller
{
    public function index(Syllabus $syllabus, LessonRepositoryInterface $lessonRepository)
    {
        $syllabus->load(['academicClass', 'semester', 'subject']);
        $lessons = $lessonRepository->getBySyllabusId($syllabus->id);
        
        return view('SchoolDashboard::academic.lessons', compact('syllabus', 'lessons'));
    }

    public function create(Syllabus $syllabus)
    {
        $syllabus->load(['academicClass', 'semester', 'subject']);
        return view('SchoolDashboard::academic.lesson_create', compact('syllabus'));
    }

    public function store(Request $request, Syllabus $syllabus, CreateLessonUseCase $useCase)
    {
        $request->validate([
            'lessons' => 'required|array|min:1',
            'lessons.*.title' => 'required|string|max:255',
            'lessons.*.lesson_titles' => 'nullable|array',
            'lessons.*.lesson_titles.*' => 'required|string',
            'lessons.*.order' => 'required|integer|min:1',
            'lessons.*.status' => 'required|in:draft,published',
            'lessons.*.file' => 'nullable|file|max:5120', // 5MB max
        ]);

        foreach ($request->input('lessons') as $index => $lessonData) {
            $data = $lessonData;
            $data['syllabus_id'] = $syllabus->id;
            
            // Handle file upload if any
            if ($request->hasFile("lessons.$index.file")) {
                $path = $request->file("lessons.$index.file")->store('lessons', 'public');
                $data['file_path'] = $path;
            }

            $useCase->execute(new CreateLessonDTO($data));
        }

        return redirect()->route('school.academic.lessons.index', $syllabus->id)
            ->with('success', 'Leçon(s) créée(s) avec succès.');
    }

    public function edit(Syllabus $syllabus, $lessonId, LessonRepositoryInterface $lessonRepository)
    {
        $syllabus->load(['academicClass', 'semester', 'subject']);
        $lesson = $lessonRepository->find($lessonId);
        
        if (!$lesson || $lesson->syllabus_id !== $syllabus->id) {
            abort(404);
        }

        return view('SchoolDashboard::academic.lesson_edit', compact('syllabus', 'lesson'));
    }

    public function update(Request $request, Syllabus $syllabus, $lessonId, UpdateLessonUseCase $useCase, LessonRepositoryInterface $lessonRepository)
    {
        $lesson = $lessonRepository->find($lessonId);
        
        if (!$lesson || $lesson->syllabus_id !== $syllabus->id) {
            abort(404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'lesson_titles' => 'nullable|array',
            'lesson_titles.*' => 'required|string',
            'order' => 'required|integer|min:1',
            'status' => 'required|in:draft,published',
        ]);

        $data = $request->all();
        
        // Handle file upload if any
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('lessons', 'public');
            $data['file_path'] = $path;
        }

        $useCase->execute($lessonId, new UpdateLessonDTO($data));

        return redirect()->route('school.academic.lessons.index', $syllabus->id)
            ->with('success', 'Leçon mise à jour avec succès.');
    }

    public function destroy(Syllabus $syllabus, $lessonId, DeleteLessonUseCase $useCase, LessonRepositoryInterface $lessonRepository)
    {
        $lesson = $lessonRepository->find($lessonId);
        
        if (!$lesson || $lesson->syllabus_id !== $syllabus->id) {
            abort(404);
        }

        $useCase->execute($lessonId);

        return redirect()->route('school.academic.lessons.index', $syllabus->id)
            ->with('success', 'Leçon supprimée avec succès.');
    }
}
