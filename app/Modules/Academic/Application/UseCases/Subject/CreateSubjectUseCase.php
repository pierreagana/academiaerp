<?php

namespace App\Modules\Academic\Application\UseCases\Subject;

use App\Modules\Academic\Domain\Repositories\SubjectRepositoryInterface;
use App\Modules\Academic\Application\DTOs\CreateSubjectDTO;

class CreateSubjectUseCase
{
    private $repository;
    public function __construct(SubjectRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute(CreateSubjectDTO $dto) { return $this->repository->create($dto->data); }
}
