<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use Illuminate\Routing\Controller;
use App\Modules\SuperAdmin\Application\UseCases\GetNetworkHealthUseCase;

class NetworkHealthController extends Controller
{
    public function __construct(
        private GetNetworkHealthUseCase $getNetworkHealthUseCase
    ) {}

    public function index()
    {
        $data = $this->getNetworkHealthUseCase->execute();

        return view('SuperAdmin::network-health', $data);
    }
}
