<?php

namespace App\Modules\ReportCard\Domain\Repositories;

interface ReportCardDomainRepositoryInterface
{
    public function allWithTree();

    public function find($id);

    public function create(array $data);

    public function delete($id);
}
