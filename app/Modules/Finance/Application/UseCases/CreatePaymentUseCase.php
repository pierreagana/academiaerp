<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Finance\Application\DTOs\CreatePaymentDTO;
use App\Modules\Finance\Domain\Repositories\PaymentRepositoryInterface;

class CreatePaymentUseCase
{
    private PaymentRepositoryInterface $repository;

    public function __construct(PaymentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreatePaymentDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
