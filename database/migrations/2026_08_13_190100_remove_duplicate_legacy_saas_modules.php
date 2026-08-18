<?php

use App\Modules\SuperAdmin\Domain\Models\SaasModule;
use App\Modules\SuperAdmin\Domain\Models\ServiceCatalogItem;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The previous data-fix migration matched rows by the new canonical
     * slugs, but several legacy rows used different slugs for the same
     * real feature (e.g. slug "cantine" vs "canteen"), so they were left
     * behind as duplicates instead of being renamed in place.
     */
    private array $legacyNames = [
        'Cantine Scolaire & Restauration',   // superseded by "Cantine"
        'Cantine & Restauration Scolaire',   // superseded by "Cantine"
        'Infirmerie & Santé Scolaire',       // superseded by "Infirmerie"
        'Gestion du Personnel & Paie',       // superseded by "RH & Paie"
        'Paiements en Ligne & Frais de Scolarité', // superseded by "Frais Scolaires"
        'Supervision Multi-Campus',          // superseded by "Multi-Succursales"
        'Transport Scolaire GPS',            // superseded by "Transport"
    ];

    public function up(): void
    {
        SaasModule::whereIn('name', $this->legacyNames)->delete();
        ServiceCatalogItem::whereIn('name', $this->legacyNames)->delete();
    }

    public function down(): void
    {
        // Data-correction migration — not meaningfully reversible.
    }
};
