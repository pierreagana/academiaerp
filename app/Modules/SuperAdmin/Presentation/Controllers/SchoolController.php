<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Modules\SuperAdmin\Application\UseCases\ListSchoolsUseCase;
use App\Modules\SuperAdmin\Domain\Models\School as SchoolModel;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function __construct(
        private ListSchoolsUseCase $listSchoolsUseCase
    ) {}

    public function index(Request $request)
    {
        $search  = $request->get('search');
        $status  = $request->get('status');
        $country = $request->get('country');
        $plan    = $request->get('plan');

        $schools = $this->listSchoolsUseCase->execute(10, $search, $status, $country, $plan);
        return view('SuperAdmin::schools', compact('schools', 'search', 'status', 'country', 'plan'));
    }

    public function show($id)
    {
        $paginator = $this->listSchoolsUseCase->execute(50);
        $school = collect($paginator->items())->firstWhere('id', (int)$id) ?? collect($paginator->items())->first();

        if (!$school) {
            $school = (object)[
                'id'              => (int)$id,
                'name'            => "Lycée d'Excellence Dakar",
                'status'          => 'actif',
                'location'        => 'Dakar, Sénégal',
                'region'          => 'Dakar, Sénégal',
                'plan_name'       => 'IA-Premium',
                'package'         => 'IA-Premium',
                'students_count'  => 1250,
                'studentsCount'   => 1250,
                'type'            => 'Secondaire (Lycée)',
                'contact_email'   => 'direction@excellence-dakar.sn',
                'contact_phone'   => '+221 33 800 00 00',
                'domain'          => 'excellence-dakar.agana.school',
                'storage_used_gb' => '18.5',
            ];
        }

        return view('SuperAdmin::school-details', compact('school'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'nullable|string|max:100',
            'location'       => 'nullable|string|max:255',
            'plan_name'      => 'nullable|string|max:100',
            'students_count' => 'nullable|integer|min:0',
            'contact_email'  => 'nullable|email|max:255',
            'contact_phone'  => 'nullable|string|max:50',
        ]);

        SchoolModel::create([
            'name'           => $validated['name'],
            'type'           => $validated['type'] ?? 'Secondaire (Lycée)',
            'status'         => 'actif',
            'plan_name'      => $validated['plan_name'] ?? 'Pro',
            'students_count' => $validated['students_count'] ?? 500,
            'location'       => $validated['location'] ?? 'Dakar, Sénégal',
            'contact_email'  => $validated['contact_email'] ?? null,
            'contact_phone'  => $validated['contact_phone'] ?? null,
        ]);

        return redirect()->route('superadmin.schools')->with('success', 'Établissement enregistré avec succès.');
    }

    public function update(Request $request, int $id)
    {
        $school = SchoolModel::find($id);
        if ($school) {
            $validated = $request->validate([
                'name'                       => 'sometimes|string|max:255',
                'type'                       => 'nullable|string|max:100',
                'status'                     => 'nullable|string|max:50',
                'plan_name'                  => 'nullable|string|max:100',
                'subscription_renewal_date'  => 'nullable|date',
                'students_count'             => 'nullable|integer|min:0',
                'location'                   => 'nullable|string|max:255',
            ]);
            $school->update($validated);
        }

        return redirect()->back()->with('success', 'Établissement mis à jour avec succès.');
    }

    public function suspend(int $id)
    {
        $school = SchoolModel::find($id);
        if ($school) {
            $school->update(['status' => 'suspendu']);
        }
        return redirect()->route('superadmin.schools')->with('success', "L'établissement a été suspendu.");
    }

    public function activate(int $id)
    {
        $school = SchoolModel::find($id);
        if ($school) {
            $school->update(['status' => 'actif']);
        }
        return redirect()->route('superadmin.schools')->with('success', "L'établissement a été réactivé.");
    }

    public function destroy(int $id)
    {
        $school = SchoolModel::find($id);
        if ($school) {
            $school->delete(); // Soft delete — sets deleted_at timestamp
        }
        return redirect()->route('superadmin.schools')->with('success', 'Établissement supprimé (archivé) avec succès.');
    }
}
