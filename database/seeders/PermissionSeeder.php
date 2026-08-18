<?php

namespace Database\Seeders;

use App\Modules\Academic\Domain\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['slug' => 'academic.classes.manage', 'name' => 'Gestion des classes', 'group' => 'Académique'],
            ['slug' => 'academic.subjects.manage', 'name' => 'Gestion des matières', 'group' => 'Académique'],
            ['slug' => 'academic.rooms.manage', 'name' => 'Gestion des salles & bâtiments', 'group' => 'Académique'],
            ['slug' => 'academic.cards.manage', 'name' => 'Cartes étudiant & personnel', 'group' => 'Académique'],
            ['slug' => 'academic.semesters.manage', 'name' => 'Gestion des semestres', 'group' => 'Académique'],
            ['slug' => 'academic.languages.manage', 'name' => 'Gestion des langues', 'group' => 'Académique'],
            ['slug' => 'academic.syllabuses.manage', 'name' => 'Programmes de cours & leçons', 'group' => 'Académique'],
            ['slug' => 'academic.timetable.manage', 'name' => 'Emploi du temps', 'group' => 'Académique'],
            ['slug' => 'academic.students.manage', 'name' => 'Gestion des étudiants', 'group' => 'Étudiants'],
            ['slug' => 'academic.parents.manage', 'name' => 'Gestion des tuteurs', 'group' => 'Étudiants'],
            ['slug' => 'academic.teachers.manage', 'name' => 'Gestion des enseignants', 'group' => 'Ressources Humaines'],
            ['slug' => 'academic.personnel.manage', 'name' => 'Gestion du personnel', 'group' => 'Ressources Humaines'],
            ['slug' => 'finance.fees.manage', 'name' => 'Gestion des frais de scolarité', 'group' => 'Finances'],
            ['slug' => 'finance.scholarships.manage', 'name' => 'Gestion des bourses', 'group' => 'Finances'],
            ['slug' => 'finance.expenses.manage', 'name' => 'Gestion des dépenses', 'group' => 'Finances'],
            ['slug' => 'communication.events.manage', 'name' => 'Gestion des événements', 'group' => 'Communication'],
            ['slug' => 'library.manage', 'name' => 'Gestion de la bibliothèque', 'group' => 'Vie Scolaire'],
            ['slug' => 'canteen.manage', 'name' => 'Gestion de la cantine', 'group' => 'Vie Scolaire'],
            ['slug' => 'infirmary.manage', 'name' => 'Gestion de l\'infirmerie', 'group' => 'Vie Scolaire'],
            ['slug' => 'transport.manage', 'name' => 'Gestion du transport', 'group' => 'Vie Scolaire'],
            ['slug' => 'hr.manage', 'name' => 'Pilotage RH & paie', 'group' => 'Ressources Humaines'],
            ['slug' => 'academic.presence.manage', 'name' => 'Présence & contrôle d\'accès', 'group' => 'Vie Scolaire'],
            ['slug' => 'branches.manage', 'name' => 'Gestion des succursales', 'group' => 'Académique'],
            ['slug' => 'report-card.manage', 'name' => 'Livret Scolaire & Compétences', 'group' => 'Vie Scolaire'],
            ['slug' => 'academic.bulletins.manage', 'name' => 'Bulletins de Notes', 'group' => 'Académique'],
            ['slug' => 'academic.homework.manage', 'name' => 'Devoirs & Interrogations', 'group' => 'Académique'],
            ['slug' => 'establishment.manage', 'name' => 'Profil de l\'établissement', 'group' => 'Administration'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }
    }
}
