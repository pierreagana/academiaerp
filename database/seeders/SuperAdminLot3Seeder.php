<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\SuperAdmin\Domain\Models\SupportTicket;
use App\Modules\SuperAdmin\Domain\Models\SystemLog;
use App\Modules\SuperAdmin\Domain\Models\BroadcastMessage;
use Carbon\Carbon;

class SuperAdminLot3Seeder extends Seeder
{
    public function run(): void
    {
        // Support Tickets
        SupportTicket::create([
            'ticket_id' => 'TK-8902',
            'subject' => 'Problème synchronisation notes',
            'description' => 'Les notes saisies ne s\'affichent pas dans le bulletin.',
            'school_name' => 'Lycée Molière',
            'priority' => 'high',
            'status' => 'open',
            'category' => 'Technique',
        ]);

        SupportTicket::create([
            'ticket_id' => 'TK-8903',
            'subject' => 'Demande d\'ajout module cantine',
            'description' => 'Nous souhaitons activer le module cantine pour le prochain trimestre.',
            'school_name' => 'Collège Saint-Exupéry',
            'priority' => 'normal',
            'status' => 'in_progress',
            'category' => 'Commercial',
        ]);

        SupportTicket::create([
            'ticket_id' => 'TK-8904',
            'subject' => 'Erreur facturation Octobre',
            'description' => 'La facture générée est incorrecte.',
            'school_name' => 'Institut Sainte-Marie',
            'priority' => 'critical',
            'status' => 'resolved',
            'category' => 'Facturation',
        ]);

        SupportTicket::create([
            'ticket_id' => 'TK-8905',
            'subject' => 'Configuration des SMS',
            'description' => 'Comment paramétrer les envois de SMS aux parents ?',
            'school_name' => 'École Primaire Les Lilas',
            'priority' => 'low',
            'status' => 'closed',
            'category' => 'Assistance',
        ]);

        // System Logs
        SystemLog::create([
            'level' => 'error',
            'message' => 'Failed to connect to SMS Gateway',
            'source' => 'communications_module',
            'ip_address' => '192.168.1.10',
            'created_at' => Carbon::now()->subMinutes(5)
        ]);

        SystemLog::create([
            'level' => 'info',
            'message' => 'New school registered successfully',
            'source' => 'onboarding_module',
            'ip_address' => '10.0.0.5',
            'created_at' => Carbon::now()->subHours(2)
        ]);

        SystemLog::create([
            'level' => 'warning',
            'message' => 'High CPU usage detected on database server',
            'source' => 'infrastructure',
            'ip_address' => '127.0.0.1',
            'created_at' => Carbon::now()->subDays(1)
        ]);

        // Broadcast Messages
        BroadcastMessage::create([
            'title' => 'Mise à jour v3.5 (Maintenance)',
            'message' => 'Le système sera indisponible ce samedi de 02:00 à 04:00 GMT pour la mise à jour vers la version 3.5.',
            'type' => 'warning',
            'target_roles' => ['school_admin', 'teacher'],
            'is_active' => true,
            'expires_at' => Carbon::now()->addDays(2)
        ]);

        BroadcastMessage::create([
            'title' => 'Nouveau Module Tuteur IA',
            'message' => 'Découvrez notre nouveau module d\'intelligence artificielle pour accompagner vos élèves !',
            'type' => 'info',
            'target_roles' => ['school_admin'],
            'is_active' => true,
            'expires_at' => Carbon::now()->addDays(10)
        ]);
        
        BroadcastMessage::create([
            'title' => 'Incident Résolu : Passerelle de paiement',
            'message' => 'Les paiements par carte bancaire sont de nouveau opérationnels.',
            'type' => 'success',
            'target_roles' => ['school_admin', 'accountant'],
            'is_active' => false,
            'expires_at' => Carbon::now()->subDays(1)
        ]);
    }
}
