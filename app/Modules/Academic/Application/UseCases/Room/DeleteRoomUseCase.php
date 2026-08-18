<?php
namespace App\Modules\Academic\Application\UseCases\Room;

use App\Modules\Academic\Domain\Repositories\RoomRepositoryInterface;

class DeleteRoomUseCase {
    private $repository;
    public function __construct(RoomRepositoryInterface $repository) { $this->repository = $repository; }
    public function execute($id) { return $this->repository->delete($id); }
}
