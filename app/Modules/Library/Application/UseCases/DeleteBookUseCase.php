<?php

namespace App\Modules\Library\Application\UseCases;

use App\Modules\Library\Domain\Repositories\BookRepositoryInterface;

class DeleteBookUseCase
{
    private BookRepositoryInterface $repository;

    public function __construct(BookRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
