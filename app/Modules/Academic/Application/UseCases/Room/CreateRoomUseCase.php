<?php
namespace App\Modules\Academic\Application\UseCases\Room;

use App\Modules\Academic\Domain\Repositories\RoomRepositoryInterface;
use App\Modules\Academic\Application\DTOs\Room\CreateRoomDTO;

class CreateRoomUseCase {
    private $repository;
    public function __construct(RoomRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute(CreateRoomDTO $dto) { return $this->repository->create($dto->data); }
}
