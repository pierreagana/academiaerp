<?php

use App\Modules\SuperAdmin\Domain\Models\SaasModule;
use App\Modules\SuperAdmin\Domain\Models\ServiceCatalogItem;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Registers the new Devoirs & Interrogations module (app/Modules/Homework) in
     * the real SaaS catalog, alongside the 17 modules already listed.
     */
    public function up(): void
    {
        $mod = [
            'slug' => 'homework',
            'name' => 'Devoirs & Interrogations',
            'description' => 'Devoirs maison, interrogations et contrôles en classe : création, suivi des rendus, notation et session en direct.',
            'icon' => 'ph-exam',
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
        SaasModule::where('slug', 'homework')->delete();
        ServiceCatalogItem::where('name', 'Devoirs & Interrogations')->delete();
    }
};
