<?php

namespace App\Modules\SuperAdmin\Domain\Repositories;

interface SupportTicketRepositoryInterface
{
    public function getAll(): array;
}
