<?php

use App\Modules\SuperAdmin\Domain\Models\SaasModule;
use App\Modules\SuperAdmin\Domain\Models\ServiceCatalogItem;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Registers the new Bulletins de Notes module (app/Modules/Bulletin) in
     * the real SaaS catalog, alongside the 16 modules already listed.
     */
    public function up(): void
    {
        $mod = [
            'slug' => 'bulletins',
            'name' => 'Bulletins de Notes',
            'description' => 'Notes chiffrées par matière, moyennes pondérées, classement de classe, validation et impression des bulletins.',
            'icon' => 'ph-ranking',
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
        SaasModule::where('slug', 'bulletins')->delete();
        ServiceCatalogItem::where('name', 'Bulletins de Notes')->delete();
    }
};
