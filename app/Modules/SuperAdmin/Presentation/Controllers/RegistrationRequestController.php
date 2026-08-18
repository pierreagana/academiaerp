<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Modules\SuperAdmin\Application\UseCases\ListRegistrationRequestsUseCase;
use App\Modules\SuperAdmin\Domain\Models\RegistrationRequest;
use App\Modules\SuperAdmin\Domain\Models\School as SchoolModel;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class RegistrationRequestController extends Controller
{
    public function __construct(
        private ListRegistrationRequestsUseCase $listRequestsUseCase
    ) {}

    public function index(Request $request)
    {
        $search  = $request->get('search');
        $status  = $request->get('status');
        $country = $request->get('country');

        $requests = $this->listRequestsUseCase->execute(10, $search, $status, $country);

        $pendingCount     = RegistrationRequest::whereIn('status', ['en attente', 'pending', 'nouveau', 'En attente'])->count();
        $approvedCount    = RegistrationRequest::whereIn('status', ['approuvé', 'approved', 'validée', 'approuvée', 'Approuvé'])->count();
        $totalCount       = RegistrationRequest::count();
        $conversionRate   = $totalCount > 0 ? round(($approvedCount / $totalCount) * 100) : 0;

        $stats = [
            'pending'    => $pendingCount,
            'approved'   => $approvedCount,
            'conversion' => $conversionRate,
        ];

        return view('SuperAdmin::registration-requests', compact('requests', 'search', 'status', 'country', 'stats'));
    }

    public function show($id)
    {
        $requestItem = RegistrationRequest::find($id);
        
        if (!$requestItem) {
            $paginator = $this->listRequestsUseCase->execute(50);
            $requestItem = collect($paginator->items())->firstWhere('id', (int)$id) ?? collect($paginator->items())->first();
        }

        return view('SuperAdmin::registration-request-details', compact('requestItem'));
    }

    public function approve(Request $request, int $id)
    {
        $req = RegistrationRequest::find($id);
        if ($req) {
            $req->update(['status' => 'approuvé']);

            // Auto-provision school into active schools directory
            SchoolModel::firstOrCreate(
                ['name' => $req->school_name],
                [
                    'type'           => 'Secondaire (Lycée)',
                    'status'         => 'actif',
                    'plan_name'      => $req->plan_requested ?? 'IA-Premium',
                    'students_count' => 500,
                    'location'       => $req->region ?? 'Dakar, Sénégal',
                    'contact_email'  => $req->email,
                    'contact_phone'  => $req->phone,
                ]
            );
        }
        return redirect()->route('superadmin.registration-requests')->with('success', 'Demande approuvée et établissement provisionné avec succès dans l\'annuaire.');
    }

    public function reject(Request $request, int $id)
    {
        $req = RegistrationRequest::find($id);
        if ($req) {
            $req->update(['status' => 'rejeté']);
        }
        return redirect()->route('superadmin.registration-requests')->with('success', 'Demande rejetée.');
    }
}
