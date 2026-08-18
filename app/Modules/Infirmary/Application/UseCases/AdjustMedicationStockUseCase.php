<?php

namespace App\Modules\Infirmary\Application\UseCases;

use App\Modules\Infirmary\Application\DTOs\AdjustMedicationStockDTO;
use App\Modules\Infirmary\Domain\Repositories\MedicationMovementRepositoryInterface;
use App\Modules\Infirmary\Domain\Repositories\MedicationRepositoryInterface;
use RuntimeException;

class AdjustMedicationStockUseCase
{
    private MedicationRepositoryInterface $medicationRepository;
    private MedicationMovementRepositoryInterface $movementRepository;

    public function __construct(MedicationRepositoryInterface $medicationRepository, MedicationMovementRepositoryInterface $movementRepository)
    {
        $this->medicationRepository = $medicationRepository;
        $this->movementRepository = $movementRepository;
    }

    public function execute(AdjustMedicationStockDTO $dto)
    {
        $medication = $this->medicationRepository->find($dto->data['medication_id']);
        $quantity = (int) $dto->data['quantity'];
        $type = $dto->data['type'];

        if ($type === 'out' && $quantity > $medication->quantity) {
            throw new RuntimeException("Quantité insuffisante en stock ({$medication->quantity} disponible(s)).");
        }

        $medication->quantity = $type === 'in'
            ? $medication->quantity + $quantity
            : $medication->quantity - $quantity;
        $medication->save();

        return $this->movementRepository->create([
            'medication_id' => $medication->id,
            'type' => $type,
            'quantity' => $quantity,
            'source' => $dto->data['source'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }
}
