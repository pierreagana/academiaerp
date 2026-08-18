<?php

namespace App\Modules\ParentPortal\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Application\Services\ParentPortalAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentChildController extends Controller
{
    public function showAddChildForm()
    {
        return view('ParentPortal::add_child');
    }

    public function addChild(Request $request, ParentPortalAccountService $service)
    {
        $data = $request->validate([
            'school_code' => ['required', 'string'],
            'matricule' => ['required', 'string'],
        ]);

        $claimed = $service->claimChild(
            Auth::guard('parent')->user(),
            trim($data['school_code']),
            trim($data['matricule'])
        );

        if (!$claimed) {
            return back()->withErrors([
                'matricule' => "Aucune correspondance trouvée. Vérifiez le code établissement et le matricule, ou contactez l'établissement.",
            ])->onlyInput('school_code', 'matricule');
        }

        return redirect()->route('parent.dashboard')->with('success', 'Enfant ajouté avec succès.');
    }
}
