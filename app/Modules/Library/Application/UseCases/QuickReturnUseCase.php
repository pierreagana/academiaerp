<?php

namespace App\Modules\Library\Application\UseCases;

use App\Modules\Library\Domain\Repositories\LoanRepositoryInterface;
use RuntimeException;

class QuickReturnUseCase
{
    private LoanRepositoryInterface $repository;

    public function __construct(LoanRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $bookIdentifier)
    {
        $loan = $this->repository->findActiveByBookIdentifier($bookIdentifier);

        if (!$loan) {
            throw new RuntimeException("Aucun emprunt actif trouvé pour ce livre.");
        }

        return $this->repository->markReturned($loan->id);
    }
}
