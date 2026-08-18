<?php

namespace App\Modules\Bulletin\Domain\Repositories;

interface BulletinSubjectPublicationRepositoryInterface
{
    public function findOrCreate(int $academicClassId, int $subjectId, int $semesterId);

    public function updateStatus(int $academicClassId, int $subjectId, int $semesterId, array $data);

    /** @return int[] subject_id list currently published for this class+semester */
    public function publishedSubjectIds(int $academicClassId, int $semesterId): array;
}
