<?php
namespace App\Modules\Academic\Application\UseCases\Room;

use App\Modules\Academic\Domain\Repositories\RoomRepositoryInterface;
use App\Modules\Academic\Application\DTOs\Room\UpdateRoomDTO;

class UpdateRoomUseCase {
    private $repository;
    public function __construct(RoomRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute($id, UpdateRoomDTO $dto) { return $this->repository->update($id, $dto->data); }
}
