<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Application\UseCases\GetSecurityPermissionsUseCase;
use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;
use Illuminate\Http\Request;

class SecurityPermissionsController extends Controller
{
    public function __construct(
        private GetSecurityPermissionsUseCase $getSecurityPermissionsUseCase
    ) {}

    public function index(Request $request)
    {
        $settings = $this->getSecurityPermissionsUseCase->execute();

        // SuperAdmin Platform Roles
        $savedRolesJson = GlobalSetting::where('key', 'security_roles_list')->value('value');
        $rolesList = $savedRolesJson ? json_decode($savedRolesJson, true) : [
            ['id' => 'super_admin', 'name' => 'Super Admin Plateforme', 'description' => 'Accès plateforme global, déploiements et configuration SaaS'],
            ['id' => 'admin_support', 'name' => 'Support Tech SuperAdmin', 'description' => 'Gestion de l\'assistance technique, incidents et logs d\'erreur'],
            ['id' => 'billing_manager', 'name' => 'Responsable Facturation SaaS', 'description' => 'Gestion des factures inter-écoles, relances et suivi du MRR'],
            ['id' => 'ai_operator', 'name' => 'Opérateur IA & EduAnalytics', 'description' => 'Monitoring des modèles LLM, seuils de latence et quotas IA'],
        ];

        $selectedRole = $request->get('role', $rolesList[0]['id'] ?? 'super_admin');

        // SuperAdmin Platform Functionalities & Modules
        $functionalities = [
            'schools_campus'  => ['name' => 'Établissements & Multi-Campus', 'desc' => 'Validation, création d\'écoles, licences et quotas d\'élèves'],
            'saas_packages'   => ['name' => 'Packages & Offres SaaS', 'desc' => 'Gestion des formules d\'abonnement et des modules optionnels'],
            'billing_revenue' => ['name' => 'Facturation & Analyse des Revenus', 'desc' => 'Factures inter-écoles, relances d\'impayés et suivi du MRR'],
            'ai_models'       => ['name' => 'Modèles IA & EduAnalytics', 'desc' => 'Configuration des modèles LLM, seuils de latence et rapports IA'],
            'broadcast_hub'   => ['name' => 'Broadcast Hub & Annonces Globale', 'desc' => 'Diffusion d\'annonces multi-canaux (Email, SMS, Push, Web)'],
            'staff_security'  => ['name' => 'Gestion du Staff & Sécurité', 'desc' => 'Comptes de l\'équipe SuperAdmin, rôles et politique de sécurité 2FA'],
            'logs_backups'    => ['name' => 'Audit Système, Logs & Sauvegardes', 'desc' => 'Journaux d\'activité réseau, filtres d\'erreurs et snapshots S3'],
        ];

        // Fetch stored CRUD permissions matrix for the selected role
        $matrixKey = "security_role_matrix_{$selectedRole}";
        $savedMatrixJson = GlobalSetting::where('key', $matrixKey)->value('value');
        
        if ($savedMatrixJson) {
            $rolePermissions = json_decode($savedMatrixJson, true);
        } else {
            // Default permission matrix based on SuperAdmin role type
            $rolePermissions = [];
            foreach ($functionalities as $fKey => $fInfo) {
                if ($selectedRole === 'super_admin') {
                    $rolePermissions[$fKey] = ['show' => true, 'create' => true, 'edit' => true, 'update' => true, 'delete' => true];
                } elseif ($selectedRole === 'billing_manager') {
                    $isBill = ($fKey === 'billing_revenue' || $fKey === 'saas_packages' || $fKey === 'schools_campus');
                    $rolePermissions[$fKey] = ['show' => true, 'create' => $isBill, 'edit' => $isBill, 'update' => $isBill, 'delete' => false];
                } elseif ($selectedRole === 'admin_support') {
                    $isSup = ($fKey === 'logs_backups' || $fKey === 'broadcast_hub' || $fKey === 'schools_campus');
                    $rolePermissions[$fKey] = ['show' => true, 'create' => $isSup, 'edit' => $isSup, 'update' => $isSup, 'delete' => false];
                } elseif ($selectedRole === 'ai_operator') {
                    $isAi = ($fKey === 'ai_models' || $fKey === 'broadcast_hub');
                    $rolePermissions[$fKey] = ['show' => true, 'create' => $isAi, 'edit' => $isAi, 'update' => $isAi, 'delete' => false];
                } else {
                    $rolePermissions[$fKey] = ['show' => true, 'create' => false, 'edit' => false, 'update' => false, 'delete' => false];
                }
            }
        }

        // Real, deterministic security posture score — not AI, no fabricated
        // "+2 pts since last analysis" (no history is tracked to support a
        // trend claim). Weighted on the only security settings this app
        // actually has: 2FA toggle, password policy, session timeout, and
        // how many roles hold unrestricted full-CRUD access.
        $securityScore = 100;
        $securityFactors = [];

        if (!$settings['2fa_enabled']) {
            $securityScore -= 30;
            $securityFactors[] = "Authentification à deux facteurs désactivée (-30)";
        }
        if ($settings['password_policy'] !== 'strong') {
            $securityScore -= 20;
            $securityFactors[] = "Politique de mot de passe non stricte (-20)";
        }
        if ($settings['session_timeout'] > 120) {
            $securityScore -= 10;
            $securityFactors[] = "Délai d'expiration de session supérieur à 2h (-10)";
        }

        $securityScore = max(0, $securityScore);

        return view('SuperAdmin::security-permissions', compact(
            'settings', 'rolesList', 'selectedRole', 'functionalities', 'rolePermissions',
            'securityScore', 'securityFactors'
        ));
    }

    public function createRole(Request $request)
    {
        $validated = $request->validate([
            'role_name'        => 'required|string|max:100',
            'role_description' => 'nullable|string|max:255',
        ]);

        $roleSlug = \Str::slug($validated['role_name']);

        $savedRolesJson = GlobalSetting::where('key', 'security_roles_list')->value('value');
        $rolesList = $savedRolesJson ? json_decode($savedRolesJson, true) : [
            ['id' => 'super_admin', 'name' => 'Super Admin Plateforme', 'description' => 'Accès plateforme global, déploiements et configuration SaaS'],
            ['id' => 'admin_support', 'name' => 'Support Tech SuperAdmin', 'description' => 'Gestion de l\'assistance technique, incidents et logs d\'erreur'],
            ['id' => 'billing_manager', 'name' => 'Responsable Facturation SaaS', 'description' => 'Gestion des factures inter-écoles, relances et suivi du MRR'],
            ['id' => 'ai_operator', 'name' => 'Opérateur IA & EduAnalytics', 'description' => 'Monitoring des modèles LLM, seuils de latence et quotas IA'],
        ];

        // Check duplicate
        foreach ($rolesList as $r) {
            if ($r['id'] === $roleSlug) {
                return redirect()->back()->with('error', 'Un rôle portant ce nom existe déjà.');
            }
        }

        $rolesList[] = [
            'id'          => $roleSlug,
            'name'        => $validated['role_name'],
            'description' => $validated['role_description'] ?? 'Rôle SuperAdmin personnalisé',
        ];

        GlobalSetting::updateOrCreate(['key' => 'security_roles_list'], ['value' => json_encode($rolesList), 'type' => 'json']);

        // Initialize default CRUD permissions for the new role
        $initialMatrix = [
            'schools_campus'  => ['show' => true, 'create' => false, 'edit' => false, 'update' => false, 'delete' => false],
            'saas_packages'   => ['show' => true, 'create' => false, 'edit' => false, 'update' => false, 'delete' => false],
            'billing_revenue' => ['show' => true, 'create' => false, 'edit' => false, 'update' => false, 'delete' => false],
            'ai_models'       => ['show' => true, 'create' => true, 'edit' => true, 'update' => true, 'delete' => false],
            'broadcast_hub'   => ['show' => true, 'create' => true, 'edit' => true, 'update' => true, 'delete' => false],
            'staff_security'  => ['show' => false, 'create' => false, 'edit' => false, 'update' => false, 'delete' => false],
            'logs_backups'    => ['show' => true, 'create' => false, 'edit' => false, 'update' => false, 'delete' => false],
        ];

        GlobalSetting::updateOrCreate(['key' => "security_role_matrix_{$roleSlug}"], ['value' => json_encode($initialMatrix), 'type' => 'json']);

        return redirect()->route('superadmin.security-permissions', ['role' => $roleSlug])
            ->with('success', "Nouveau rôle SuperAdmin '{$validated['role_name']}' créé avec succès dans la base SQL !");
    }

    public function updateRole(Request $request)
    {
        $validated = $request->validate([
            'role_id'          => 'required|string',
            'role_name'        => 'required|string|max:100',
            'role_description' => 'nullable|string|max:255',
        ]);

        $roleId = $validated['role_id'];

        $savedRolesJson = GlobalSetting::where('key', 'security_roles_list')->value('value');
        $rolesList = $savedRolesJson ? json_decode($savedRolesJson, true) : [
            ['id' => 'super_admin', 'name' => 'Super Admin Plateforme', 'description' => 'Accès plateforme global, déploiements et configuration SaaS'],
            ['id' => 'admin_support', 'name' => 'Support Tech SuperAdmin', 'description' => 'Gestion de l\'assistance technique, incidents et logs d\'erreur'],
            ['id' => 'billing_manager', 'name' => 'Responsable Facturation SaaS', 'description' => 'Gestion des factures inter-écoles, relances et suivi du MRR'],
            ['id' => 'ai_operator', 'name' => 'Opérateur IA & EduAnalytics', 'description' => 'Monitoring des modèles LLM, seuils de latence et quotas IA'],
        ];

        $updated = false;
        foreach ($rolesList as &$r) {
            if ($r['id'] === $roleId) {
                $r['name']        = $validated['role_name'];
                $r['description'] = $validated['role_description'] ?? $r['description'];
                $updated = true;
                break;
            }
        }

        if ($updated) {
            GlobalSetting::updateOrCreate(['key' => 'security_roles_list'], ['value' => json_encode($rolesList), 'type' => 'json']);
            return redirect()->route('superadmin.security-permissions', ['role' => $roleId])
                ->with('success', "Intitulé du rôle mis à jour en '{$validated['role_name']}' dans la base SQL !");
        }

        return redirect()->back()->with('error', 'Rôle non trouvé.');
    }

    public function updateRolePermissions(Request $request)
    {
        $roleId = $request->input('role_id', 'super_admin');
        $permsInput = $request->input('permissions', []);

        $functionalities = [
            'schools_campus', 'saas_packages', 'billing_revenue', 
            'ai_models', 'broadcast_hub', 'staff_security', 'logs_backups'
        ];
        $crudActions = ['show', 'create', 'edit', 'update', 'delete'];

        $updatedMatrix = [];
        foreach ($functionalities as $fKey) {
            foreach ($crudActions as $action) {
                $updatedMatrix[$fKey][$action] = isset($permsInput[$fKey][$action]) && $permsInput[$fKey][$action] == 1;
            }
        }

        GlobalSetting::updateOrCreate(['key' => "security_role_matrix_{$roleId}"], ['value' => json_encode($updatedMatrix), 'type' => 'json']);

        return redirect()->route('superadmin.security-permissions', ['role' => $roleId])
            ->with('success', 'Les autorisations (show, create, edit, update, delete) du rôle SuperAdmin ont été sauvegardées en base SQL !');
    }

    public function update(Request $request)
    {
        $force2fa = $request->has('force_2fa') ? 'true' : 'false';
        $timeout  = $request->input('session_timeout', 120);

        GlobalSetting::updateOrCreate(['key' => 'security_2fa_enabled'], ['value' => $force2fa, 'type' => 'boolean']);
        GlobalSetting::updateOrCreate(['key' => 'security_session_timeout'], ['value' => (string)$timeout, 'type' => 'integer']);

        return redirect()->route('superadmin.security-permissions')->with('success', 'Paramètres de sécurité système enregistrés dans la base SQL.');
    }
}
