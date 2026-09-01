<?php

namespace App\Modules\Academic\Application\UseCases\TimetableBreak;

use App\Modules\Academic\Domain\Repositories\TimetableBreakRepositoryInterface;
use App\Modules\Academic\Application\DTOs\CreateTimetableBreakDTO;

class CreateTimetableBreakUseCase
{
    private $repository;
    public function __construct(TimetableBreakRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute(CreateTimetableBreakDTO $dto) { return $this->repository->create($dto->data); }
}
