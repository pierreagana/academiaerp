<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Application\UseCases\GetCmsDataUseCase;
use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    public function __construct(
        private GetCmsDataUseCase $getCmsDataUseCase
    ) {}

    public function index()
    {
        $settingsRaw = GlobalSetting::all()->pluck('value', 'key')->toArray();

        $cmsSettings = [
            'platform_name'          => $settingsRaw['platform_name'] ?? 'AcademiaERP SaaS',
            'support_email'          => $settingsRaw['support_email'] ?? 'support@academiaerp.com',
            'support_phone'          => $settingsRaw['support_phone'] ?? '+221 33 800 00 00',

            // Hero Section
            'cms_hero_badge'         => $settingsRaw['cms_hero_badge'] ?? 'La Référence SaaS ERP & IA de Gestion Scolaire Multi-Campus',
            'cms_hero_headline'      => $settingsRaw['cms_hero_headline'] ?? 'Pilotez Vos Établissements avec Intendance & IA',
            'cms_hero_subtitle'      => $settingsRaw['cms_hero_subtitle'] ?? 'De la gestion des inscriptions et du calcul des bulletins au paiement Mobile Money (Orange Money, Wave), en passant par la cantine, l\'infirmerie et le suivi GPS des bus.',
            'cms_hero_primary_cta'   => $settingsRaw['cms_hero_primary_cta'] ?? 'Essai Gratuit (30 Jours)',
            'cms_hero_secondary_cta' => $settingsRaw['cms_hero_secondary_cta'] ?? 'Explorer les 12 Modules',
            'cms_hero_image'         => $settingsRaw['cms_hero_image'] ?? '/images/hero_dashboard.png',

            // Stat Counters
            'cms_stat_schools'       => $settingsRaw['cms_stat_schools'] ?? '48+',
            'cms_stat_students'      => $settingsRaw['cms_stat_students'] ?? '12 500+',
            'cms_stat_mobile_money'  => $settingsRaw['cms_stat_mobile_money'] ?? '100%',
            'cms_stat_sla'           => $settingsRaw['cms_stat_sla'] ?? '99.9%',

            // Pricing Section Headings
            'cms_pricing_title'      => $settingsRaw['cms_pricing_title'] ?? 'Des Forfaits Transparents Adaptés à Votre Établissement',
            'cms_pricing_subtitle'   => $settingsRaw['cms_pricing_subtitle'] ?? 'Facturation annuelle claire avec mises à jour et sauvegarde cloud incluses.',

            // Modules Section Headings
            'cms_modules_title'      => $settingsRaw['cms_modules_title'] ?? 'Une Suite Complète de 12 Modules Intégrés',
            'cms_modules_subtitle'   => $settingsRaw['cms_modules_subtitle'] ?? 'Découvrez tous les modules spécialisés activables à la carte pour chaque établissement.',

            // Features Block
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

        return view('SuperAdmin::cms', compact('cmsSettings', 'faqItems', 'testimonials'));
    }

    public function updateLandingCms(Request $request)
    {
        $data = $request->except(['_token', 'hero_image_file', 'feature1_image_file', 'feature2_image_file']);

        // Handle File Uploads
        if ($request->hasFile('hero_image_file')) {
            $file = $request->file('hero_image_file');
            $fileName = 'hero_uploaded_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $fileName);
            $data['cms_hero_image'] = '/images/' . $fileName;
        }

        if ($request->hasFile('feature1_image_file')) {
            $file = $request->file('feature1_image_file');
            $fileName = 'feature1_uploaded_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $fileName);
            $data['cms_feature1_image'] = '/images/' . $fileName;
        }

        if ($request->hasFile('feature2_image_file')) {
            $file = $request->file('feature2_image_file');
            $fileName = 'feature2_uploaded_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $fileName);
            $data['cms_feature2_image'] = '/images/' . $fileName;
        }

        foreach ($data as $key => $value) {
            GlobalSetting::updateOrCreate(
                ['key' => $key],
                ['value' => (string)$value]
            );
        }

        return redirect()->route('superadmin.cms')
            ->with('success', 'Tous les textes et illustrations de la Landing Page ont été mis à jour avec succès !');
    }

    public function addFaq(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer'   => 'required|string',
            'category' => 'required|string|max:50',
        ]);

        $settingsRaw = GlobalSetting::where('key', 'cms_faq_items')->value('value');
        $faqItems = $settingsRaw ? json_decode($settingsRaw, true) : [];

        $faqItems[] = [
            'question' => $request->input('question'),
            'answer'   => $request->input('answer'),
            'category' => $request->input('category'),
            'status'   => 'published',
        ];

        GlobalSetting::updateOrCreate(
            ['key' => 'cms_faq_items'],
            ['value' => json_encode($faqItems)]
        );

        return redirect()->route('superadmin.cms')
            ->with('success', 'Nouvelle question FAQ ajoutée à la Landing Page avec succès !');
    }

    public function deleteFaq($index)
    {
        $settingsRaw = GlobalSetting::where('key', 'cms_faq_items')->value('value');
        $faqItems = $settingsRaw ? json_decode($settingsRaw, true) : [];

        if (isset($faqItems[$index])) {
            array_splice($faqItems, $index, 1);
            GlobalSetting::updateOrCreate(
                ['key' => 'cms_faq_items'],
                ['value' => json_encode($faqItems)]
            );
        }

        return redirect()->route('superadmin.cms')
            ->with('success', 'Question FAQ supprimée de la Landing Page avec succès !');
    }

    public function addTestimonial(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'role'   => 'required|string|max:255',
            'school' => 'required|string|max:255',
            'quote'  => 'required|string',
        ]);

        $settingsRaw = GlobalSetting::where('key', 'cms_testimonials')->value('value');
        $testimonials = $settingsRaw ? json_decode($settingsRaw, true) : [];

        $testimonials[] = [
            'name'   => $request->input('name'),
            'role'   => $request->input('role'),
            'school' => $request->input('school'),
            'quote'  => $request->input('quote'),
            'stars'  => (int)$request->input('stars', 5),
        ];

        GlobalSetting::updateOrCreate(
            ['key' => 'cms_testimonials'],
            ['value' => json_encode($testimonials)]
        );

        return redirect()->route('superadmin.cms')
            ->with('success', 'Nouveau témoignage client ajouté à la Landing Page avec succès !');
    }

    public function deleteTestimonial($index)
    {
        $settingsRaw = GlobalSetting::where('key', 'cms_testimonials')->value('value');
        $testimonials = $settingsRaw ? json_decode($settingsRaw, true) : [];

        if (isset($testimonials[$index])) {
            array_splice($testimonials, $index, 1);
            GlobalSetting::updateOrCreate(
                ['key' => 'cms_testimonials'],
                ['value' => json_encode($testimonials)]
            );
        }

        return redirect()->route('superadmin.cms')
            ->with('success', 'Témoignage supprimé de la Landing Page avec succès !');
    }
}
