<?php
namespace App\Modules\Academic\Application\DTOs\Building;

class CreateBuildingDTO {
    public $data;
    public function __construct(array $data) { $this->data = $data; }
}
