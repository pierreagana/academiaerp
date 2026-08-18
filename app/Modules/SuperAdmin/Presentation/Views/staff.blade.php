@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[28px] font-extrabold text-[#111827]">Gestion du Personnel & Membres de l'Équipe</h2>
            <p class="text-[15px] text-slate-500 mt-1">Gérez les membres de l'équipe d'administration centrale, leurs rôles et leurs accès (Base SQL).</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 shrink-0 mt-2 md:mt-0">
            <button type="button" onclick="openAddStaffModal()" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-blue-900 transition shadow-sm cursor-pointer">
                <i class="ph ph-user-plus text-lg font-bold"></i> + Ajouter un Membre
            </button>
        </div>
    </div>

    <!-- Toast Alerts -->
    @if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-xl flex items-center justify-between text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2.5">
            <i class="ph ph-check-circle text-emerald-600 text-xl font-bold"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-lg font-bold">✕</button>
    </div>
    @endif

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-700 font-bold text-xl">
                <i class="ph ph-users-three"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">MEMBRES ACTIFS (BD SQL)</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $kpis['total_active'] ?? count($staffMembers) }} Membres</h3>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-700 font-bold text-xl">
                <i class="ph ph-shield-check"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">RÔLES DÉFINIS</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $kpis['total_roles'] ?? 4 }} Niveaux d'Accès</h3>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-700 font-bold text-xl">
                <i class="ph ph-lock-key"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">SÉCURITÉ 2FA</p>
                <h3 class="text-2xl font-extrabold text-emerald-600">100% Requis</h3>
            </div>
        </div>
    </div>

    <!-- Staff Table -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm mb-8">
        <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 bg-[#FCFDFE]">
            <h3 class="text-xl font-extrabold text-slate-900">Membres de l'Équipe (BD SQL)</h3>
            
            <form action="{{ route('superadmin.staff') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <div class="relative w-full sm:w-64">
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Rechercher nom, email, code..." class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl pl-9 pr-4 py-2 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>

                <select name="role" onchange="this.form.submit()" class="bg-white border border-slate-200 text-slate-700 px-3 py-2 rounded-xl text-xs font-bold hover:bg-slate-50 transition outline-none cursor-pointer">
                    <option value="all" {{ ($roleFilter ?? 'all') === 'all' ? 'selected' : '' }}>Tous les rôles</option>
                    @foreach($availableRoles as $r)
                        <option value="{{ $r }}" {{ ($roleFilter ?? '') === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>

                <button type="submit" class="bg-[#031C5B] text-white px-3.5 py-2 rounded-xl text-xs font-bold hover:bg-blue-900 transition">Filtrer</button>
                @if(!empty($search) || ($roleFilter ?? 'all') !== 'all')
                    <a href="{{ route('superadmin.staff') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition">Réinitialiser</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                <thead>
                    <tr class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest bg-slate-50 border-b border-slate-200">
                        <th class="py-4 px-6">MEMBRE DE L'ÉQUIPE</th>
                        <th class="py-4 px-4">RÔLE SÉCURITÉ</th>
                        <th class="py-4 px-4">DÉPARTEMENT</th>
                        <th class="py-4 px-4">DERNIÈRE CONNEXION</th>
                        <th class="py-4 px-4 text-center">STATUT</th>
                        <th class="py-4 px-6 text-right">ACTIONS DISPONIBLES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($staffMembers as $member)
                        @php
                            $mId = $member->id ?? $member['id'] ?? 1;
                            $mName = $member->name ?? $member['name'] ?? 'Membre Staff';
                            $mEmail = $member->email ?? $member['email'] ?? 'staff@academia.sn';
                            $mRole = $member->role ?? $member['role'] ?? 'Super Administrateur';
                            $mDept = $member->department ?? $member['department'] ?? 'Direction';
                            $mCode = $member->staff_code ?? $member['staff_code'] ?? 'STF-001';
                            $mStatus = $member->status ?? $member['status'] ?? 'Active';
                            $mLastLogin = $member->last_login ?? $member['last_login'] ?? 'Récemment';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-800 font-bold flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($mName, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm">{{ $mName }}</p>
                                        <p class="text-slate-400 text-[11px] font-mono">{{ $mEmail }} • <span class="text-slate-500">{{ $mCode }}</span></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">{{ $mRole }}</span>
                            </td>
                            <td class="py-4 px-4 text-slate-600">
                                {{ $mDept }}
                            </td>
                            <td class="py-4 px-4 text-slate-500 font-mono text-[11px]">
                                {{ $mLastLogin }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($mStatus === 'Active')
                                    <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase border border-emerald-200">Actif</span>
                                @else
                                    <span class="bg-slate-100 text-slate-500 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase border border-slate-200">Inactif</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit Member -->
                                    <button type="button" onclick="openEditStaffModal({{ $mId }}, '{{ addslashes($mName) }}', '{{ addslashes($mEmail) }}', '{{ addslashes($mRole) }}', '{{ addslashes($mDept) }}')" title="Éditer les informations" class="p-1.5 rounded-lg bg-blue-50 text-[#031C5B] hover:bg-[#031C5B] hover:text-white transition font-bold cursor-pointer">
                                        <i class="ph ph-pencil-simple text-base"></i>
                                    </button>

                                    <!-- Toggle Active / Inactive Status -->
                                    <form action="{{ route('superadmin.staff.toggle-status', $mId) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" title="{{ $mStatus === 'Active' ? 'Désactiver le compte' : 'Activer le compte' }}" class="p-1.5 rounded-lg {{ $mStatus === 'Active' ? 'bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white' }} transition font-bold cursor-pointer">
                                            <i class="ph {{ $mStatus === 'Active' ? 'ph-user-minus' : 'ph-user-check' }} text-base"></i>
                                        </button>
                                    </form>

                                    <!-- Reset Password -->
                                    <form action="{{ route('superadmin.staff.reset-password', $mId) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" title="Réinitialiser le Mot de passe" class="p-1.5 rounded-lg bg-purple-50 text-[#7C3AED] hover:bg-[#7C3AED] hover:text-white transition font-bold cursor-pointer">
                                            <i class="ph ph-key text-base"></i>
                                        </button>
                                    </form>

                                    <!-- Delete Member -->
                                    <form action="{{ route('superadmin.staff.destroy', $mId) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitvement le membre {{ addslashes($mName) }} ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Supprimer du personnel" class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition font-bold cursor-pointer">
                                            <i class="ph ph-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-sm">
                                Aucun membre du personnel correspondant aux critères.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($staffMembers, 'links'))
        <div class="px-6 py-4 bg-[#FCFDFE] border-t border-slate-200">
            {{ $staffMembers->links() }}
        </div>
        @endif
    </div>

    <!-- Modal : Ajouter un Membre -->
    <div id="addStaffModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-user-plus text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Ajouter un Membre de l'Équipe</h3>
                        <p class="text-xs text-blue-200 font-medium">Attribuez un rôle et enregistrez en BD SQL</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddStaffModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('superadmin.staff.store') }}" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nom complet *</label>
                    <input type="text" name="name" required placeholder="Ex: Jean-Luc Sow" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Adresse Email *</label>
                    <input type="email" name="email" required placeholder="jeanluc@superadmin.sn" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Rôle Système *</label>
                        <select name="role" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition cursor-pointer">
                            @foreach($availableRoles as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Département</label>
                        <input type="text" name="department" placeholder="Ex: Support Technique" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeAddStaffModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold hover:bg-slate-50 transition cursor-pointer">
                        Annuler
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#031C5B] text-white font-bold hover:bg-blue-900 transition shadow-sm flex items-center gap-2 cursor-pointer">
                        <i class="ph ph-check text-sm font-bold"></i> Enregistrer en BD SQL
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal : Éditer un Membre -->
    <div id="editStaffModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="px-6 py-5 bg-[#7C3AED] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-pencil-simple text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Éditer la Fiche du Membre</h3>
                        <p class="text-xs text-purple-200 font-medium">Mise à jour des informations et du rôle</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditStaffModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form id="editStaffForm" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nom complet *</label>
                    <input type="text" name="name" id="editStaffName" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-purple-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Adresse Email *</label>
                    <input type="email" name="email" id="editStaffEmail" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-purple-500 focus:bg-white transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Rôle Système *</label>
                        <select name="role" id="editStaffRole" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:border-purple-500 focus:bg-white transition cursor-pointer">
                            @foreach($availableRoles as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Département</label>
                        <input type="text" name="department" id="editStaffDept" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-900 focus:outline-none focus:border-purple-500 focus:bg-white transition">
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeEditStaffModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold hover:bg-slate-50 transition cursor-pointer">
                        Annuler
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#7C3AED] text-white font-bold hover:bg-purple-800 transition shadow-sm flex items-center gap-2 cursor-pointer">
                        <i class="ph ph-check text-sm font-bold"></i> Enregistrer les Modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddStaffModal() {
            const modal = document.getElementById('addStaffModal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeAddStaffModal() {
            const modal = document.getElementById('addStaffModal');
            if (modal) modal.classList.add('hidden');
        }
        function openEditStaffModal(id, name, email, role, dept) {
            document.getElementById('editStaffForm').action = "/superadmin/staff/" + id;
            document.getElementById('editStaffName').value = name;
            document.getElementById('editStaffEmail').value = email;
            document.getElementById('editStaffRole').value = role;
            document.getElementById('editStaffDept').value = dept;
            const modal = document.getElementById('editStaffModal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeEditStaffModal() {
            const modal = document.getElementById('editStaffModal');
            if (modal) modal.classList.add('hidden');
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAddStaffModal();
                closeEditStaffModal();
            }
        });
    </script>
@endsection
