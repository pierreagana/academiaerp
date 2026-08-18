<?php
namespace App\Modules\Academic\Application\UseCases\Building;

use App\Modules\Academic\Domain\Repositories\BuildingRepositoryInterface;
use App\Modules\Academic\Application\DTOs\Building\UpdateBuildingDTO;

class UpdateBuildingUseCase {
    private $repository;
    public function __construct(BuildingRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute($id, UpdateBuildingDTO $dto) { return $this->repository->update($id, $dto->data); }
}
