<?php

namespace App\Modules\Academic\Application\UseCases\Subject;

use App\Modules\Academic\Domain\Repositories\SubjectRepositoryInterface;

class DeleteSubjectUseCase
{
    private $repository;
    public function __construct(SubjectRepositoryInterface $repository) { 
        $this->repository = $repository; 
    }
    public function execute($id) { 
        return $this->repository->delete($id); 
    }
}
