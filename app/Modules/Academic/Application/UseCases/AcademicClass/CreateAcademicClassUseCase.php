<?php

namespace App\Modules\Academic\Application\UseCases\AcademicClass;

use App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface;
use App\Modules\Academic\Application\DTOs\CreateAcademicClassDTO;

class CreateAcademicClassUseCase
{
    private $repository;
    public function __construct(AcademicClassRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute(CreateAcademicClassDTO $dto) { return $this->repository->create($dto->data); }
}
