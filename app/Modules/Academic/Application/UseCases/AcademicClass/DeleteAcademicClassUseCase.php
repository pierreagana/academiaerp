<?php

namespace App\Modules\Academic\Application\UseCases\AcademicClass;

use App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface;

class DeleteAcademicClassUseCase
{
    private $repository;
    public function __construct(AcademicClassRepositoryInterface $repository) { 
        $this->repository = $repository; 
    }
    public function execute($id) { 
        return $this->repository->delete($id); 
    }
}
