<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\SuperAdmin\Domain\Models\AIModel;
use App\Modules\SuperAdmin\Domain\Models\ServiceCatalogItem;
use App\Modules\SuperAdmin\Domain\Models\StaffMember;
use App\Modules\SuperAdmin\Domain\Models\NetworkNode;
use App\Modules\SuperAdmin\Domain\Models\SystemAlertRule;
use App\Modules\SuperAdmin\Domain\Models\BackupLog;

class SuperAdminLot5Seeder extends Seeder
{
    public function run(): void
    {
        // 1. AI Models
        $aiModels = [
            [
                'name'         => 'GPT-4o',
                'provider'     => 'OpenAI via Azure EU',
                'status'       => 'active',
                'status_label' => 'Actif · Pédagogie',
                'latency'      => '450ms',
                'color'        => 'emerald',
            ],
            [
                'name'         => 'Claude 3.5 Sonnet',
                'provider'     => 'Anthropic API',
                'status'       => 'active',
                'status_label' => 'Actif · Analyse',
                'latency'      => '320ms',
                'color'        => 'violet',
            ],
            [
                'name'         => 'Gemini 1.5 Pro',
                'provider'     => 'Google Cloud Vertex',
                'status'       => 'standby',
                'status_label' => 'En veille (Fallback)',
                'latency'      => null,
                'color'        => 'slate',
            ],
        ];

        foreach ($aiModels as $m) {
            AIModel::firstOrCreate(['name' => $m['name']], $m);
        }

        // 2. Service Catalog Items — Les 12 Modules AcademiaERP
        $catalogItems = [
            [
                'name'        => 'Gestion Scolaire & Inscriptions',
                'type'        => 'module',
                'description' => 'Gestion des dossiers élèves, inscriptions, classes, niveaux et archives scolaires.',
                'price_tag'   => 'Inclus',
                'price_color' => 'text-emerald-600',
                'icon'        => 'ph-graduation-cap',
                'icon_bg'     => 'bg-blue-100 text-blue-700',
                'is_enabled'  => true,
                'is_beta'     => false,
            ],
            [
                'name'        => 'Bulletins & Calcul de Moyennes',
                'type'        => 'module',
                'description' => 'Génération automatique de bulletins PDF, calcul de moyennes pondérées et classements.',
                'price_tag'   => 'Inclus',
                'price_color' => 'text-emerald-600',
                'icon'        => 'ph-certificate',
                'icon_bg'     => 'bg-indigo-100 text-indigo-700',
                'is_enabled'  => true,
                'is_beta'     => false,
            ],
            [
                'name'        => 'Paiements en Ligne & Frais de Scolarité',
                'type'        => 'module',
                'description' => 'Collecte des frais de scolarité par Mobile Money (Orange Money, Wave, MTN) et carte bancaire.',
                'price_tag'   => 'Pro+',
                'price_color' => 'text-blue-700',
                'icon'        => 'ph-device-mobile',
                'icon_bg'     => 'bg-emerald-100 text-emerald-700',
                'is_enabled'  => true,
                'is_beta'     => false,
            ],
            [
                'name'        => 'Portail Parents & SMS / WhatsApp',
                'type'        => 'module',
                'description' => 'Application mobile parents avec notifications push, SMS et partage de bulletins.',
                'price_tag'   => 'Pro+',
                'price_color' => 'text-blue-700',
                'icon'        => 'ph-chats',
                'icon_bg'     => 'bg-amber-100 text-amber-700',
                'is_enabled'  => true,
                'is_beta'     => false,
            ],
            [
                'name'        => 'Cantine & Restauration Scolaire',
                'type'        => 'module',
                'description' => 'Gestion des menus, abonnements cantine, pointage quotidien et facturation des repas.',
                'price_tag'   => 'Pro+',
                'price_color' => 'text-blue-700',
                'icon'        => 'ph-fork-knife',
                'icon_bg'     => 'bg-orange-100 text-orange-700',
                'is_enabled'  => true,
                'is_beta'     => false,
            ],
            [
                'name'        => 'Infirmerie & Santé Scolaire',
                'type'        => 'module',
                'description' => 'Suivi médical des élèves, dossiers de santé, fiches d\'incidents et carnets de vaccins.',
                'price_tag'   => 'Pro+',
                'price_color' => 'text-blue-700',
                'icon'        => 'ph-first-aid',
                'icon_bg'     => 'bg-red-100 text-red-600',
                'is_enabled'  => true,
                'is_beta'     => false,
            ],
            [
                'name'        => 'Transport Scolaire GPS',
                'type'        => 'module',
                'description' => 'Suivi en temps réel des bus scolaires, pointage RFID élèves et notifications parents.',
                'price_tag'   => 'Enterprise',
                'price_color' => 'text-purple-700',
                'icon'        => 'ph-bus',
                'icon_bg'     => 'bg-violet-100 text-violet-700',
                'is_enabled'  => true,
                'is_beta'     => false,
            ],
            [
                'name'        => 'Gestion du Personnel & Paie',
                'type'        => 'module',
                'description' => 'Gestion RH des enseignants et administratifs, calcul de paie, congés et contrats.',
                'price_tag'   => 'Enterprise',
                'price_color' => 'text-purple-700',
                'icon'        => 'ph-users-three',
                'icon_bg'     => 'bg-teal-100 text-teal-700',
                'is_enabled'  => true,
                'is_beta'     => false,
            ],
            [
                'name'        => 'Assistant IA & Détection du Décrochage',
                'type'        => 'module',
                'description' => 'Algorithmes IA de détection précoce du décrochage scolaire et recommandations pédagogiques.',
                'price_tag'   => 'Enterprise',
                'price_color' => 'text-purple-700',
                'icon'        => 'ph-brain',
                'icon_bg'     => 'bg-purple-100 text-purple-700',
                'is_enabled'  => true,
                'is_beta'     => true,
            ],
            [
                'name'        => 'Supervision Multi-Campus',
                'type'        => 'module',
                'description' => 'Pilotage centralisé de plusieurs établissements depuis un tableau de bord unifié.',
                'price_tag'   => 'Enterprise',
                'price_color' => 'text-purple-700',
                'icon'        => 'ph-buildings',
                'icon_bg'     => 'bg-slate-100 text-slate-700',
                'is_enabled'  => true,
                'is_beta'     => false,
            ],
            [
                'name'        => 'Portail Apprenant & Tuteur Virtuel IA',
                'type'        => 'addon',
                'description' => 'Espace numérique dédié aux élèves avec tuteur IA, exercices adaptatifs et corrections.',
                'price_tag'   => '+2€ / élève',
                'price_color' => 'text-purple-700',
                'icon'        => 'ph-sparkle',
                'icon_bg'     => 'bg-pink-100 text-pink-700',
                'is_enabled'  => true,
                'is_beta'     => true,
            ],
            [
                'name'        => 'Biométrie & Sécurité',
                'type'        => 'addon',
                'description' => 'Contrôle d\'accès et pointage par empreinte digitale pour la présence des élèves.',
                'price_tag'   => '+150€ / site',
                'price_color' => 'text-slate-900',
                'icon'        => 'ph-fingerprint',
                'icon_bg'     => 'bg-blue-100 text-blue-600',
                'is_enabled'  => true,
                'is_beta'     => false,
            ],
        ];

        foreach ($catalogItems as $item) {
            ServiceCatalogItem::firstOrCreate(['name' => $item['name']], $item);
        }

        // 3. Staff Members
        $staff = [
            [
                'staff_code' => 'STF-001',
                'name'       => 'Amadou Diallo',
                'email'      => 'a.diallo@academia.com',
                'role'       => 'Super Administrateur',
                'department' => 'Direction Technique',
                'status'     => 'Active',
                'last_login' => 'Aujourd\'hui, 14:22',
            ],
            [
                'staff_code' => 'STF-002',
                'name'       => 'Aïcha Koné',
                'email'      => 'a.kone@academia.com',
                'role'       => 'Responsable Support',
                'department' => 'Service Client & SLA',
                'status'     => 'Active',
                'last_login' => 'Hier, 18:45',
            ],
            [
                'staff_code' => 'STF-003',
                'name'       => 'Jean-Pierre Sow',
                'email'      => 'jp.sow@academia.com',
                'role'       => 'Ingénieur Sécurité',
                'department' => 'Infrastructures & Cloud',
                'status'     => 'Active',
                'last_login' => '12 Oct, 09:10',
            ],
            [
                'staff_code' => 'STF-004',
                'name'       => 'Fatou Bamba',
                'email'      => 'f.bamba@academia.com',
                'role'       => 'Gestionnaire Billing',
                'department' => 'Finances & Facturation',
                'status'     => 'Inactif',
                'last_login' => '01 Oct, 11:30',
            ],
        ];

        foreach ($staff as $s) {
            StaffMember::firstOrCreate(['staff_code' => $s['staff_code']], $s);
        }

        // 4. Network Nodes
        $nodes = [
            ['name' => 'Cluster Principal (Dakar-1)',   'region' => 'Afrique de l\'Ouest', 'ip_address' => '197.234.21.10', 'status' => 'online',   'latency_ms' => 12, 'load_pct' => 42],
            ['name' => 'Cluster Secondaire (Abidjan)', 'region' => 'Afrique de l\'Ouest', 'ip_address' => '41.207.19.88',  'status' => 'online',   'latency_ms' => 18, 'load_pct' => 58],
            ['name' => 'Nœud Régional (Douala-1)',     'region' => 'Afrique Centrale',    'ip_address' => '195.24.201.5',  'status' => 'degraded', 'latency_ms' => 65, 'load_pct' => 84],
            ['name' => 'Edge Cache (Bamako)',           'region' => 'Afrique de l\'Ouest', 'ip_address' => '160.154.10.4',  'status' => 'online',   'latency_ms' => 24, 'load_pct' => 31],
        ];

        foreach ($nodes as $node) {
            NetworkNode::firstOrCreate(['name' => $node['name']], $node);
        }

        // 5. System Alert Rules
        $rules = [
            ['title' => 'Dépassement Seuil CPU Cluster > 85%', 'severity' => 'critical', 'metric' => 'CPU Load',         'threshold' => '85%',  'is_active' => true],
            ['title' => 'Latence API Supérieure à 500ms',      'severity' => 'warning',  'metric' => 'API Latency',      'threshold' => '500ms','is_active' => true],
            ['title' => 'Erreurs de Paiement Mobile Money',    'severity' => 'warning',  'metric' => 'Payment Failures', 'threshold' => '5%',   'is_active' => true],
            ['title' => 'Espace Disque Insuffisant < 15%',     'severity' => 'critical', 'metric' => 'Disk Space',       'threshold' => '15%',  'is_active' => true],
        ];

        foreach ($rules as $rule) {
            SystemAlertRule::firstOrCreate(['title' => $rule['title']], $rule);
        }

        // 6. Backup Logs
        $backups = [
            ['filename' => 'snapshot-2023-10-27-0300.sql.gz',      'size' => '2.4 GB', 'type' => 'Automatique', 'status' => 'success', 'storage_location' => 'AWS S3 (eu-west-3)'],
            ['filename' => 'snapshot-2023-10-26-0300.sql.gz',      'size' => '2.3 GB', 'type' => 'Automatique', 'status' => 'success', 'storage_location' => 'AWS S3 (eu-west-3)'],
            ['filename' => 'snapshot-manual-pre-update.sql.gz',    'size' => '2.4 GB', 'type' => 'Manuel',      'status' => 'success', 'storage_location' => 'Wasabi Cloud (eu-central)'],
        ];

        foreach ($backups as $b) {
            BackupLog::firstOrCreate(['filename' => $b['filename']], $b);
        }
    }
}
