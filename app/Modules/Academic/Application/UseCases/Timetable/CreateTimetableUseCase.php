<?php

namespace App\Modules\Academic\Application\UseCases\Timetable;

use App\Modules\Academic\Domain\Repositories\TimetableRepositoryInterface;
use App\Modules\Academic\Application\DTOs\CreateTimetableDTO;

class CreateTimetableUseCase
{
    private $repository;
    public function __construct(TimetableRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute(CreateTimetableDTO $dto) { return $this->repository->create($dto->data); }
}
