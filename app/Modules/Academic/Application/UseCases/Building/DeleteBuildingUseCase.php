<?php
namespace App\Modules\Academic\Application\UseCases\Building;

use App\Modules\Academic\Domain\Repositories\BuildingRepositoryInterface;

class DeleteBuildingUseCase {
    private $repository;
    public function __construct(BuildingRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute($id) { return $this->repository->delete($id); }
}
