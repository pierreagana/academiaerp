<?php

namespace App\Modules\Library\Domain\Repositories;

interface LibrarySettingRepositoryInterface
{
    public function getForSchool(int $schoolId);

    public function updateRules(int $schoolId, array $data);

    public function updateAccess(int $schoolId, array $data);
}
