<?php

namespace App\Modules\Transport\Application\UseCases;

use App\Modules\Transport\Application\DTOs\LogTripDTO;
use App\Modules\Transport\Domain\Repositories\RouteStopRepositoryInterface;
use App\Modules\Transport\Domain\Repositories\TripLogRepositoryInterface;

class LogTripUseCase
{
    private TripLogRepositoryInterface $repository;
    private RouteStopRepositoryInterface $stopRepository;

    public function __construct(TripLogRepositoryInterface $repository, RouteStopRepositoryInterface $stopRepository)
    {
        $this->repository = $repository;
        $this->stopRepository = $stopRepository;
    }

    public function execute(LogTripDTO $dto)
    {
        $data = $dto->data;

        if (!empty($data['route_id'])) {
            $data['expected_count'] = $this->stopRepository->forRoute($data['route_id'])->sum('students_count');
        }

        return $this->repository->create($data);
    }
}
