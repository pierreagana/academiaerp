<?php

namespace App\Modules\Canteen\Application\UseCases;

use App\Modules\Canteen\Application\DTOs\CreateMealRecordDTO;
use App\Modules\Canteen\Domain\Repositories\AccountRepositoryInterface;
use App\Modules\Canteen\Domain\Repositories\MealRecordRepositoryInterface;

class RecordMealUseCase
{
    private MealRecordRepositoryInterface $mealRepository;
    private AccountRepositoryInterface $accountRepository;

    public function __construct(MealRecordRepositoryInterface $mealRepository, AccountRepositoryInterface $accountRepository)
    {
        $this->mealRepository = $mealRepository;
        $this->accountRepository = $accountRepository;
    }

    public function execute(CreateMealRecordDTO $dto)
    {
        $record = $this->mealRepository->create($dto->data);

        $price = (float) ($dto->data['price'] ?? 0);
        if ($price > 0) {
            $this->accountRepository->debit($dto->data['account_id'], $price);
        }

        return $record;
    }
}
