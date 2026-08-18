<?php

namespace App\Modules\Cards\Application\UseCases;

use App\Modules\Cards\Application\DTOs\SaveCardTemplateDTO;
use App\Modules\Cards\Domain\Repositories\CardTemplateRepositoryInterface;

class SaveCardTemplateUseCase
{
    private CardTemplateRepositoryInterface $repository;

    public function __construct(CardTemplateRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(SaveCardTemplateDTO $dto)
    {
        return $this->repository->save($dto->cardType, $dto->data);
    }
}
