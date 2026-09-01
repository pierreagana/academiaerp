<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SchoolTrack\Application\Services\SchoolTrackAccessService;
use App\Modules\SuperAdmin\Application\UseCases\GetSchoolTrackAdminOverviewUseCase;
use Illuminate\Http\Request;

class SchoolTrackAdminController extends Controller
{
    public function __construct(
        private GetSchoolTrackAdminOverviewUseCase $getOverviewUseCase,
        private SchoolTrackAccessService $access,
    ) {}

    public function index(Request $request)
    {
        $overview = $this->getOverviewUseCase->execute(
            $request->get('search'),
            $request->get('status'),
            $request->get('plan'),
        );

        return view('SuperAdmin::school-track', $overview);
    }

    public function toggle(Request $request)
    {
        $this->access->setModuleEnabled(!$this->access->isModuleEnabled());

        return redirect()->route('superadmin.school-track')->with('success', 'Statut du module School Track mis à jour.');
    }
}
