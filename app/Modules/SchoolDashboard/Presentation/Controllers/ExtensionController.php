<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Domain\Models\SaasModule;
use App\Modules\SuperAdmin\Domain\Models\SchoolExtensionRequest;
use Illuminate\Http\Request;

class ExtensionController extends Controller
{
    /**
     * Billing decisions are the establishment director's call, not a
     * delegable RBAC permission — same convention as RoleController.
     */
    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()->role === 'adminschool', 403, "Seul le directeur de l'établissement peut gérer les extensions payantes.");
    }

    public function index()
    {
        $this->ensureAdmin();

        $school = auth()->user()->school;
        $package = $school->activePackage();
        $includedFeatures = $package && is_array($package->features) ? $package->features : [];

        $requests = $school->extensionRequests()->get()->keyBy('module_name');

        $extensions = SaasModule::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->reject(fn ($module) => in_array($module->name, $includedFeatures, true))
            ->map(function ($module) use ($requests) {
                $request = $requests->get($module->name);
                return (object) [
                    'id'          => $module->id,
                    'name'        => $module->name,
                    'description' => $module->description,
                    'icon'        => $module->icon ?? 'ph-puzzle-piece',
                    'price'       => $module->price,
                    'status'      => $request?->status,
                ];
            })
            ->values();

        return view('SchoolDashboard::extensions', [
            'extensions'  => $extensions,
            'hasPackage'  => (bool) $package,
            'packageName' => $package->name ?? null,
            'systemCurrency' => 'FCFA',
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'module_name' => ['required', 'string'],
        ]);

        $school = auth()->user()->school;
        $module = SaasModule::where('name', $validated['module_name'])->where('status', 'active')->firstOrFail();

        // Already included in the base package — nothing to request.
        $package = $school->activePackage();
        $includedFeatures = $package && is_array($package->features) ? $package->features : [];
        if (in_array($module->name, $includedFeatures, true)) {
            return back()->with('error', "« {$module->name} » est déjà inclus dans votre forfait actuel.");
        }

        $existing = SchoolExtensionRequest::where('school_id', $school->id)
            ->where('module_name', $module->name)
            ->first();

        if ($existing && $existing->status === 'approved') {
            return back()->with('error', "« {$module->name} » est déjà activé pour votre établissement.");
        }

        if ($existing && $existing->status === 'pending') {
            return back()->with('success', "Votre demande pour « {$module->name} » est déjà en cours de traitement.");
        }

        SchoolExtensionRequest::updateOrCreate(
            ['school_id' => $school->id, 'module_name' => $module->name],
            ['status' => 'pending', 'requested_by' => auth()->id(), 'decided_by' => null, 'decided_at' => null]
        );

        return back()->with('success', "Votre demande d'activation pour « {$module->name} » a été envoyée. Notre équipe vous contactera pour la facturation.");
    }
}
