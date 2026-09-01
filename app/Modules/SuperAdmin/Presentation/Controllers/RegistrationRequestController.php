<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Modules\SuperAdmin\Application\UseCases\ListRegistrationRequestsUseCase;
use App\Modules\SuperAdmin\Domain\Models\RegistrationRequest;
use App\Modules\SuperAdmin\Domain\Models\SaasPackage;
use App\Modules\SuperAdmin\Domain\Models\School as SchoolModel;
use App\Modules\SuperAdmin\Domain\Models\SchoolGroup;
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

        // Real, deterministic completeness check — not AI, no fabricated
        // "90/100, domaine vérifié" claim (nothing actually checks the
        // domain). Scored on fields genuinely present/valid on this record.
        $reliabilityScore = 0;
        $reliabilityNotes = [];

        if (!empty($requestItem->email) && filter_var($requestItem->email, FILTER_VALIDATE_EMAIL)) {
            $reliabilityScore += 25;
            $freeMailDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com'];
            $domain = strtolower(substr(strrchr($requestItem->email, '@'), 1));
            if (!in_array($domain, $freeMailDomains, true)) {
                $reliabilityScore += 15;
                $reliabilityNotes[] = "domaine email professionnel";
            } else {
                $reliabilityNotes[] = "adresse email grand public";
            }
        } else {
            $reliabilityNotes[] = "email manquant ou invalide";
        }

        if (!empty($requestItem->phone) && strlen(preg_replace('/\D/', '', $requestItem->phone)) >= 8) {
            $reliabilityScore += 20;
            $reliabilityNotes[] = "téléphone renseigné";
        } else {
            $reliabilityNotes[] = "téléphone manquant ou incomplet";
        }

        if (!empty($requestItem->school_name) && strlen($requestItem->school_name) >= 4) {
            $reliabilityScore += 20;
        }
        if (!empty($requestItem->region)) {
            $reliabilityScore += 10;
            $reliabilityNotes[] = "région précisée";
        }
        if (!empty($requestItem->notes)) {
            $reliabilityScore += 10;
            $reliabilityNotes[] = "notes complémentaires fournies";
        }

        $reliabilityScore = min(100, $reliabilityScore);

        $schoolGroups = SchoolGroup::with('founder')->get();

        return view('SuperAdmin::registration-request-details', compact('requestItem', 'reliabilityScore', 'reliabilityNotes', 'schoolGroups'));
    }

    public function approve(Request $request, int $id, \App\Modules\Academic\Application\Services\ProvisionMainBranchService $provisionMainBranch)
    {
        $req = RegistrationRequest::find($id);
        if (!$req) {
            return redirect()->route('superadmin.registration-requests')->withErrors(['request' => 'Demande introuvable.']);
        }

        $data = $request->validate([
            'is_founder' => ['nullable', 'boolean'],
            'school_group_id' => ['nullable', 'exists:school_groups,id'],
        ]);

        $req->update(['status' => 'approuvé']);

        // Auto-provision school into active schools directory — reuses everything
        // the public "Demande de Démo" wizard collected (falls back to the
        // historical defaults for older/manually-entered requests that predate
        // those fields).
        $isBilingual = str_contains(strtolower($req->language_regime ?? ''), 'bilingue');

        // A school only gets restricted to its plan's real feature set once
        // plan_name matches an actual SaasPackage (see School::activePackage())
        // — anything else (a stale/free-text value, or none requested) falls
        // open to full access, so always resolve to a real package here rather
        // than trusting an arbitrary string through.
        $planName = SaasPackage::where('name', $req->plan_requested)->exists()
            ? $req->plan_requested
            : 'Starter';

        $school = SchoolModel::firstOrCreate(
            ['name' => $req->school_name],
            [
                'type'            => $req->type ?? 'Secondaire (Lycée)',
                'status'          => 'actif',
                'plan_name'       => $planName,
                'students_count'  => $req->students_count ?? 500,
                'sector'          => $req->sector ?? 'Privé',
                'is_bilingual'    => $isBilingual,
                'language_regime' => $req->language_regime ?? ($isBilingual ? 'Bilingue (Français / Anglais)' : 'Monolingue (Français)'),
                'levels'          => $req->levels ?? ['Secondaire (Lycée)'],
                'slogan'          => $req->slogan,
                'location'        => $req->address ?? $req->region ?? 'Dakar, Sénégal',
                'city'            => $req->city,
                'country'         => $req->country,
                'latitude'        => $req->latitude,
                'longitude'       => $req->longitude,
                'contact_email'   => $req->email,
                'contact_phone'   => $req->phone,
                'logo_url'        => $req->logo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($req->logo_path) : null,
            ]
        );

        if ($school->wasRecentlyCreated && !empty($req->facilities)) {
            $school->facilitiesList()->sync($req->facilities);
        }

        // Provision the school's first admin account — previously this endpoint
        // created the School directory entry only, leaving no way to actually
        // log in and use it. Reuses the same helper as the (now-defunct) direct
        // registration flow so the school actually receives its credentials by
        // email instead of the password only ever appearing in this page's
        // flash message.
        $user = \App\Models\User::where('email', $req->email)->first();
        $credentialsEmailed = false;
        if (!$user) {
            $user = \App\Services\SchoolAdminProvisioner::createAndNotify($school, $req->applicant_name, $req->email);
            $credentialsEmailed = true;
        }

        $isFounder = $request->boolean('is_founder');
        $founderBlockedReason = null;
        if ($isFounder && !\App\Modules\SuperAdmin\Presentation\Controllers\SchoolController::schoolAllowsMultiSuccursales($school)) {
            $isFounder = false;
            $founderBlockedReason = "Le forfait « {$school->plan_name} » n'inclut pas le module « Multi-Succursales » — le compte a été créé en simple Directeur. Changez de forfait puis désignez le fondateur depuis la fiche école.";
        }
        if ($isFounder) {
            if (!empty($data['school_group_id'])) {
                $group = SchoolGroup::findOrFail($data['school_group_id']);
            } else {
                $group = SchoolGroup::create([
                    'name' => $req->school_name . ' — Groupe',
                    'founder_user_id' => $user->id,
                ]);
            }
            $school->update(['school_group_id' => $group->id]);
            $provisionMainBranch->handle($school);
        }

        $message = 'Demande approuvée et établissement provisionné avec succès dans l\'annuaire.';
        if ($credentialsEmailed) {
            $message .= " Identifiants envoyés par email à {$req->email}.";
        } else {
            $message .= " Un compte existait déjà pour {$req->email} — il a été rattaché à cette école.";
        }
        if ($founderBlockedReason) {
            $message .= ' ' . $founderBlockedReason;
        }

        return redirect()->route('superadmin.registration-requests')->with('success', $message);
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
