<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\SuperAdmin\Domain\Models\School;
use App\Modules\SuperAdmin\Domain\Models\RegistrationRequest;
use Carbon\Carbon;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Schools
        School::create([
            'name' => 'Lycée d\'Excellence',
            'type' => 'Lycée',
            'status' => 'actif',
            'plan_name' => 'Premium',
            'students_count' => 1250,
            'storage_used_gb' => 45.5,
            'location' => 'Dakar, Sénégal',
            'contact_email' => 'contact@lexcellence.sn',
            'contact_phone' => '+221 77 123 45 67',
            'founded_date' => Carbon::parse('2025-12-12'),
        ]);

        School::create([
            'name' => 'Complexe Scolaire Les Leaders',
            'type' => 'Complexe',
            'status' => 'actif',
            'plan_name' => 'Starter',
            'students_count' => 840,
            'storage_used_gb' => 12.0,
            'location' => 'Abidjan, CI',
            'contact_email' => 'info@lesleaders.ci',
            'contact_phone' => '+225 07 12 34 56',
            'founded_date' => Carbon::parse('2025-11-05'),
        ]);

        School::create([
            'name' => 'Institut Saint-Jean',
            'type' => 'Institut',
            'status' => 'inactif',
            'plan_name' => 'Enterprise',
            'students_count' => 320,
            'storage_used_gb' => 120.5,
            'location' => 'Yaoundé, Cameroun',
            'contact_email' => 'admin@isj.cm',
            'contact_phone' => '+237 6 12 34 56 78',
            'founded_date' => Carbon::now()->subDays(2),
        ]);

        School::create([
            'name' => 'Groupe Scolaire Aminata',
            'type' => 'Groupe Scolaire',
            'status' => 'en attente',
            'plan_name' => 'Premium',
            'students_count' => 0,
            'storage_used_gb' => 0,
            'location' => 'Bamako, Mali',
            'contact_email' => 'contact@gs-aminata.ml',
            'contact_phone' => '+223 71 23 45 67',
            'founded_date' => null,
        ]);

        School::create([
            'name' => 'Collège Notre-Dame',
            'type' => 'Collège',
            'status' => 'actif',
            'plan_name' => 'Enterprise',
            'students_count' => 2100,
            'storage_used_gb' => 85.0,
            'location' => 'Libreville, Gabon',
            'contact_email' => 'direction@cnd.ga',
            'contact_phone' => '+241 01 23 45 67',
            'founded_date' => Carbon::parse('2026-01-24'),
        ]);

        // Seed Registration Requests
        RegistrationRequest::create([
            'school_name' => 'Lycée Moderne de Cocody',
            'applicant_name' => 'Jean-Paul Kouadio',
            'email' => 'jp.kouadio@lmc.ci',
            'phone' => '+225 07 78 45 12',
            'region' => 'Abidjan, CI',
            'status' => 'en attente',
            'plan_requested' => 'Premium',
            'created_at' => Carbon::now()->subHours(2),
        ]);

        RegistrationRequest::create([
            'school_name' => 'Complexe Scolaire La Base',
            'applicant_name' => 'Marie Ndiaye',
            'email' => 'direction@labase.sn',
            'phone' => '+221 77 456 12 89',
            'region' => 'Dakar, Sénégal',
            'status' => 'en revue',
            'plan_requested' => 'Starter',
            'created_at' => Carbon::now()->subHours(5),
        ]);

        RegistrationRequest::create([
            'school_name' => 'Institut Privé Descartes',
            'applicant_name' => 'Paul Mensah',
            'email' => 'admin@descartes.tg',
            'phone' => '+228 90 12 34 56',
            'region' => 'Lomé, Togo',
            'status' => 'approuvée',
            'plan_requested' => 'Enterprise',
            'created_at' => Carbon::now()->subDays(1),
        ]);
        
        RegistrationRequest::create([
            'school_name' => 'École Primaire Les Anges',
            'applicant_name' => 'Sophie Diallo',
            'email' => 'contact@lesanges.gn',
            'phone' => '+224 620 12 34 56',
            'region' => 'Conakry, Guinée',
            'status' => 'rejetée',
            'plan_requested' => 'Basic',
            'created_at' => Carbon::now()->subDays(2),
        ]);
    }
}
