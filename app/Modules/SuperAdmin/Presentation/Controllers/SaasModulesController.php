<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Application\UseCases\ListSaasModulesUseCase;
use Illuminate\Http\Request;

class SaasModulesController extends Controller
{
    public function __construct(
        private ListSaasModulesUseCase $listSaasModulesUseCase
    ) {}

    public function index()
    {
        $modules = $this->listSaasModulesUseCase->execute();

        return view('SuperAdmin::modules', compact('modules'));
    }
}
