<?php

namespace App\Modules\Bulletin\Domain\Repositories;

interface BulletinTemplateRepositoryInterface
{
    public function findOrDefault(int $schoolId);

    public function save(int $schoolId, array $data);
}
