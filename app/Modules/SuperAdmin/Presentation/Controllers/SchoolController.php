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
        $saasPackages = \App\Modules\SuperAdmin\Domain\Models\SaasPackage::orderBy('price')->get();
        $facilities = \App\Modules\SuperAdmin\Domain\Models\Facility::where('is_active', true)->orderBy('order')->orderBy('name')->get();
        $availableSectors = SchoolModel::getAvailableSectors();
        $availableLevels = SchoolModel::getAvailableLevels();
        $availableLanguageRegimes = SchoolModel::getAvailableLanguageRegimes();

        // Real under-utilization signal — not AI, no fabricated "3 écoles au
        // Sénégal" claim. There's no feature/login usage tracking in this
        // app, so the only honest proxy available is real enrollment: a paid
        // (non-Starter) plan with very few students actually recorded.
        $underutilizedSchools = SchoolModel::where('plan_name', '!=', 'Starter')
            ->where('students_count', '<', 10)
            ->get(['name', 'plan_name', 'students_count']);

        return view('SuperAdmin::schools', compact(
            'schools', 'search', 'status', 'country', 'plan', 'facilities', 'saasPackages',
            'availableSectors', 'availableLevels', 'availableLanguageRegimes',
            'underutilizedSchools'
        ));
    }

    public function show($id)
    {
        $paginator = $this->listSchoolsUseCase->execute(50);
        $school = collect($paginator->items())->firstWhere('id', (int)$id) ?? collect($paginator->items())->first();
        $schoolModel = SchoolModel::with('facilitiesList')->find((int)$id);
        $facilities = \App\Modules\SuperAdmin\Domain\Models\Facility::where('is_active', true)->orderBy('order')->orderBy('name')->get();
        $schoolFacilityIds = $schoolModel ? $schoolModel->facilitiesList->pluck('id')->all() : [];

        $availableSectors = SchoolModel::getAvailableSectors();
        $availableLevels = SchoolModel::getAvailableLevels();
        $availableLanguageRegimes = SchoolModel::getAvailableLanguageRegimes();

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
                'sector'          => 'Privé',
                'is_bilingual'    => true,
                'language_regime' => 'Bilingue (Français / Anglais)',
                'levels'          => ['Collège', 'Lycée'],
                'contact_email'   => 'direction@excellence-dakar.sn',
                'contact_phone'   => '+221 33 800 00 00',
                'domain'          => 'excellence-dakar.agana.school',
                'storage_used_gb' => '18.5',
            ];
        }

        // Real active modules for this specific school — not a static
        // "Notes & Bulletins / Comptabilité / Présences / IA Appréciations"
        // tile grid that used to render identically for every school
        // regardless of its actual package.
        $activePackageFeatures = $schoolModel?->activePackage()?->features ?? [];
        $approvedExtensionModules = $schoolModel?->approvedExtensionModuleNames() ?? [];

        $schoolAdminUsers = $schoolModel
            ? \App\Models\User::where('school_id', $schoolModel->id)->where('role', 'adminschool')->get()
            : collect();
        $schoolGroups = \App\Modules\SuperAdmin\Domain\Models\SchoolGroup::with('founder')->get();
        $allowsMultiSuccursales = $schoolModel ? self::schoolAllowsMultiSuccursales($schoolModel) : true;

        return view('SuperAdmin::school-details', compact(
            'school', 'schoolModel', 'facilities', 'schoolFacilityIds',
            'availableSectors', 'availableLevels', 'availableLanguageRegimes',
            'activePackageFeatures', 'approvedExtensionModules', 'schoolAdminUsers', 'schoolGroups', 'allowsMultiSuccursales'
        ));
    }

    /** Lets SuperAdmin retroactively turn an already-created single-school "Directeur" account into a Fondateur (or attach the school to an existing founder's group) — the registration-approval flow only covered this at initial creation, leaving no path for a school to switch later. */
    /** The founder/multi-établissement feature reuses the school's existing "Multi-Succursales" package module rather than a new, parallel gate. */
    public static function schoolAllowsMultiSuccursales(SchoolModel $schoolModel): bool
    {
        $accessible = $schoolModel->accessibleModuleNames();

        return $accessible === null || in_array('Multi-Succursales', $accessible, true);
    }

    public function updateGroup(Request $request, int $id, \App\Modules\Academic\Application\Services\ProvisionMainBranchService $provisionMainBranch)
    {
        $schoolModel = SchoolModel::findOrFail($id);

        $data = $request->validate([
            'founder_user_id' => ['nullable', 'exists:users,id'],
            'school_group_id' => ['nullable', 'exists:school_groups,id'],
        ]);

        if (empty($data['founder_user_id']) && empty($data['school_group_id'])) {
            $schoolModel->update(['school_group_id' => null]);
            return back()->with('success', 'École retirée de son groupe — retour en gestion simple (Directeur).');
        }

        if (!self::schoolAllowsMultiSuccursales($schoolModel)) {
            return back()->withErrors(['founder_user_id' => "Le forfait actuel de « {$schoolModel->name} » ({$schoolModel->plan_name}) n'inclut pas le module « Multi-Succursales ». Changez de forfait avant de désigner un fondateur."]);
        }

        if (!empty($data['school_group_id'])) {
            $group = \App\Modules\SuperAdmin\Domain\Models\SchoolGroup::findOrFail($data['school_group_id']);
        } else {
            $group = \App\Modules\SuperAdmin\Domain\Models\SchoolGroup::create([
                'name' => $schoolModel->name . ' — Groupe',
                'founder_user_id' => $data['founder_user_id'],
            ]);
        }

        $schoolModel->update(['school_group_id' => $group->id]);
        $provisionMainBranch->handle($schoolModel);

        return back()->with('success', 'École rattachée au groupe « ' . $group->name . ' » avec succès.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'nullable|string|max:100',
            'sector'          => 'nullable|string|max:100',
            'is_bilingual'    => 'nullable|boolean',
            'language_regime' => 'nullable|string|max:100',
            'levels'          => 'nullable|array',
            'levels.*'        => 'string|max:100',
            'location'        => 'nullable|string|max:255',
            'plan_name'       => 'nullable|string|max:100',
            'students_count'  => 'nullable|integer|min:0',
            'contact_email'   => 'nullable|email|max:255',
            'phone_country_code' => 'nullable|string',
            'phone_number'    => 'nullable|string|max:50',
            'slogan'          => 'nullable|string|max:255',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'facilities'      => 'nullable|array',
            'facilities.*'    => 'integer|exists:facilities,id',
        ]);

        $isBilingual = $request->has('is_bilingual')
            ? (bool) $request->input('is_bilingual')
            : (str_contains(strtolower($validated['language_regime'] ?? ''), 'bilingue'));

        $school = SchoolModel::create([
            'name'            => $validated['name'],
            'type'            => $validated['type'] ?? 'Secondaire (Lycée)',
            'sector'          => $validated['sector'] ?? 'Privé',
            'is_bilingual'    => $isBilingual,
            'language_regime' => $validated['language_regime'] ?? ($isBilingual ? 'Bilingue (Français / Anglais)' : 'Monolingue (Français)'),
            'levels'          => $validated['levels'] ?? ['Secondaire (Lycée)'],
            'status'          => 'actif',
            'plan_name'       => $validated['plan_name'] ?? 'Starter',
            'students_count'  => $validated['students_count'] ?? 500,
            'location'        => $validated['location'] ?? 'Dakar, Sénégal',
            'contact_email'   => $validated['contact_email'] ?? null,
            'contact_phone'   => \App\Modules\SuperAdmin\Domain\Models\Country::combinePhone($validated['phone_country_code'] ?? null, $validated['phone_number'] ?? null),
            'slogan'          => $validated['slogan'] ?? null,
            'latitude'        => $validated['latitude'] ?? null,
            'longitude'       => $validated['longitude'] ?? null,
        ]);

        if (!empty($validated['facilities'])) {
            $school->facilitiesList()->sync($validated['facilities']);
        }

        $message = 'Établissement enregistré avec succès.';
        if (!empty($validated['contact_email'])) {
            \App\Services\SchoolAdminProvisioner::createAndNotify($school, 'Administrateur ' . $school->name, $validated['contact_email']);
            $message .= ' Les identifiants de connexion ont été envoyés à ' . $validated['contact_email'] . '.';
        } else {
            $message .= " Aucun email de contact fourni : aucun compte administrateur n'a été créé.";
        }

        return redirect()->route('superadmin.schools')->with('success', $message);
    }

    public function update(Request $request, int $id)
    {
        $school = SchoolModel::find($id);
        if ($school) {
            $validated = $request->validate([
                'name'                       => 'sometimes|string|max:255',
                'type'                       => 'nullable|string|max:100',
                'sector'                     => 'nullable|string|max:100',
                'is_bilingual'               => 'nullable|boolean',
                'language_regime'            => 'nullable|string|max:100',
                'levels'                     => 'nullable|array',
                'levels.*'                   => 'string|max:100',
                'status'                     => 'nullable|string|max:50',
                'plan_name'                  => 'nullable|string|max:100',
                'subscription_renewal_date'  => 'nullable|date',
                'students_count'             => 'nullable|integer|min:0',
                'location'                   => 'nullable|string|max:255',
                'facilities'                 => 'nullable|array',
                'facilities.*'               => 'integer|exists:facilities,id',
            ]);

            if ($request->has('language_regime')) {
                $validated['is_bilingual'] = str_contains(strtolower($request->input('language_regime')), 'bilingue');
            } elseif ($request->has('is_bilingual')) {
                $validated['is_bilingual'] = (bool) $request->input('is_bilingual');
            }

            if ($request->has('levels')) {
                $validated['levels'] = $request->input('levels', []);
            }

            $school->update($validated);

            if ($request->has('facilities')) {
                $school->facilitiesList()->sync($request->input('facilities', []));
            }
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
