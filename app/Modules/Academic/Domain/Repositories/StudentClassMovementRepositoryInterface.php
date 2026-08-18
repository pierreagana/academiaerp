<?php

namespace App\Modules\Academic\Domain\Repositories;

interface StudentClassMovementRepositoryInterface
{
    public function create(array $data);

    public function recent(?string $type = null, int $limit = 15);
}
