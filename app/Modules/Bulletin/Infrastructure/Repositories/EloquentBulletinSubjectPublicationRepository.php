<?php

namespace App\Modules\Bulletin\Infrastructure\Repositories;

use App\Modules\Bulletin\Domain\Models\BulletinSubjectPublication;
use App\Modules\Bulletin\Domain\Repositories\BulletinSubjectPublicationRepositoryInterface;

class EloquentBulletinSubjectPublicationRepository implements BulletinSubjectPublicationRepositoryInterface
{
    public function findOrCreate(int $academicClassId, int $subjectId, int $semesterId)
    {
        return BulletinSubjectPublication::firstOrCreate(
            ['academic_class_id' => $academicClassId, 'subject_id' => $subjectId, 'semester_id' => $semesterId],
            ['status' => BulletinSubjectPublication::STATUS_DRAFT]
        );
    }

    public function updateStatus(int $academicClassId, int $subjectId, int $semesterId, array $data)
    {
        $publication = $this->findOrCreate($academicClassId, $subjectId, $semesterId);
        $publication->update($data);

        return $publication;
    }

    public function publishedSubjectIds(int $academicClassId, int $semesterId): array
    {
        return BulletinSubjectPublication::where('academic_class_id', $academicClassId)
            ->where('semester_id', $semesterId)
            ->where('status', BulletinSubjectPublication::STATUS_PUBLISHED)
            ->pluck('subject_id')
            ->all();
    }
}
