<?php

namespace App\Modules\Presence\Domain\Repositories;

interface AccessLogRepositoryInterface
{
    public function create(array $data);

    public function paginate(array $filters, int $perPage = 15);

    public function lastForHolder(string $holderType, int $holderId);
}
