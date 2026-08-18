<?php

namespace App\Modules\Academic\Application\UseCases\Semester;

use App\Modules\Academic\Domain\Repositories\SemesterRepositoryInterface;
use App\Modules\Academic\Application\DTOs\UpdateSemesterDTO;

class UpdateSemesterUseCase
{
    private $repository;
    public function __construct(SemesterRepositoryInterface $repository) { 
        $this->repository = $repository; 
    }
    public function execute($id, UpdateSemesterDTO $dto) { 
        return $this->repository->update($id, $dto->data); 
    }
}
