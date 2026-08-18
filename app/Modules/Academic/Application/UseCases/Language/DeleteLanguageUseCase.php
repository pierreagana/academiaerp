<?php

namespace App\Modules\Academic\Application\UseCases\Language;

use App\Modules\Academic\Domain\Repositories\LanguageRepositoryInterface;

class DeleteLanguageUseCase
{
    private $repository;
    public function __construct(LanguageRepositoryInterface $repository) { 
        $this->repository = $repository; 
    }
    public function execute($id) { 
        return $this->repository->delete($id); 
    }
}
