<?php

namespace App\Modules\Library\Application\UseCases;

use App\Modules\Library\Application\DTOs\UpdateBookDTO;
use App\Modules\Library\Domain\Repositories\BookRepositoryInterface;
use InvalidArgumentException;

class UpdateBookUseCase
{
    private BookRepositoryInterface $repository;

    public function __construct(BookRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id, UpdateBookDTO $dto)
    {
        $book = $this->repository->find($id);

        if (isset($dto->data['quantity_total'])) {
            $currentlyLoaned = $book->quantity_total - $book->quantity_available;

            if ($dto->data['quantity_total'] < $currentlyLoaned) {
                throw new InvalidArgumentException(
                    "Impossible de réduire la quantité en dessous de {$currentlyLoaned}, le nombre d'exemplaires actuellement empruntés."
                );
            }
        }

        return $this->repository->update($id, $dto->data);
    }
}
