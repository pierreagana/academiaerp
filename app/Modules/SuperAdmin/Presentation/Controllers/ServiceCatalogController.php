<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use Illuminate\Routing\Controller;
use App\Modules\SuperAdmin\Application\UseCases\ListServiceCatalogUseCase;

class ServiceCatalogController extends Controller
{
    public function __construct(
        private ListServiceCatalogUseCase $listServiceCatalogUseCase
    ) {}

    public function index()
    {
        $data = $this->listServiceCatalogUseCase->execute();

        // Catalog items and SaaS modules are only linked by matching name
        // (same pairing used in toggle() below), so resolve each item's
        // real module slug for the "Détails" link instead of re-deriving a
        // slug from the display name, which almost never matches.
        $moduleSlugsByName = collect($data['packages'])->keyBy('name')->map(fn ($m) => $m->slug);

        $addons = [];
        foreach ($data['services'] as $item) {
            $addons[] = [
                'id'          => $item->id,
                'name'        => $item->name,
                'slug'        => $moduleSlugsByName->get($item->name),
                'type'        => $item->type,
                'description' => $item->description,
                'price_tag'   => $item->priceTag ?? 'Gratuit',
                'price_color' => $item->priceColor ?? 'text-emerald-600',
                'icon'        => $item->icon ?? 'ph-puzzle-piece',
                'icon_bg'     => $item->iconBg ?? 'bg-purple-100 text-purple-600',
                'is_enabled'  => $item->isEnabled,
                'is_beta'     => $item->isBeta,
            ];
        }

        $packages = [
            [
                'id' => 1,
                'name' => 'Starter',
                'tagline' => 'Pour les petits établissements scolaires',
                'price' => '49.000 FCFA',
                'period' => '/ mois',
                'is_popular' => false,
                'features' => [
                    ['text' => 'Jusqu\'à 300 élèves', 'included' => true, 'bold' => true],
                    ['text' => 'Gestion des notes & bulletins', 'included' => true],
                    ['text' => 'Portail parents & élèves', 'included' => true],
                    ['text' => 'Comptabilité avancée', 'included' => false],
                    ['text' => 'Module IA prédictif', 'included' => false],
                ]
            ],
            [
                'id' => 2,
                'name' => 'Pro Campus',
                'tagline' => 'Pour collèges & lycées en pleine croissance',
                'price' => '129.000 FCFA',
                'period' => '/ mois',
                'is_popular' => true,
                'features' => [
                    ['text' => 'Jusqu\'à 1 500 élèves', 'included' => true, 'bold' => true],
                    ['text' => 'Gestion des notes & bulletins', 'included' => true],
                    ['text' => 'Portail parents, élèves & profs', 'included' => true],
                    ['text' => 'Comptabilité & Facturation', 'included' => true],
                    ['text' => 'Module IA prédictif', 'included' => false],
                ]
            ],
            [
                'id' => 3,
                'name' => 'Enterprise',
                'tagline' => 'Grands groupes & réseaux multi-campus',
                'price' => '299.000 FCFA',
                'period' => '/ mois',
                'is_popular' => false,
                'features' => [
                    ['text' => 'Élèves illimités & Multi-campus', 'included' => true, 'bold' => true],
                    ['text' => 'Gestion intégrale + RH/Paie', 'included' => true],
                    ['text' => 'API REST & Intégrations sur mesure', 'included' => true],
                    ['text' => 'Comptabilité & Facturation', 'included' => true],
                    ['text' => 'Module IA prédictif & Anomaly Engine', 'included' => true, 'bold' => true],
                ]
            ],
        ];

        $slas = [
            [
                'forfait' => 'Enterprise',
                'uptime' => '99.9%',
                'has_check' => true,
                'support_response' => '< 1 heure (24/7)',
                'penalty' => 'Credit 10% si < 99.9%',
            ],
            [
                'forfait' => 'Pro Campus',
                'uptime' => '99.5%',
                'has_check' => true,
                'support_response' => '< 4 heures',
                'penalty' => 'Credit 5% si < 99.5%',
            ],
            [
                'forfait' => 'Starter',
                'uptime' => '99.0%',
                'has_check' => false,
                'support_response' => '< 24 heures',
                'penalty' => 'Aucune',
            ],
        ];

        $availableModules = \App\Modules\SuperAdmin\Domain\Models\SaasModule::where('status', 'active')->get();

        return view('SuperAdmin::service-catalog', compact('addons', 'packages', 'slas', 'availableModules'));
    }

    public function toggle(int $id)
    {
        $item = \App\Modules\SuperAdmin\Domain\Models\ServiceCatalogItem::findOrFail($id);
        $item->is_enabled = !$item->is_enabled;
        $item->save();

        $saasModule = \App\Modules\SuperAdmin\Domain\Models\SaasModule::where('name', $item->name)->first();
        if ($saasModule) {
            $saasModule->status = $item->is_enabled ? 'active' : 'inactive';
            $saasModule->save();
        }

        $statusText = $item->is_enabled ? 'activé' : 'désactivé';
        return redirect()->back()->with('success', "Le service '{$item->name}' a été {$statusText} avec succès sur le système.");
    }
}
