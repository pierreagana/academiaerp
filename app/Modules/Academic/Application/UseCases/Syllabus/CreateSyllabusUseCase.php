<?php

namespace App\Modules\Academic\Application\UseCases\Syllabus;

use App\Modules\Academic\Domain\Repositories\SyllabusRepositoryInterface;
use App\Modules\Academic\Application\DTOs\CreateSyllabusDTO;

class CreateSyllabusUseCase
{
    private $repository;
    public function __construct(SyllabusRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute(CreateSyllabusDTO $dto) { return $this->repository->create($dto->data); }
}
