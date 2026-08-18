<?php

namespace App\Modules\Bulletin\Infrastructure\Repositories;

use App\Modules\Bulletin\Domain\Models\BulletinPublication;
use App\Modules\Bulletin\Domain\Repositories\BulletinPublicationRepositoryInterface;

class EloquentBulletinPublicationRepository implements BulletinPublicationRepositoryInterface
{
    public function findOrCreate(int $academicClassId, int $semesterId)
    {
        return BulletinPublication::firstOrCreate(
            ['academic_class_id' => $academicClassId, 'semester_id' => $semesterId],
            ['status' => BulletinPublication::STATUS_DRAFT]
        );
    }

    public function updateStatus(int $academicClassId, int $semesterId, array $data)
    {
        $publication = $this->findOrCreate($academicClassId, $semesterId);
        $publication->update($data);

        return $publication;
    }
}
