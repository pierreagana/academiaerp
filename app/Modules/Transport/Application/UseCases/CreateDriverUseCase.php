<?php

namespace App\Modules\Transport\Application\UseCases;

use App\Modules\Transport\Application\DTOs\CreateDriverDTO;
use App\Modules\Transport\Domain\Repositories\DriverRepositoryInterface;

class CreateDriverUseCase
{
    private DriverRepositoryInterface $repository;

    public function __construct(DriverRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateDriverDTO $dto)
    {
        $data = $dto->data;

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->repository->create($data);
    }
}
