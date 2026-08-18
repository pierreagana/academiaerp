<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\SupportTicketRepositoryInterface;

class ListSupportTicketsUseCase
{
    public function __construct(
        private SupportTicketRepositoryInterface $ticketRepository
    ) {}

    public function execute(): array
    {
        return $this->ticketRepository->getAll();
    }
}
