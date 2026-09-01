<?php

use App\Modules\SuperAdmin\Domain\Models\SaasPackage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * `User::schoolHasModuleAccess()` gates every real module (Cantine,
     * Transport, Bulletins de Notes...) by checking whether its canonical
     * name is present in the school's active package `features` array.
     * "Pro Excellence" and "Enterprise Multi-Campus" already carry a real,
     * coherent, cumulative feature list (verified live: Pro Excellence's 12
     * modules are a strict subset of Enterprise Multi-Campus's 18) — only
     * "Starter" has an empty `features` array, meaning a Starter-plan school
     * would be locked out of every gated module, including core academic
     * management. This assigns Starter a sensible essentials-only subset,
     * strictly contained in Pro Excellence's existing list, so the 3-tier
     * hierarchy is actually coherent before real plan names start being
     * assigned to new schools by default (see RegistrationRequestController /
     * SchoolController / AuthController changes in this same batch of work).
     */
    private const STARTER_MODULES = [
        'Académie de Base',
        'Étudiants & Tuteurs',
        'Enseignants & Personnel',
        "Présence & Contrôle d'Accès",
        'Frais Scolaires',
        'Bulletins de Notes',
    ];

    public function up(): void
    {
        $package = SaasPackage::where('name', 'Starter')->first();
        if (!$package) {
            return;
        }

        $features = is_array($package->features) ? $package->features : [];
        $package->update(['features' => array_values(array_unique(array_merge($features, self::STARTER_MODULES)))]);
    }

    public function down(): void
    {
        $package = SaasPackage::where('name', 'Starter')->first();
        if (!$package) {
            return;
        }

        $features = is_array($package->features) ? $package->features : [];
        $package->update(['features' => array_values(array_diff($features, self::STARTER_MODULES))]);
    }
};
