<?php

namespace App\Modules\Academic\Application\UseCases\TimetableBreak;

use App\Modules\Academic\Domain\Repositories\TimetableBreakRepositoryInterface;

class DeleteTimetableBreakUseCase
{
    private $repository;
    public function __construct(TimetableBreakRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute($id) { return $this->repository->delete($id); }
}
