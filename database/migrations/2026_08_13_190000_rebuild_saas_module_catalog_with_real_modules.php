<?php

use App\Modules\SuperAdmin\Domain\Models\SaasModule;
use App\Modules\SuperAdmin\Domain\Models\ServiceCatalogItem;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Rebuilds the SaaS module catalog to reflect only features that are
     * actually built in the School app (app/Modules/*), instead of the
     * fictional entries (fake SMS/WhatsApp portals, AI dropout detection,
     * biometrics, etc.) that had no code behind them.
     */
    private array $realModules = [
        ['slug' => 'core-academy', 'name' => 'Académie de Base', 'description' => "Classes, matières, salles, semestres, langues, programme de cours et emploi du temps.", 'icon' => 'ph-student'],
        ['slug' => 'students-guardians', 'name' => 'Étudiants & Tuteurs', 'description' => "Inscription des élèves, gestion des tuteurs, transfert de section et promotion de niveau.", 'icon' => 'ph-graduation-cap'],
        ['slug' => 'staff-management', 'name' => 'Enseignants & Personnel', 'description' => "Gestion des enseignants et du personnel, comptes d'accès portail.", 'icon' => 'ph-users-three'],
        ['slug' => 'cards-diplomas', 'name' => 'Cartes & Diplômes', 'description' => 'Génération de cartes étudiant/personnel et diplômes personnalisables.', 'icon' => 'ph-identification-card'],
        ['slug' => 'presence-access', 'name' => "Présence & Contrôle d'Accès", 'description' => "Prise de présence, points d'accès et journaux de passage.", 'icon' => 'ph-fingerprint'],
        ['slug' => 'fees', 'name' => 'Frais Scolaires', 'description' => 'Frais par niveau, paiements et échéanciers.', 'icon' => 'ph-currency-circle-dollar'],
        ['slug' => 'scholarships', 'name' => 'Bourses', 'description' => "Types de bourses, critères d'éligibilité et décaissements.", 'icon' => 'ph-medal'],
        ['slug' => 'expenses-budgets', 'name' => 'Dépenses & Budgets', 'description' => "Suivi des dépenses et des budgets de l'établissement.", 'icon' => 'ph-chart-pie-slice'],
        ['slug' => 'hr-payroll', 'name' => 'RH & Paie', 'description' => 'Pilotage RH, grilles salariales, rubriques de paie et contrats.', 'icon' => 'ph-briefcase'],
        ['slug' => 'library', 'name' => 'Bibliothèque', 'description' => 'Catalogue, circulation des prêts et paramètres.', 'icon' => 'ph-books'],
        ['slug' => 'canteen', 'name' => 'Cantine', 'description' => 'Planification des menus, stocks et réservations.', 'icon' => 'ph-bowl-food'],
        ['slug' => 'infirmary', 'name' => 'Infirmerie', 'description' => 'Interventions, suivi médical des élèves et pharmacie.', 'icon' => 'ph-first-aid-kit'],
        ['slug' => 'transport', 'name' => 'Transport', 'description' => 'Flotte, itinéraires, arrêts, trajets et chauffeurs.', 'icon' => 'ph-bus'],
        ['slug' => 'events', 'name' => 'Communication & Événements', 'description' => "Calendrier scolaire, création d'événements et inscriptions.", 'icon' => 'ph-calendar-heart'],
        ['slug' => 'multi-campus', 'name' => 'Multi-Succursales', 'description' => 'Gestion de plusieurs campus/branches et vue consolidée.', 'icon' => 'ph-buildings'],
    ];

    /** Entries with no corresponding feature anywhere in the codebase. */
    private array $fictionalNames = [
        'Communication (SMS/Email)',
        'Bulletins & Calcul de Moyennes',
        'Portail Parents & SMS / WhatsApp',
        'Assistant IA & Détection du Décrochage',
        'Portail Apprenant & Tuteur Virtuel IA',
        'Support Prioritaire 24/7 & SLA 99.9%',
        'Biométrie & Sécurité',
        // Duplicate of "Académie de Base" under a different label.
        'Gestion Scolaire & Inscriptions',
    ];

    public function up(): void
    {
        SaasModule::whereIn('name', $this->fictionalNames)->delete();
        ServiceCatalogItem::whereIn('name', $this->fictionalNames)->delete();

        foreach ($this->realModules as $mod) {
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
    }

    public function down(): void
    {
        // Data-correction migration — not meaningfully reversible.
    }
};
