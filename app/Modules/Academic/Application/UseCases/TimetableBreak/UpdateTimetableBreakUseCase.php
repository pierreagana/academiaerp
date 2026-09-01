<?php

namespace App\Modules\Academic\Application\UseCases\TimetableBreak;

use App\Modules\Academic\Domain\Repositories\TimetableBreakRepositoryInterface;
use App\Modules\Academic\Application\DTOs\UpdateTimetableBreakDTO;

class UpdateTimetableBreakUseCase
{
    private $repository;
    public function __construct(TimetableBreakRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute($id, UpdateTimetableBreakDTO $dto) { return $this->repository->update($id, $dto->data); }
}
