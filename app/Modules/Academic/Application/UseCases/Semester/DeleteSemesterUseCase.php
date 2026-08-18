<?php

namespace App\Modules\Academic\Application\UseCases\Semester;

use App\Modules\Academic\Domain\Repositories\SemesterRepositoryInterface;

class DeleteSemesterUseCase
{
    private $repository;
    public function __construct(SemesterRepositoryInterface $repository) { 
        $this->repository = $repository; 
    }
    public function execute($id) { 
        return $this->repository->delete($id); 
    }
}
