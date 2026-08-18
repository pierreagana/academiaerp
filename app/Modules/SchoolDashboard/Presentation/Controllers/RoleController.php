<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Academic\Domain\Models\Permission;
use App\Modules\Academic\Domain\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private const ACTIONS = ['show', 'create', 'edit', 'update', 'delete'];

    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()->role === 'adminschool', 403, "Seul le directeur de l'établissement peut gérer les rôles et permissions.");
    }

    /**
     * Permissions for modules the school's package (+ approved extensions)
     * doesn't actually include would be dead checkboxes — School::canAccess()
     * blocks them regardless of what a role says. Only offer what could
     * really work, so the matrix never lies about what a role grants.
     */
    private function accessiblePermissions()
    {
        $all = Permission::orderBy('group')->orderBy('name')->get();

        $accessibleModules = auth()->user()->school?->accessibleModuleNames();
        if ($accessibleModules === null) {
            return $all;
        }

        return $all->filter(function ($permission) use ($accessibleModules) {
            $moduleName = User::SLUG_MODULE_MAP[$permission->slug] ?? null;
            return $moduleName === null || in_array($moduleName, $accessibleModules, true);
        })->values();
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $roles = Role::where('school_id', auth()->user()->school_id)
            ->withCount('users')
            ->orderBy('name')
            ->get();

        $selectedRole = null;
        if ($roles->isNotEmpty()) {
            $selectedRole = $roles->firstWhere('id', (int) $request->get('role')) ?? $roles->first();
            $selectedRole->load('permissions');
        }

        $permissionsGrouped = $this->accessiblePermissions()->groupBy('group');

        $matrix = [];
        if ($selectedRole) {
            foreach ($selectedRole->permissions as $permission) {
                $matrix[$permission->id] = [
                    'show' => (bool) $permission->pivot->can_show,
                    'create' => (bool) $permission->pivot->can_create,
                    'edit' => (bool) $permission->pivot->can_edit,
                    'update' => (bool) $permission->pivot->can_update,
                    'delete' => (bool) $permission->pivot->can_delete,
                ];
            }
        }

        return view('SchoolDashboard::roles.index', compact('roles', 'selectedRole', 'permissionsGrouped', 'matrix'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,NULL,id,school_id,' . auth()->user()->school_id],
            'is_branch_director' => ['nullable', 'boolean'],
        ]);

        $role = Role::create([
            'school_id' => auth()->user()->school_id,
            'name' => $data['name'],
            'is_branch_director' => $data['is_branch_director'] ?? false,
        ]);

        return redirect()->route('school.roles', ['role' => $role->id])->with('success', "Rôle « {$role->name} » créé. Configurez ses permissions ci-dessous.");
    }

    public function rename(Request $request, $id)
    {
        $this->ensureAdmin();

        $role = Role::where('school_id', auth()->user()->school_id)->findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id . ',id,school_id,' . auth()->user()->school_id],
            'is_branch_director' => ['nullable', 'boolean'],
        ]);

        $role->update(['name' => $data['name'], 'is_branch_director' => $data['is_branch_director'] ?? false]);

        return redirect()->route('school.roles', ['role' => $role->id])->with('success', 'Intitulé du rôle mis à jour.');
    }

    public function updateMatrix(Request $request, $id)
    {
        $this->ensureAdmin();

        $role = Role::where('school_id', auth()->user()->school_id)->findOrFail($id);

        $request->validate([
            'permissions' => ['nullable', 'array'],
        ]);

        $input = $request->input('permissions', []);
        $allowedPermissionIds = $this->accessiblePermissions()->pluck('id');

        $syncData = [];
        foreach ($allowedPermissionIds as $permissionId) {
            $actions = $input[$permissionId] ?? [];
            $pivot = [];
            $anyChecked = false;
            foreach (self::ACTIONS as $action) {
                $checked = isset($actions[$action]) && $actions[$action] == 1;
                $pivot["can_{$action}"] = $checked;
                $anyChecked = $anyChecked || $checked;
            }
            if ($anyChecked) {
                $syncData[$permissionId] = $pivot;
            }
        }

        $role->permissions()->sync($syncData);

        return redirect()->route('school.roles', ['role' => $role->id])->with('success', 'Autorisations mises à jour.');
    }

    public function destroy($id)
    {
        $this->ensureAdmin();

        $role = Role::where('school_id', auth()->user()->school_id)->withCount('users')->findOrFail($id);

        if ($role->users_count > 0) {
            return back()->with('error', 'Impossible de supprimer ce rôle : des comptes y sont encore rattachés.');
        }

        $role->delete();

        return redirect()->route('school.roles')->with('success', 'Rôle supprimé avec succès.');
    }
}
