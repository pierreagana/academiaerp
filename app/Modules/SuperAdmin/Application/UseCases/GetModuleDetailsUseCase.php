<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Application\Services\ModuleAdoptionService;
use App\Modules\SuperAdmin\Domain\Models\School;
use App\Modules\SuperAdmin\Domain\Repositories\SaasModuleRepositoryInterface;

class GetModuleDetailsUseCase
{
    /**
     * What each module actually does, in the real app — used to describe
     * the module honestly instead of a generic placeholder list.
     */
    private const FEATURES = [
        'core-academy' => [
            ['title' => 'Classes, matières et salles', 'desc' => "Structuration de l'établissement par classe, matière et salle."],
            ['title' => 'Semestres et langues', 'desc' => "Découpage de l'année scolaire et gestion des langues enseignées."],
            ['title' => 'Programme de cours et emploi du temps', 'desc' => 'Planification des leçons et de l\'emploi du temps hebdomadaire.'],
        ],
        'students-guardians' => [
            ['title' => 'Inscription des élèves', 'desc' => "Fiche complète de l'élève et suivi de son dossier."],
            ['title' => 'Gestion des tuteurs', 'desc' => 'Rattachement des responsables légaux à chaque élève.'],
            ['title' => 'Transfert et promotion', 'desc' => "Changement de section (même niveau) ou promotion vers le niveau supérieur."],
        ],
        'staff-management' => [
            ['title' => 'Gestion des enseignants', 'desc' => "Fiches enseignants et affectation aux classes/matières."],
            ['title' => 'Gestion du personnel', 'desc' => 'Personnel administratif et technique de l\'établissement.'],
            ['title' => "Comptes d'accès portail", 'desc' => 'Identifiant et mot de passe pour se connecter au portail.'],
        ],
        'cards-diplomas' => [
            ['title' => 'Cartes étudiant & personnel', 'desc' => 'Génération avec modèles personnalisables (couleurs, orientation, filigrane).'],
            ['title' => 'Diplômes', 'desc' => "Génération de diplômes pour les élèves."],
            ['title' => 'Impression par lot', 'desc' => "Génération groupée pour plusieurs personnes à la fois."],
        ],
        'presence-access' => [
            ['title' => 'Prise de présence', 'desc' => 'Suivi de présence par classe et par jour.'],
            ['title' => "Contrôle d'accès", 'desc' => "Points d'accès et journaux de passage (scan de badge)."],
        ],
        'fees' => [
            ['title' => 'Frais par niveau', 'desc' => 'Barème des frais scolaires par niveau.'],
            ['title' => 'Paiements', 'desc' => 'Suivi des paiements par élève et par échéance.'],
        ],
        'scholarships' => [
            ['title' => 'Types de bourses', 'desc' => "Critères d'éligibilité par type de bourse."],
            ['title' => 'Décaissements', 'desc' => 'Suivi des décaissements et des documents justificatifs.'],
        ],
        'expenses-budgets' => [
            ['title' => 'Dépenses', 'desc' => "Suivi des dépenses de l'établissement."],
            ['title' => 'Budgets', 'desc' => 'Budgets par catégorie de dépense.'],
        ],
        'hr-payroll' => [
            ['title' => 'Pilotage RH', 'desc' => 'Annuaire du personnel et suivi des contrats.'],
            ['title' => 'Grilles salariales', 'desc' => 'Échelons et rubriques de paie.'],
        ],
        'library' => [
            ['title' => 'Catalogue', 'desc' => 'Ouvrages classés par catégorie.'],
            ['title' => 'Circulation', 'desc' => 'Prêts et retours de livres.'],
        ],
        'canteen' => [
            ['title' => 'Planification des menus', 'desc' => 'Menus hebdomadaires.'],
            ['title' => 'Stocks et réservations', 'desc' => 'Suivi des stocks et réservations de repas.'],
        ],
        'infirmary' => [
            ['title' => 'Interventions', 'desc' => 'Suivi des interventions médicales par élève.'],
            ['title' => 'Pharmacie', 'desc' => 'Gestion des médicaments disponibles.'],
        ],
        'transport' => [
            ['title' => 'Flotte', 'desc' => 'Gestion des bus et des chauffeurs.'],
            ['title' => 'Itinéraires', 'desc' => 'Itinéraires, arrêts et trajets.'],
        ],
        'events' => [
            ['title' => 'Calendrier scolaire', 'desc' => "Vue d'ensemble des événements de l'établissement."],
            ['title' => 'Événements', 'desc' => 'Création et gestion des inscriptions.'],
        ],
        'multi-campus' => [
            ['title' => 'Succursales', 'desc' => "Plusieurs campus/branches pour un même établissement."],
            ['title' => 'Vue Globale', 'desc' => "Vue consolidée sur toutes les succursales, ou vue par succursale."],
        ],
        'report-card' => [
            ['title' => 'Référentiels de compétences', 'desc' => 'Domaines, sous-domaines et compétences par cycle scolaire.'],
            ['title' => "Grille d'évaluation", 'desc' => 'Saisie du niveau de maîtrise par élève, classe et matière.'],
            ['title' => 'Livret par élève', 'desc' => "Cartographie des compétences, assiduité et observations, exportables en PDF."],
        ],
        'bulletins' => [
            ['title' => 'Saisie des notes', 'desc' => 'Notes sur 20 par élève, matière et semestre, pondérées par coefficient.'],
            ['title' => 'Classement de classe', 'desc' => 'Moyennes pondérées et rang calculés en temps réel.'],
            ['title' => 'Validation & impression', 'desc' => "Validation par classe et bulletin imprimable par élève."],
        ],
    ];

    public function __construct(
        private SaasModuleRepositoryInterface $moduleRepository,
        private ModuleAdoptionService $adoptionService
    ) {}

    public function execute(string $slug): ?array
    {
        $module = collect($this->moduleRepository->getAll())->first(fn ($m) => $m->slug === $slug);

        if (!$module) {
            return null;
        }

        $adoptedIds = $this->adoptionService->adoptedSchoolIds($slug);
        $totalSchools = School::count();
        $usagePct = $totalSchools > 0 ? (int) round((count($adoptedIds) / $totalSchools) * 100) : 0;

        $schools = School::orderBy('name')->get(['id', 'name', 'location'])->map(fn ($school) => [
            'name'     => $school->name,
            'location' => $school->location ?? '—',
            'adopted'  => in_array($school->id, $adoptedIds, true),
        ]);

        return [
            'id'             => $module->id,
            'name'           => $module->name,
            'slug'           => $module->slug,
            'description'    => $module->description,
            'status'         => $module->status ?? 'active',
            'price'          => $module->price,
            'version'        => $module->version,
            'features'       => self::FEATURES[$slug] ?? [],
            'adopted_count'  => count($adoptedIds),
            'total_schools'  => $totalSchools,
            'usage_pct'      => $usagePct,
            'revenue_mrr'    => $module->price * count($adoptedIds),
            'schools'        => $schools,
        ];
    }
}
