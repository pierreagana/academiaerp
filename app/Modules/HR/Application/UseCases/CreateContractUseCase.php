<?php

namespace App\Modules\HR\Application\UseCases;

use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\HR\Application\DTOs\CreateContractDTO;
use App\Modules\HR\Domain\Repositories\ContractRepositoryInterface;

class CreateContractUseCase
{
    private ContractRepositoryInterface $repository;

    public function __construct(ContractRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateContractDTO $dto)
    {
        $contract = $this->repository->create($dto->data);

        $this->syncHolderRecord($contract);

        return $contract;
    }

    private function syncHolderRecord($contract): void
    {
        $normalizedType = match ($contract->contract_type) {
            'CDI' => 'cdi',
            'CDD' => 'cdd',
            'Prestataire' => 'prestataire',
            default => null,
        };

        $update = ['contract_end_date' => $contract->end_date];
        if ($normalizedType !== null) {
            $update['contract_type'] = $normalizedType;
        }

        if ($contract->holder_type === 'teacher') {
            Teacher::where('id', $contract->holder_id)->update($update);
        } elseif ($contract->holder_type === 'staff') {
            Staff::where('id', $contract->holder_id)->update($update);
        }
    }
}
