<?php

use App\Modules\SuperAdmin\Domain\Models\SaasModule;
use App\Modules\SuperAdmin\Domain\Models\ServiceCatalogItem;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Registers the new Livret Scolaire module (app/Modules/ReportCard) in
     * the real SaaS catalog, alongside the 15 modules already listed.
     */
    public function up(): void
    {
        $mod = [
            'slug' => 'report-card',
            'name' => 'Livret Scolaire',
            'description' => "Référentiels de compétences, grille d'évaluation, cartographie des compétences et livret par élève.",
            'icon' => 'ph-notebook',
        ];

        SaasModule::updateOrCreate(
            ['slug' => $mod['slug']],
            [
                'name' => $mod['name'],
                'description' => $mod['description'],
                'icon' => $mod['icon'],
                'status' => 'active',
                'price' => 0,
            ]
        );

        ServiceCatalogItem::updateOrCreate(
            ['name' => $mod['name']],
            [
                'type' => 'Module Système',
                'description' => $mod['description'],
                'icon' => $mod['icon'],
                'icon_bg' => 'bg-blue-100 text-blue-700',
                'price_tag' => 'Inclus',
                'price_color' => 'text-emerald-600',
                'is_enabled' => true,
                'is_beta' => false,
            ]
        );
    }

    public function down(): void
    {
        SaasModule::where('slug', 'report-card')->delete();
        ServiceCatalogItem::where('name', 'Livret Scolaire')->delete();
    }
};
