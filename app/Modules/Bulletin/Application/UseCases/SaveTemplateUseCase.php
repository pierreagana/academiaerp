<?php

namespace App\Modules\Bulletin\Application\UseCases;

use App\Modules\Bulletin\Application\DTOs\SaveTemplateDTO;
use App\Modules\Bulletin\Domain\Repositories\BulletinTemplateRepositoryInterface;

class SaveTemplateUseCase
{
    public function __construct(private BulletinTemplateRepositoryInterface $repository) {}

    public function execute(int $schoolId, SaveTemplateDTO $dto)
    {
        return $this->repository->save($schoolId, $dto->data);
    }
}
