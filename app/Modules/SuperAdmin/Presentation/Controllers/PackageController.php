<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Modules\SuperAdmin\Application\UseCases\ListSaasPackagesUseCase;
use App\Modules\SuperAdmin\Domain\Models\SaasPackage;

class PackageController extends Controller
{
    public function __construct(
        private ListSaasPackagesUseCase $listSaasPackagesUseCase
    ) {}

    public function index()
    {
        $packages = $this->listSaasPackagesUseCase->execute();
        $availableModules = \App\Modules\SuperAdmin\Domain\Models\SaasModule::where('status', 'active')->get();
        $schools = \App\Modules\SuperAdmin\Domain\Models\School::take(10)->get();
        return view('SuperAdmin::packages', compact('packages', 'availableModules', 'schools'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'price'          => 'required|numeric|min:0',
            'billing_cycle'  => 'nullable|string',
            'max_students'   => 'nullable|integer',
            'max_storage_gb' => 'nullable|integer',
            'features'       => 'nullable|array',
            'description'    => 'nullable|string',
            'is_popular'     => 'nullable|boolean',
        ]);

        SaasPackage::create([
            'name'           => $validated['name'],
            'description'    => $validated['description'] ?? null,
            'price'          => $validated['price'],
            'billing_cycle'  => $validated['billing_cycle'] ?? 'annuel',
            'max_students'   => $validated['max_students'] ?? null,
            'max_storage_gb' => $validated['max_storage_gb'] ?? null,
            'features'       => $validated['features'] ?? [],
            'status'         => 'active',
            'is_popular'     => (bool)($validated['is_popular'] ?? false),
        ]);

        return redirect()->back()->with('success', 'Nouveau forfait créé avec ses options activées.');
    }

    public function update(Request $request, int $id)
    {
        $pkg = SaasPackage::findOrFail($id);
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'description'    => 'nullable|string',
            'price'          => 'sometimes|numeric|min:0',
            'billing_cycle'  => 'sometimes|string',
            'max_students'   => 'nullable|integer',
            'max_storage_gb' => 'nullable|integer',
            'features'       => 'nullable|array',
            'is_popular'     => 'nullable|boolean',
            'status'         => 'nullable|string',
        ]);

        if (isset($validated['is_popular'])) {
            $validated['is_popular'] = (bool)$validated['is_popular'];
        }

        $pkg->update($validated);
        return redirect()->back()->with('success', 'Forfait mis à jour avec succès.');
    }

    public function destroy(int $id)
    {
        SaasPackage::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Forfait supprimé.');
    }
}
