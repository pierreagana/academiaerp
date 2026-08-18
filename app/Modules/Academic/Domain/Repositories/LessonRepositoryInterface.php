<?php

namespace App\Modules\Academic\Domain\Repositories;

use App\Modules\Academic\Domain\Models\Lesson;

interface LessonRepositoryInterface
{
    public function find($id): ?Lesson;
    public function getBySyllabusId($syllabusId);
    public function create(array $data): Lesson;
    public function update($id, array $data): bool;
    public function delete($id): bool;
}
