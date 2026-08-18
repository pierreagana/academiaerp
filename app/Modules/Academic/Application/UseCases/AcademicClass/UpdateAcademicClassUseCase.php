<?php

namespace App\Modules\Academic\Application\UseCases\AcademicClass;

use App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface;
use App\Modules\Academic\Application\DTOs\UpdateAcademicClassDTO;

class UpdateAcademicClassUseCase
{
    private $repository;
    public function __construct(AcademicClassRepositoryInterface $repository) { 
        $this->repository = $repository; 
    }
    public function execute($id, UpdateAcademicClassDTO $dto) { 
        return $this->repository->update($id, $dto->data); 
    }
}
