<?php

namespace App\Modules\HR\Domain\Repositories;

interface SalaryGradeRepositoryInterface
{
    public function create(array $data);

    public function all();
}
