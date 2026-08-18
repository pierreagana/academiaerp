<?php

namespace App\Modules\Academic\Application\UseCases\Subject;

use App\Modules\Academic\Domain\Repositories\SubjectRepositoryInterface;
use App\Modules\Academic\Application\DTOs\UpdateSubjectDTO;

class UpdateSubjectUseCase
{
    private $repository;
    public function __construct(SubjectRepositoryInterface $repository) { 
        $this->repository = $repository; 
    }
    public function execute($id, UpdateSubjectDTO $dto) { 
        return $this->repository->update($id, $dto->data); 
    }
}
