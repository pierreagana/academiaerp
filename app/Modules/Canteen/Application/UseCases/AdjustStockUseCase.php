<?php

namespace App\Modules\Canteen\Application\UseCases;

use App\Modules\Canteen\Application\DTOs\AdjustStockDTO;
use App\Modules\Canteen\Domain\Repositories\ProductRepositoryInterface;
use App\Modules\Canteen\Domain\Repositories\StockMovementRepositoryInterface;
use RuntimeException;

class AdjustStockUseCase
{
    private ProductRepositoryInterface $productRepository;
    private StockMovementRepositoryInterface $movementRepository;

    public function __construct(ProductRepositoryInterface $productRepository, StockMovementRepositoryInterface $movementRepository)
    {
        $this->productRepository = $productRepository;
        $this->movementRepository = $movementRepository;
    }

    public function execute(AdjustStockDTO $dto)
    {
        $product = $this->productRepository->find($dto->data['product_id']);
        $quantity = (float) $dto->data['quantity'];
        $type = $dto->data['type'];

        if ($type === 'out' && $quantity > $product->quantity) {
            throw new RuntimeException("Quantité insuffisante en stock ({$product->quantity} {$product->unit} disponible(s)).");
        }

        $product->quantity = $type === 'in'
            ? $product->quantity + $quantity
            : $product->quantity - $quantity;
        $product->save();

        return $this->movementRepository->create([
            'product_id' => $product->id,
            'type' => $type,
            'category' => $dto->data['category'] ?? null,
            'quantity' => $quantity,
            'source' => $dto->data['source'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }
}
