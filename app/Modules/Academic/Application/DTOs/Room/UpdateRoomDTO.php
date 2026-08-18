<?php
namespace App\Modules\Academic\Application\DTOs\Room;

class UpdateRoomDTO {
    public $data;
    public function __construct(array $data) { $this->data = $data; }
}
