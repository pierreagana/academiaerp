<?php

namespace App\Modules\Academic\Application\UseCases\Language;

use App\Modules\Academic\Domain\Repositories\LanguageRepositoryInterface;
use App\Modules\Academic\Application\DTOs\UpdateLanguageDTO;

class UpdateLanguageUseCase
{
    private $repository;
    public function __construct(LanguageRepositoryInterface $repository) { 
        $this->repository = $repository; 
    }
    public function execute($id, UpdateLanguageDTO $dto) { 
        return $this->repository->update($id, $dto->data); 
    }
}
