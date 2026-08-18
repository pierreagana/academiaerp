<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Application\UseCases\GetMultiCampusNetworkUseCase;
use Illuminate\Http\Request;

class MultiCampusController extends Controller
{
    public function __construct(
        private GetMultiCampusNetworkUseCase $getMultiCampusNetworkUseCase
    ) {}

    public function index()
    {
        $data = $this->getMultiCampusNetworkUseCase->execute();
        return view('SuperAdmin::multi-campus', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'region' => 'nullable|string|max:100',
        ]);
        // Network creation logic would go here (DB insert via repository)
        return redirect()->route('superadmin.multi-campus')->with('success', 'Réseau multi-campus créé avec succès.');
    }
}
