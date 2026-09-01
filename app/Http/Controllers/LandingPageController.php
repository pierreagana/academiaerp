<?php

namespace App\Http\Controllers;

use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;
use App\Modules\SuperAdmin\Domain\Models\SaasModule;
use App\Modules\SuperAdmin\Domain\Models\SaasPackage;
use App\Modules\SuperAdmin\Domain\Models\ServiceCatalogItem;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        $settingsRaw = GlobalSetting::all()->pluck('value', 'key')->toArray();

        $catalogItems = ServiceCatalogItem::where('is_enabled', true)->get();
        $moduleCount = $catalogItems->count();

        $settings = [
            'platform_name'          => $settingsRaw['platform_name'] ?? 'AcademiaERP SaaS',
            'support_email'          => $settingsRaw['support_email'] ?? 'contact@academiaerp.com',
            'support_phone'          => $settingsRaw['support_phone'] ?? '+221 33 800 00 00',
            'primary_theme_color'    => $settingsRaw['primary_theme_color'] ?? '#031C5B',
            'default_language'       => $settingsRaw['default_language'] ?? 'fr',

            // CMS Landing Content
            'cms_hero_badge'         => $settingsRaw['cms_hero_badge'] ?? 'La Référence SaaS ERP & IA de Gestion Scolaire Multi-Campus',
            'cms_hero_headline'      => $settingsRaw['cms_hero_headline'] ?? 'Pilotez Vos Établissements avec Intendance & IA',
            'cms_hero_subtitle'      => $settingsRaw['cms_hero_subtitle'] ?? 'De la gestion des inscriptions et du calcul des bulletins au paiement Mobile Money (Orange Money, Wave), en passant par la cantine, l\'infirmerie et le suivi GPS des bus.',
            'cms_hero_primary_cta'   => $settingsRaw['cms_hero_primary_cta'] ?? 'Essai Gratuit (30 Jours)',
            'cms_hero_secondary_cta' => $settingsRaw['cms_hero_secondary_cta'] ?? "Explorer les {$moduleCount} Modules",
            'cms_hero_image'         => $settingsRaw['cms_hero_image'] ?? '/images/hero_dashboard.png',

            // Stat Counters
            'cms_stat_schools'       => $settingsRaw['cms_stat_schools'] ?? '48+',
            'cms_stat_students'      => $settingsRaw['cms_stat_students'] ?? '12 500+',
            'cms_stat_mobile_money'  => $settingsRaw['cms_stat_mobile_money'] ?? '100%',
            'cms_stat_sla'           => $settingsRaw['cms_stat_sla'] ?? '99.9%',

            // Pricing & Modules Headings
            'cms_pricing_title'      => $settingsRaw['cms_pricing_title'] ?? 'Des Forfaits Transparents Adaptés à Votre Établissement',
            'cms_pricing_subtitle'   => $settingsRaw['cms_pricing_subtitle'] ?? 'Facturation annuelle claire avec mises à jour et sauvegarde cloud incluses.',
            'cms_modules_title'      => $settingsRaw['cms_modules_title'] ?? "Une Suite Complète de {$moduleCount} Modules Intégrés",
            'cms_modules_subtitle'   => $settingsRaw['cms_modules_subtitle'] ?? 'Découvrez tous les modules spécialisés activables à la carte pour chaque établissement.',

            // Feature Blocks & Images
            'cms_feature1_title'     => $settingsRaw['cms_feature1_title'] ?? 'Encaissement Automatisé par Mobile Money (Orange Money & Wave)',
            'cms_feature1_desc'      => $settingsRaw['cms_feature1_desc'] ?? 'Dites adieu aux longues files d\'attente lors des rentrées scolaires. Les parents règlent directement les échéances de scolarité depuis leur téléphone portable.',
            'cms_feature1_image'    => $settingsRaw['cms_feature1_image'] ?? '/images/mobile_payment.png',

            'cms_feature2_title'     => $settingsRaw['cms_feature2_title'] ?? 'Détection Prédictive du Décrochage Scolaire par l\'IA',
            'cms_feature2_desc'      => $settingsRaw['cms_feature2_desc'] ?? 'Nos algorithmes d\'IA analysent l\'évolution des notes et l\'assiduité des élèves pour signaler précocement les risques de décrochage à la direction pédagogique.',
            'cms_feature2_image'    => $settingsRaw['cms_feature2_image'] ?? '/images/ai_analytics.png',
        ];

        // FAQ Items JSON array
        $faqJson = $settingsRaw['cms_faq_items'] ?? null;
        $faqItems = $faqJson ? json_decode($faqJson, true) : [
            [
                'question' => 'Combien de temps prend l\'installation d\'AcademiaERP dans mon établissement ?',
                'answer'   => 'L’activation de votre espace est instantanée. L\'importation de la liste de vos élèves et enseignants via fichier Excel se fait en moins de 15 minutes avec l\'accompagnement de notre équipe support.',
                'category' => 'Installation',
                'status'   => 'published'
            ],
            [
                'question' => 'Quels sont les moyens de paiement acceptés pour la scolarité ?',
                'answer'   => 'L\'application accepte le paiement par Mobile Money (Orange Money, Wave, MTN), les cartes bancaires (Visa/Mastercard) ainsi que le paiement physique au guichet comptable de l\'établissement.',
                'category' => 'Paiements',
                'status'   => 'published'
            ],
            [
                'question' => 'Peut-on ajouter ou retirer des modules plus tard ?',
                'answer'   => 'Oui, absolument ! Le système est modulaire à 100%. Vous pouvez commencer avec le forfait Starter et ajouter les modules Cantine ou Transport GPS selon vos besoins.',
                'category' => 'Modules',
                'status'   => 'published'
            ]
        ];

        // Testimonials JSON array
        $testimJson = $settingsRaw['cms_testimonials'] ?? null;
        $testimonials = $testimJson ? json_decode($testimJson, true) : [
            [
                'name'    => 'M. Amadou Diallo',
                'role'    => 'Fondateur & Directeur Général',
                'school'  => 'Groupe Scolaire Excellence Dakar',
                'quote'   => 'Depuis l’adoption d’AcademiaERP et du paiement Mobile Money, notre taux de recouvrement des frais de scolarité a atteint 98% dès le 5 du mois !',
                'stars'   => 5
            ],
            [
                'name'    => 'Mme Mariama Ba',
                'role'    => 'Directrice Pédagogique',
                'school'  => 'Complexe Saint-Louis',
                'quote'   => 'L’assistant IA nous a permis d’identifier 12 élèves en risque de décrochage en plein milieu de semestre et de leur proposer un soutien ciblé.',
                'stars'   => 5
            ]
        ];

        $packages = SaasPackage::where('status', 'active')->get();
        if ($packages->isEmpty()) {
            $packages = collect([
                (object)[
                    'id' => 1,
                    'name' => 'Starter',
                    'price' => 150000,
                    'billing_cycle' => 'yearly',
                    'max_students' => 300,
                    'is_popular' => false,
                    'features' => [
                        'Gestion Scolaire & Inscriptions (300 Élèves max)',
                        'Notes, Bulletins & Calcul de Moyennes',
                        'Portail Parents & Élèves Web',
                        'Support Standard par Email'
                    ]
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Pro Excellence',
                    'price' => 350000,
                    'billing_cycle' => 'yearly',
                    'max_students' => 1500,
                    'is_popular' => true,
                    'features' => [
                        'Gestion Scolaire Complexe (1 500 Élèves max)',
                        'Bulletins PDF & Signature Numérique',
                        'Paiements Scolarité Mobile Money & CB (Academia Pay)',
                        'Application Mobile Parents & Notifications Push/SMS',
                        'Module Cantine Scolaire & Restauration',
                        'Infirmerie & Dossiers Médicaux Santé',
                        'Support Prioritaire 24/7'
                    ]
                ],
                (object)[
                    'id' => 3,
                    'name' => 'Enterprise Multi-Campus',
                    'price' => 750000,
                    'billing_cycle' => 'yearly',
                    'max_students' => 0,
                    'is_popular' => false,
                    'features' => [
                        'Élèves Illimités & Supervision Multi-Campus',
                        'Tous les Modules Inclus (Scolaire, Finance, Cantine, Infirmerie, Transport)',
                        'Assistant IA Détecteur du Décrochage & Remédiation',
                        'Transport Scolaire GPS & Pointage RFID',
                        'Module RH, Paie & Gestion du Personnel',
                        'API REST & Intégrations sur mesure',
                        'Support Dédié & Garantie SLA 99.9%'
                    ]
                ]
            ]);
        }

        $facilities = \App\Modules\SuperAdmin\Domain\Models\Facility::where('is_active', true)->orderBy('order')->orderBy('name')->get();
        $availableSectors = \App\Modules\SuperAdmin\Domain\Models\School::getAvailableSectors();
        $availableLevels = \App\Modules\SuperAdmin\Domain\Models\School::getAvailableLevels();
        $availableLanguageRegimes = \App\Modules\SuperAdmin\Domain\Models\School::getAvailableLanguageRegimes();

        return view('landing', compact(
            'settings', 'packages', 'catalogItems', 'faqItems', 'testimonials',
            'facilities', 'availableSectors', 'availableLevels', 'availableLanguageRegimes'
        ));
    }
}
