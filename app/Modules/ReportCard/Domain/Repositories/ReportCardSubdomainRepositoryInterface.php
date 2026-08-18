<?php

namespace App\Modules\ReportCard\Domain\Repositories;

interface ReportCardSubdomainRepositoryInterface
{
    public function create(array $data);

    public function delete($id);
}
