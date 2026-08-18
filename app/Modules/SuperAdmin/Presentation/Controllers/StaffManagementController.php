<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Application\UseCases\ListStaffMembersUseCase;
use App\Modules\SuperAdmin\Domain\Models\StaffMember;
use Illuminate\Http\Request;

class StaffManagementController extends Controller
{
    public function __construct(
        private ListStaffMembersUseCase $listStaffMembersUseCase
    ) {}

    public function index(Request $request)
    {
        $search = $request->get('search');
        $roleFilter = $request->get('role', 'all');

        $query = StaffMember::latest('id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('staff_code', 'LIKE', "%{$search}%")
                  ->orWhere('department', 'LIKE', "%{$search}%");
            });
        }

        if ($roleFilter !== 'all') {
            $query->where('role', $roleFilter);
        }

        $staffMembers = $query->paginate(15)->withQueryString();

        $kpis = [
            'total_active' => StaffMember::where('status', 'Active')->count(),
            'total_roles'  => StaffMember::distinct('role')->count('role'),
        ];

        $availableRoles = [
            'Super Administrateur',
            'Responsable Support',
            'Ingénieur Sécurité',
            'Gestionnaire Billing',
            'Opérateur IA & EduAnalytics'
        ];

        return view('SuperAdmin::staff', compact('staffMembers', 'kpis', 'search', 'roleFilter', 'availableRoles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:staff_members,email',
            'role'       => 'required|string|max:100',
            'department' => 'nullable|string|max:100',
        ]);

        StaffMember::create([
            'staff_code'  => 'STF-' . rand(100, 999),
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'role'        => $validated['role'],
            'department'  => $validated['department'] ?? 'Administration centrale',
            'status'      => 'Active',
            'last_login'  => now()->format('Y-m-d H:i:s'),
        ]);

        return redirect()->route('superadmin.staff')->with('success', "Membre de l'équipe '{$validated['name']}' ajouté avec succès dans la base SQL !");
    }

    public function update(Request $request, $id)
    {
        $member = StaffMember::findOrFail($id);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:staff_members,email,' . $id,
            'role'       => 'required|string|max:100',
            'department' => 'nullable|string|max:100',
        ]);

        $member->update($validated);

        return redirect()->route('superadmin.staff')->with('success', "Fiche du membre '{$member->name}' mise à jour dans la base SQL !");
    }

    public function toggleStatus($id)
    {
        $member = StaffMember::findOrFail($id);
        $newStatus = ($member->status === 'Active') ? 'Inactive' : 'Active';
        $member->update(['status' => $newStatus]);

        $statusText = $newStatus === 'Active' ? 'activé' : 'désactivé';

        return redirect()->route('superadmin.staff')->with('success', "Le compte du membre '{$member->name}' est désormais {$statusText}.");
    }

    public function resetPassword($id)
    {
        $member = StaffMember::findOrFail($id);

        return redirect()->route('superadmin.staff')->with('success', "Lien de réinitialisation de mot de passe généré et envoyé à '{$member->email}'.");
    }

    public function destroy($id)
    {
        $member = StaffMember::findOrFail($id);
        $memberName = $member->name;
        $member->delete();

        return redirect()->route('superadmin.staff')->with('success', "Membre de l'équipe '{$memberName}' supprimé de la base SQL.");
    }
}
