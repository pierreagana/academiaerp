<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Application\UseCases\GetModuleDetailsUseCase;
use App\Modules\SuperAdmin\Domain\Models\SaasModule;
use Illuminate\Http\Request;

class ModuleDetailsController extends Controller
{
    public function __construct(
        private GetModuleDetailsUseCase $getModuleDetailsUseCase
    ) {}

    public function index()
    {
        $firstSlug = SaasModule::orderBy('name')->value('slug');

        abort_if(!$firstSlug, 404, 'Aucun module disponible.');

        return $this->show($firstSlug);
    }

    public function show(string $slug)
    {
        $module = $this->getModuleDetailsUseCase->execute($slug);

        abort_if(!$module, 404, "Ce module n'existe pas.");

        return view('SuperAdmin::module-details', compact('module'));
    }

    public function updatePrice(Request $request, string $slug)
    {
        $module = SaasModule::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $module->update(['price' => $validated['price']]);

        return redirect()->route('superadmin.module-details.show', $slug)
            ->with('success', "Prix du module « {$module->name} » mis à jour.");
    }
}
