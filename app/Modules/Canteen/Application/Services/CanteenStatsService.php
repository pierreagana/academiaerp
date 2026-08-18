<?php

namespace App\Modules\Canteen\Application\Services;

use App\Modules\Canteen\Domain\Repositories\MealRecordRepositoryInterface;
use App\Modules\Canteen\Domain\Repositories\ProductRepositoryInterface;
use App\Modules\Canteen\Domain\Repositories\StockMovementRepositoryInterface;

class CanteenStatsService
{
    private MealRecordRepositoryInterface $mealRepository;
    private StockMovementRepositoryInterface $movementRepository;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        MealRecordRepositoryInterface $mealRepository,
        StockMovementRepositoryInterface $movementRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->mealRepository = $mealRepository;
        $this->movementRepository = $movementRepository;
        $this->productRepository = $productRepository;
    }

    public function dashboardStats(): array
    {
        return [
            'meals_today' => $this->mealRepository->countToday(),
            'average_price_today' => $this->mealRepository->averagePriceToday(),
            'waste_ratio' => $this->movementRepository->wasteRatioForMonth(),
        ];
    }

    public function criticalProducts()
    {
        return $this->productRepository->criticalOrLow()->filter(fn ($p) => $p->status === 'critical')->values();
    }
}
