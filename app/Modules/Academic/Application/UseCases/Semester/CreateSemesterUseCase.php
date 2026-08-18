<?php

namespace App\Modules\Academic\Application\UseCases\Semester;

use App\Modules\Academic\Domain\Repositories\SemesterRepositoryInterface;
use App\Modules\Academic\Application\DTOs\CreateSemesterDTO;

class CreateSemesterUseCase
{
    private $repository;
    public function __construct(SemesterRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute(CreateSemesterDTO $dto) { return $this->repository->create($dto->data); }
}
