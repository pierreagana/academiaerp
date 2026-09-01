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

        // Same subject taught to other sections of the same grade level, in the
        // same semester — lets a teacher enter a chapter once and duplicate it
        // across parallel classes (e.g. 6ème A/B/C) instead of re-typing the
        // same content per class. Restricted to the same level (not just the
        // same subject) since e.g. "Français" in 6ème and in 4ème are not the
        // same curriculum despite sharing one Subject row.
        $otherClassSyllabuses = Syllabus::where('subject_id', $syllabus->subject_id)
            ->where('semester_id', $syllabus->semester_id)
            ->where('id', '!=', $syllabus->id)
            ->whereHas('academicClass', function ($q) use ($syllabus) {
                $q->where('school_id', auth()->user()->school_id)
                    ->where('level', $syllabus->academicClass->level);
            })
            ->with('academicClass')
            ->get();

        return view('SchoolDashboard::academic.lesson_create', compact('syllabus', 'otherClassSyllabuses'));
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
            'target_syllabus_ids' => 'nullable|array',
            'target_syllabus_ids.*' => 'integer',
        ]);

        // Only the syllabus in the URL by default — plus whichever sibling
        // classes (same subject + semester) the teacher explicitly checked,
        // so the same chapters get duplicated instead of re-typed per class.
        $syllabus->loadMissing('academicClass');

        $targetSyllabuses = Syllabus::where('id', $syllabus->id)
            ->orWhere(function ($q) use ($request, $syllabus) {
                $q->whereIn('id', $request->input('target_syllabus_ids', []))
                    ->where('subject_id', $syllabus->subject_id)
                    ->where('semester_id', $syllabus->semester_id)
                    ->whereHas('academicClass', fn ($qc) => $qc->where('school_id', auth()->user()->school_id)
                        ->where('level', $syllabus->academicClass->level));
            })
            ->get();

        // Files are uploaded once and the same stored path is reused for
        // every target class's copy of the lesson.
        $filePaths = [];
        foreach ($request->input('lessons') as $index => $lessonData) {
            if ($request->hasFile("lessons.$index.file")) {
                $filePaths[$index] = $request->file("lessons.$index.file")->store('lessons', 'public');
            }
        }

        foreach ($targetSyllabuses as $targetSyllabus) {
            foreach ($request->input('lessons') as $index => $lessonData) {
                $data = $lessonData;
                $data['syllabus_id'] = $targetSyllabus->id;

                // Format lesson_titles into structured items
                if (!empty($lessonData['lesson_titles']) && is_array($lessonData['lesson_titles'])) {
                    $formattedTitles = [];
                    foreach ($lessonData['lesson_titles'] as $titleStr) {
                        if (is_string($titleStr) && trim($titleStr) !== '') {
                            $formattedTitles[] = [
                                'title' => trim($titleStr),
                                'status' => 'not_started',
                                'started_at' => null,
                                'completed_at' => null,
                            ];
                        } elseif (is_array($titleStr)) {
                            $formattedTitles[] = $titleStr;
                        }
                    }
                    $data['lesson_titles'] = $formattedTitles;
                }

                if (isset($filePaths[$index])) {
                    $data['file_path'] = $filePaths[$index];
                }

                $createdLesson = $useCase->execute(new CreateLessonDTO($data));
                $createdLesson->recalculateChapterProgress();
            }
        }

        $classCount = $targetSyllabuses->count();
        $message = $classCount > 1
            ? "Leçon(s) créée(s) avec succès pour {$classCount} classes."
            : 'Leçon(s) créée(s) avec succès.';

        return redirect()->route('school.academic.lessons.index', $syllabus->id)
            ->with('success', $message);
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
        $existingSubLessons = $lesson->sub_lessons;

        if (!empty($data['lesson_titles']) && is_array($data['lesson_titles'])) {
            $formattedTitles = [];
            foreach ($data['lesson_titles'] as $idx => $titleStr) {
                $existing = $existingSubLessons[$idx] ?? null;
                $formattedTitles[] = [
                    'title' => is_string($titleStr) ? trim($titleStr) : ($titleStr['title'] ?? ''),
                    'status' => $existing['status'] ?? 'not_started',
                    'started_at' => $existing['started_at'] ?? null,
                    'completed_at' => $existing['completed_at'] ?? null,
                ];
            }
            $data['lesson_titles'] = $formattedTitles;
        }
        
        // Handle file upload if any
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('lessons', 'public');
            $data['file_path'] = $path;
        }

        $useCase->execute($lessonId, new UpdateLessonDTO($data));

        $refreshedLesson = $lessonRepository->find($lessonId);
        $refreshedLesson?->recalculateChapterProgress();

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

    public function updateSubLessonProgress(Request $request, Syllabus $syllabus, $lessonId, LessonRepositoryInterface $lessonRepository)
    {
        $lesson = $lessonRepository->find($lessonId);

        if (!$lesson || $lesson->syllabus_id !== $syllabus->id) {
            abort(404);
        }

        $request->validate([
            'index' => 'required|integer|min:0',
            'status' => 'required|in:not_started,in_progress,completed',
        ]);

        $index = (int) $request->input('index');
        $newStatus = $request->input('status');
        $subLessons = $lesson->sub_lessons;

        if (!isset($subLessons[$index])) {
            abort(422, 'Leçon introuvable.');
        }

        $now = now()->format('Y-m-d H:i:s');
        $currentSub = $subLessons[$index];

        if ($newStatus === 'in_progress') {
            $currentSub['status'] = 'in_progress';
            $currentSub['started_at'] = $currentSub['started_at'] ?: $now;
            $currentSub['completed_at'] = null;
        } elseif ($newStatus === 'completed') {
            $currentSub['status'] = 'completed';
            $currentSub['started_at'] = $currentSub['started_at'] ?: $now;
            $currentSub['completed_at'] = $now;
        } else {
            $currentSub['status'] = 'not_started';
            $currentSub['started_at'] = null;
            $currentSub['completed_at'] = null;
        }

        $subLessons[$index] = $currentSub;
        $lesson->lesson_titles = $subLessons;
        $lesson->recalculateChapterProgress();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Progression mise à jour avec succès.',
                'sub_lesson' => $currentSub,
                'chapter_status' => $lesson->progress_status,
                'chapter_started_at' => $lesson->started_at ? $lesson->started_at->translatedFormat('d M Y, H:i') : null,
                'chapter_completed_at' => $lesson->completed_at ? $lesson->completed_at->translatedFormat('d M Y, H:i') : null,
                'progress_percentage' => $lesson->progress_percentage,
            ]);
        }

        return redirect()->route('school.academic.lessons.index', $syllabus->id)
            ->with('success', 'Statut de la leçon mis à jour.');
    }
}
