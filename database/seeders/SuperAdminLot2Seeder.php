<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\SuperAdmin\Domain\Models\SaasPackage;
use App\Modules\SuperAdmin\Domain\Models\SaasModule;
use App\Modules\SuperAdmin\Domain\Models\Invoice;
use Carbon\Carbon;

class SuperAdminLot2Seeder extends Seeder
{
    public function run(): void
    {
        // Packages
        SaasPackage::create([
            'name' => 'Starter',
            'price' => 29.00,
            'billing_cycle' => 'monthly',
            'max_students' => 500,
            'max_storage_gb' => 50,
            'features' => ['Gestion Scolaire de base', '1 Compte Admin', 'Support Standard'],
            'status' => 'active',
            'is_popular' => false,
        ]);

        SaasPackage::create([
            'name' => 'Premium',
            'price' => 99.00,
            'billing_cycle' => 'monthly',
            'max_students' => 2000,
            'max_storage_gb' => 250,
            'features' => ['Tout du Starter', 'Comptes illimités', 'IA Predictive', 'Support Prioritaire'],
            'status' => 'active',
            'is_popular' => true,
        ]);

        SaasPackage::create([
            'name' => 'Enterprise',
            'price' => 299.00,
            'billing_cycle' => 'monthly',
            'max_students' => null,
            'max_storage_gb' => 1000,
            'features' => ['Tout du Premium', 'Multi-Campus', 'API Dédiée', 'Account Manager'],
            'status' => 'active',
            'is_popular' => false,
        ]);

        // Modules
        SaasModule::create([
            'name' => 'Académie de Base',
            'slug' => 'core-academy',
            'description' => 'Gestion des élèves, classes et présences.',
            'icon' => 'ph-student',
            'version' => '1.0',
            'status' => 'active',
            'price' => 0,
            'required_plans' => ['Starter', 'Premium', 'Enterprise']
        ]);
        
        SaasModule::create([
            'name' => 'Finance & Facturation',
            'slug' => 'finance',
            'description' => 'Paiements scolaires, relances automatisées.',
            'icon' => 'ph-currency-circle-dollar',
            'version' => '2.1',
            'status' => 'active',
            'price' => 10,
            'required_plans' => ['Premium', 'Enterprise']
        ]);
        
        SaasModule::create([
            'name' => 'Communication (SMS/Email)',
            'slug' => 'communications',
            'description' => 'Portail parents, notifications.',
            'icon' => 'ph-paper-plane-tilt',
            'version' => '1.5',
            'status' => 'active',
            'price' => 5,
            'required_plans' => ['Premium', 'Enterprise']
        ]);

        // Invoices
        Invoice::create([
            'invoice_number' => 'INV-2023-0891',
            'school_name' => 'Green Valley Academy',
            'amount' => 15000.00,
            'status' => 'paid',
            'issue_date' => Carbon::now()->subDays(5),
            'due_date' => Carbon::now()->addDays(25),
        ]);

        Invoice::create([
            'invoice_number' => 'INV-2023-0892',
            'school_name' => 'St. Jude\'s Academy',
            'amount' => 12500.00,
            'status' => 'pending',
            'issue_date' => Carbon::now()->subDays(10),
            'due_date' => Carbon::now()->addDays(20),
        ]);

        Invoice::create([
            'invoice_number' => 'INV-2023-0875',
            'school_name' => 'Oakridge High',
            'amount' => 8750.00,
            'status' => 'failed',
            'issue_date' => Carbon::now()->subDays(45),
            'due_date' => Carbon::now()->subDays(15),
        ]);
        
        Invoice::create([
            'invoice_number' => 'INV-2023-0894',
            'school_name' => 'Lakeside Prep',
            'amount' => 32000.00,
            'status' => 'paid',
            'issue_date' => Carbon::now()->subDays(12),
            'due_date' => Carbon::now()->addDays(18),
        ]);
    }
}
