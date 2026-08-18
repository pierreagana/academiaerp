<?php

namespace App\Modules\Canteen\Application\UseCases;

use App\Modules\Canteen\Domain\Repositories\MenuRepositoryInterface;

class PublishWeekUseCase
{
    private MenuRepositoryInterface $repository;

    public function __construct(MenuRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $weekStartDate)
    {
        return $this->repository->publishWeek($weekStartDate);
    }
}
