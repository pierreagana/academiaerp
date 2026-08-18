<?php
namespace App\Modules\Academic\Application\DTOs\Building;

class UpdateBuildingDTO {
    public $data;
    public function __construct(array $data) { $this->data = $data; }
}
