<?php

namespace App\Modules\Bulletin\Domain\Repositories;

interface BulletinPublicationRepositoryInterface
{
    public function findOrCreate(int $academicClassId, int $semesterId);

    public function updateStatus(int $academicClassId, int $semesterId, array $data);
}
