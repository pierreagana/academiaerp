<?php
namespace App\Modules\Academic\Application\UseCases\Building;

use App\Modules\Academic\Domain\Repositories\BuildingRepositoryInterface;
use App\Modules\Academic\Application\DTOs\Building\CreateBuildingDTO;

class CreateBuildingUseCase {
    private $repository;
    public function __construct(BuildingRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute(CreateBuildingDTO $dto) { return $this->repository->create($dto->data); }
}
