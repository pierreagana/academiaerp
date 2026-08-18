@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[28px] font-extrabold text-[#111827]">Sécurité & Management des Rôles</h2>
            <p class="text-[15px] text-slate-500 mt-1">Créez ou modifiez des rôles et définissez les autorisations granulaires (Consulter, Créer, Éditer, Mettre à jour, Supprimer) par fonctionnalité (Base SQL).</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 shrink-0 mt-2 md:mt-0">
            <button type="button" onclick="openCreateRoleModal()" class="flex items-center gap-2 bg-[#7C3AED] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-purple-700 transition shadow-sm cursor-pointer">
                <i class="ph ph-shield-plus text-lg font-bold"></i> + Créer un Rôle
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
    @if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-5 py-3.5 rounded-xl flex items-center justify-between text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2.5">
            <i class="ph ph-warning-circle text-red-600 text-xl font-bold"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-800 text-lg font-bold">✕</button>
    </div>
    @endif

    <!-- Top KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <!-- KPI 1 -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 relative overflow-hidden flex flex-col">
            <div class="flex justify-between items-start mb-2 relative z-10">
                <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">SCORE DE SÉCURITÉ IA</h3>
                <i class="ph ph-shield-check text-2xl text-[#7C3AED] font-bold"></i>
            </div>
            <div class="flex items-baseline gap-2 mb-3 mt-1">
                <h2 class="text-[36px] font-extrabold text-slate-900 leading-none flex items-baseline gap-1">94<span class="text-[18px] text-slate-400 font-medium">/100</span></h2>
            </div>
            <div class="flex items-center gap-2 mt-auto">
                <span class="inline-flex items-center gap-1 bg-[#ECFDF5] text-[#059669] text-[11px] font-bold px-2 py-0.5 rounded-md">
                    <i class="ph ph-trend-up"></i> +2 pts
                </span>
                <span class="text-[12px] font-medium text-slate-500">depuis la dernière analyse</span>
            </div>
        </div>

        <!-- KPI 2 -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 relative overflow-hidden flex flex-col">
            <div class="flex justify-between items-start mb-2 relative z-10">
                <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">ADOPTION 2FA</h3>
                <i class="ph ph-lock-key text-2xl text-[#031C5B] font-bold"></i>
            </div>
            <div class="flex items-baseline gap-2 mb-4 mt-1">
                <h2 class="text-[36px] font-extrabold text-slate-900 leading-none">87%</h2>
            </div>
            <div class="mt-auto">
                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden mb-2">
                    <div class="h-full bg-[#031C5B] rounded-full" style="width: 87%"></div>
                </div>
                <p class="text-[12px] font-medium text-slate-500">13% des comptes sans A2F</p>
            </div>
        </div>

        <!-- KPI 3 -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 relative overflow-hidden flex flex-col">
            <div class="flex justify-between items-start mb-2 relative z-10">
                <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">RÔLES SUPERADMIN (BD SQL)</h3>
                <i class="ph ph-users-three text-2xl text-purple-600 font-bold"></i>
            </div>
            <div class="flex items-baseline gap-2 mb-4 mt-1">
                <h2 class="text-[36px] font-extrabold text-slate-900 leading-none">{{ count($rolesList) }}</h2>
                <span class="text-xs font-bold text-slate-500">rôles configurés</span>
            </div>
            <div class="mt-auto flex items-center gap-2">
                <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2.5 py-1 rounded-full border border-purple-200">
                    Granularité CRUD Active
                </span>
            </div>
        </div>

    </div>

    <!-- Main Section: Configuration & Matrix -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        
        <!-- Left: Configuration des Rôles & Matrice CRUD (2 cols) -->
        <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            
            <div class="p-6 border-b border-slate-200 flex items-center justify-between bg-[#FCFDFE]">
                <div class="flex items-center gap-3">
                    <i class="ph ph-shield-check text-2xl text-[#031C5B] font-bold"></i>
                    <div>
                        <h3 class="text-[20px] font-extrabold text-[#111827]">Matrice d'Autorisations par Fonctionnalité</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Cochez les droits (show, create, edit, update, delete) accordés au rôle</p>
                    </div>
                </div>
                <button type="button" onclick="openCreateRoleModal()" class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-[#7C3AED] bg-purple-50 hover:bg-purple-100 rounded-xl transition border border-purple-200 cursor-pointer">
                    <i class="ph ph-plus font-bold"></i> Nouveau Rôle
                </button>
            </div>

            <!-- Role Tabs -->
            <div class="px-6 pt-4 border-b border-slate-200 bg-slate-50/50 overflow-x-auto">
                <div class="flex items-center gap-2 text-xs font-bold whitespace-nowrap">
                    @foreach($rolesList as $r)
                        @php $isSel = ($selectedRole === $r['id']); @endphp
                        <a href="{{ route('superadmin.security-permissions', ['role' => $r['id']]) }}" class="px-4 py-2.5 rounded-t-xl transition border-t-2 border-x {{ $isSel ? 'bg-white border-t-[#031C5B] border-x-slate-200 text-[#031C5B] shadow-2xs font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ $r['name'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Matrix Form -->
            <form action="{{ route('superadmin.security-permissions.role-permissions') }}" method="POST" class="p-6 flex-1 flex flex-col justify-between">
                @csrf
                <input type="hidden" name="role_id" value="{{ $selectedRole }}">

                <div>
                    <!-- Active Role Banner -->
                    @php
                        $currRole = collect($rolesList)->firstWhere('id', $selectedRole);
                        $roleName = $currRole['name'] ?? ucfirst($selectedRole);
                        $roleDesc = $currRole['description'] ?? 'Rôle utilisateur SuperAdmin';
                    @endphp
                    <div class="mb-6 p-4 rounded-xl bg-blue-50/60 border border-blue-100 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#031C5B]">Rôle actuellement configuré</span>
                                <button type="button" onclick="openEditRoleModal('{{ $selectedRole }}', '{{ addslashes($roleName) }}', '{{ addslashes($roleDesc) }}')" class="text-[11px] font-bold text-[#7C3AED] hover:underline cursor-pointer flex items-center gap-1">
                                    <i class="ph ph-pencil-simple font-bold"></i> Modifier l'intitulé
                                </button>
                            </div>
                            <h4 class="text-base font-extrabold text-slate-900">
                                {{ $roleName }}
                            </h4>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $roleDesc }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="checkAllCrud(true)" class="text-[11px] font-bold text-blue-700 bg-white px-2.5 py-1 rounded-lg border border-blue-200 hover:bg-blue-50 transition cursor-pointer">Tout Cocher</button>
                            <button type="button" onclick="checkAllCrud(false)" class="text-[11px] font-bold text-slate-600 bg-white px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-50 transition cursor-pointer">Tout Décocher</button>
                        </div>
                    </div>

                    <!-- CRUD Table -->
                    <div class="overflow-x-auto border border-slate-200 rounded-xl mb-6">
                        <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3.5 px-4 w-1/3">Fonctionnalité SuperAdmin</th>
                                    <th class="py-3.5 px-2 text-center text-emerald-700">Consulter<br><span class="font-mono text-[9px] lowercase font-normal">(show)</span></th>
                                    <th class="py-3.5 px-2 text-center text-blue-700">Créer<br><span class="font-mono text-[9px] lowercase font-normal">(create)</span></th>
                                    <th class="py-3.5 px-2 text-center text-amber-700">Éditer<br><span class="font-mono text-[9px] lowercase font-normal">(edit)</span></th>
                                    <th class="py-3.5 px-2 text-center text-purple-700">Mise à jour<br><span class="font-mono text-[9px] lowercase font-normal">(update)</span></th>
                                    <th class="py-3.5 px-2 text-center text-red-700">Supprimer<br><span class="font-mono text-[9px] lowercase font-normal">(delete)</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($functionalities as $fKey => $fInfo)
                                    @php
                                        $perms = $rolePermissions[$fKey] ?? ['show' => false, 'create' => false, 'edit' => false, 'update' => false, 'delete' => false];
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3.5 px-4">
                                            <p class="font-bold text-slate-900 text-[13px] mb-0.5">{{ $fInfo['name'] }}</p>
                                            <p class="text-[11px] font-medium text-slate-400 leading-snug truncate max-w-xs">{{ $fInfo['desc'] }}</p>
                                        </td>

                                        <!-- show -->
                                        <td class="py-3.5 px-2 text-center">
                                            <input type="checkbox" name="permissions[{{ $fKey }}][show]" value="1" {{ !empty($perms['show']) ? 'checked' : '' }} class="crud-cb w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer">
                                        </td>

                                        <!-- create -->
                                        <td class="py-3.5 px-2 text-center">
                                            <input type="checkbox" name="permissions[{{ $fKey }}][create]" value="1" {{ !empty($perms['create']) ? 'checked' : '' }} class="crud-cb w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer">
                                        </td>

                                        <!-- edit -->
                                        <td class="py-3.5 px-2 text-center">
                                            <input type="checkbox" name="permissions[{{ $fKey }}][edit]" value="1" {{ !empty($perms['edit']) ? 'checked' : '' }} class="crud-cb w-4 h-4 text-amber-600 rounded border-slate-300 focus:ring-amber-500 cursor-pointer">
                                        </td>

                                        <!-- update -->
                                        <td class="py-3.5 px-2 text-center">
                                            <input type="checkbox" name="permissions[{{ $fKey }}][update]" value="1" {{ !empty($perms['update']) ? 'checked' : '' }} class="crud-cb w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500 cursor-pointer">
                                        </td>

                                        <!-- delete -->
                                        <td class="py-3.5 px-2 text-center">
                                            <input type="checkbox" name="permissions[{{ $fKey }}][delete]" value="1" {{ !empty($perms['delete']) ? 'checked' : '' }} class="crud-cb w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500 cursor-pointer">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#031C5B] text-white text-xs font-bold hover:bg-blue-900 transition shadow-sm cursor-pointer flex items-center gap-2">
                        <i class="ph ph-check text-sm font-bold"></i> Enregistrer les Permissions (BD SQL)
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: Politique Globale -->
        <div class="bg-[#F8FAFC] rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6">
                <i class="ph ph-shield-check text-2xl text-[#7C3AED] font-bold"></i>
                <h3 class="text-[18px] font-extrabold text-[#111827]">Politique Globale de Sécurité</h3>
            </div>

            <form action="{{ route('superadmin.security-permissions.update') }}" method="POST" class="space-y-6 text-xs">
                @csrf
                <!-- Setting 1 -->
                <div class="flex items-start justify-between gap-4 p-4 bg-white rounded-xl border border-slate-200">
                    <div class="flex-1">
                        <label class="block text-[14px] font-bold text-[#111827] mb-0.5">Forcer l'A2F (2FA)</label>
                        <p class="text-[12px] text-slate-500 font-medium leading-snug">Exiger l'authentification à double facteur pour tout le personnel staff SuperAdmin.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                        <input type="checkbox" name="force_2fa" value="1" class="sr-only peer" {{ !empty($settings['2fa_enabled']) ? 'checked' : '' }}>
                        <div class="w-10 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#031C5B]"></div>
                    </label>
                </div>

                <!-- Setting 2 -->
                <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-2">
                    <label class="block text-[14px] font-bold text-[#111827]">Expiration de Session (minutes)</label>
                    <p class="text-slate-500 text-[11px]">Déconnexion automatique en cas d'inactivité prolongée.</p>
                    <input type="number" name="session_timeout" value="{{ $settings['session_timeout'] ?? '120' }}" min="15" max="1440" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 font-bold focus:outline-none focus:border-[#031C5B] transition shadow-2xs bg-slate-50">
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition shadow-2xs cursor-pointer">
                    Mettre à jour la Politique Globale
                </button>
            </form>
        </div>

    </div>

    <!-- Modal : Créer un Nouveau Rôle -->
    <div id="createRoleModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-6 py-5 bg-[#7C3AED] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center font-bold">
                        <i class="ph ph-shield-plus text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold">Créer un Nouveau Rôle SuperAdmin</h3>
                        <p class="text-xs text-purple-200 font-medium">Gestionnaire des Autorisations SQL</p>
                    </div>
                </div>
                <button type="button" onclick="closeCreateRoleModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <form action="{{ route('superadmin.security-permissions.create-role') }}" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Intitulé du Rôle *</label>
                    <input type="text" name="role_name" required placeholder="ex: Responsable Support / Auditeur Sécurité" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 font-bold focus:outline-none focus:border-purple-600 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Description & Périmètre d'Accès</label>
                    <textarea name="role_description" rows="3" placeholder="Description des responsabilités attribuées à ce rôle SuperAdmin..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-900 font-medium focus:outline-none focus:border-purple-600 focus:bg-white transition resize-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeCreateRoleModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold">Annuler</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#7C3AED] text-white font-bold hover:bg-purple-800 transition shadow-sm">Créer le Rôle</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal : Modifier le Rôle -->
    <div id="editRoleModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center font-bold">
                        <i class="ph ph-pencil-simple text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold">Modifier l'Intitulé du Rôle</h3>
                        <p class="text-xs text-blue-200 font-medium">Mise à jour en Base SQL</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditRoleModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <form action="{{ route('superadmin.security-permissions.update-role') }}" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                <input type="hidden" name="role_id" id="editRoleId">

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nom du Rôle *</label>
                    <input type="text" name="role_name" id="editRoleName" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 font-bold focus:outline-none focus:border-blue-600 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Description & Périmètre d'Accès</label>
                    <textarea name="role_description" id="editRoleDescription" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-900 font-medium focus:outline-none focus:border-blue-600 focus:bg-white transition resize-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeEditRoleModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold">Annuler</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#031C5B] text-white font-bold hover:bg-blue-900 transition shadow-sm">Enregistrer la modification</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateRoleModal() {
            const modal = document.getElementById('createRoleModal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeCreateRoleModal() {
            const modal = document.getElementById('createRoleModal');
            if (modal) modal.classList.add('hidden');
        }
        function openEditRoleModal(id, name, desc) {
            document.getElementById('editRoleId').value = id;
            document.getElementById('editRoleName').value = name;
            document.getElementById('editRoleDescription').value = desc;
            const modal = document.getElementById('editRoleModal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeEditRoleModal() {
            const modal = document.getElementById('editRoleModal');
            if (modal) modal.classList.add('hidden');
        }
        function checkAllCrud(checked) {
            document.querySelectorAll('.crud-cb').forEach(cb => {
                cb.checked = checked;
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeCreateRoleModal();
                closeEditRoleModal();
            }
        });
    </script>
@endsection
