<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\Lesson;
use App\Modules\Academic\Domain\Repositories\LessonRepositoryInterface;

class EloquentLessonRepository implements LessonRepositoryInterface
{
    public function find($id): ?Lesson
    {
        return Lesson::find($id);
    }

    public function getBySyllabusId($syllabusId)
    {
        return Lesson::where('syllabus_id', $syllabusId)->orderBy('order')->get();
    }

    public function create(array $data): Lesson
    {
        return Lesson::create($data);
    }

    public function update($id, array $data): bool
    {
        $lesson = Lesson::find($id);
        if ($lesson) {
            return $lesson->update($data);
        }
        return false;
    }

    public function delete($id): bool
    {
        $lesson = Lesson::find($id);
        if ($lesson) {
            return $lesson->delete();
        }
        return false;
    }
}
