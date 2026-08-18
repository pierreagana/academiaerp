<?php

namespace App\Modules\Academic\Application\UseCases\Language;

use App\Modules\Academic\Domain\Repositories\LanguageRepositoryInterface;
use App\Modules\Academic\Application\DTOs\CreateLanguageDTO;

class CreateLanguageUseCase
{
    private $repository;
    public function __construct(LanguageRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute(CreateLanguageDTO $dto) { return $this->repository->create($dto->data); }
}
