@extends('SchoolDashboard::layouts.app')

@section('content')
<div x-data="{ createOpen: false, renameOpen: false }" class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Matrice d'Autorisations par Rôle</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Cochez les droits (consulter, créer, éditer, mettre à jour, supprimer) accordés à chaque rôle.</p>
        </div>
        <button type="button" @click="createOpen = true" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
            <i class="ph-bold ph-plus text-lg"></i>
            Nouveau Rôle
        </button>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 text-sm text-red-800 rounded-xl bg-red-50 flex items-center gap-2 border border-red-100" role="alert">
        <i class="ph-fill ph-warning-circle text-lg"></i>
        <span class="font-bold">{{ session('error') }}</span>
    </div>
    @endif

    @if($roles->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-16 text-center">
            <i class="ph-bold ph-shield-check text-5xl text-slate-300 mb-4"></i>
            <p class="text-slate-500 font-medium mb-4">Aucun rôle défini. Créez-en un pour pouvoir attribuer des accès aux enseignants et au personnel.</p>
            <button type="button" @click="createOpen = true" class="inline-flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition">
                <i class="ph-bold ph-plus text-lg"></i> Créer le premier rôle
            </button>
        </div>
    @else
        <!-- Tabs -->
        <div class="flex items-center gap-1 border-b border-slate-200 overflow-x-auto">
            @foreach($roles as $role)
                <a href="{{ route('school.roles', ['role' => $role->id]) }}"
                   class="px-4 py-2.5 text-[13.5px] font-bold whitespace-nowrap border-b-2 -mb-px transition
                   {{ $selectedRole && $selectedRole->id === $role->id ? 'border-[#031C5B] text-[#031C5B]' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                    {{ $role->name }}
                </a>
            @endforeach
        </div>

        @if($selectedRole)
        <!-- Configured role panel -->
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Rôle actuellement configuré</span>
                    <button type="button" @click="renameOpen = true" class="text-[12px] font-bold text-[#031C5B] hover:underline flex items-center gap-1">
                        <i class="ph-bold ph-pencil-simple"></i> Modifier l'intitulé
                    </button>
                </div>
                <h3 class="text-[22px] font-extrabold text-[#0F172A]">{{ $selectedRole->name }}</h3>
                <p class="text-[13px] text-slate-500 mt-1">
                    {{ $selectedRole->users_count }} compte(s) assigné(s)
                    @if($selectedRole->is_branch_director)
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-purple-100 text-purple-700 text-[10px] font-bold ml-2 align-middle">Directeur de Succursale</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="setAllCheckboxes(true)" class="px-4 py-2.5 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-bold text-[13px] rounded-xl transition">Tout Cocher</button>
                <button type="button" onclick="setAllCheckboxes(false)" class="px-4 py-2.5 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-bold text-[13px] rounded-xl transition">Tout Décocher</button>
                <form action="{{ route('school.roles.destroy', $selectedRole->id) }}" method="POST" onsubmit="return confirm('Supprimer ce rôle ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2.5 bg-white border border-slate-200 hover:border-red-300 hover:text-red-600 text-slate-500 rounded-xl transition" title="Supprimer le rôle">
                        <i class="ph-bold ph-trash text-[16px]"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Matrix -->
        <form id="matrixForm" action="{{ route('school.roles.permissions.update', $selectedRole->id) }}" method="POST">
            @csrf
            <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-[#F8FAFC] border-b border-slate-200">
                                <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Fonctionnalité</th>
                                <th class="px-4 py-4 text-[11px] font-extrabold text-emerald-700 uppercase tracking-wider text-center">Consulter<br><span class="font-mono normal-case font-medium text-slate-400">(show)</span></th>
                                <th class="px-4 py-4 text-[11px] font-extrabold text-blue-700 uppercase tracking-wider text-center">Créer<br><span class="font-mono normal-case font-medium text-slate-400">(create)</span></th>
                                <th class="px-4 py-4 text-[11px] font-extrabold text-amber-700 uppercase tracking-wider text-center">Éditer<br><span class="font-mono normal-case font-medium text-slate-400">(edit)</span></th>
                                <th class="px-4 py-4 text-[11px] font-extrabold text-purple-700 uppercase tracking-wider text-center">Mise à jour<br><span class="font-mono normal-case font-medium text-slate-400">(update)</span></th>
                                <th class="px-4 py-4 text-[11px] font-extrabold text-red-700 uppercase tracking-wider text-center">Supprimer<br><span class="font-mono normal-case font-medium text-slate-400">(delete)</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($permissionsGrouped as $group => $groupPermissions)
                                <tr class="bg-slate-50/70">
                                    <td colspan="6" class="px-5 py-2 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">{{ $group }}</td>
                                </tr>
                                @foreach($groupPermissions as $permission)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-5 py-3.5 font-bold text-[#0F172A] text-[13.5px]">{{ $permission->name }}</td>
                                        @foreach(['show', 'create', 'edit', 'update', 'delete'] as $action)
                                            <td class="px-4 py-3.5 text-center">
                                                <input type="checkbox"
                                                    name="permissions[{{ $permission->id }}][{{ $action }}]"
                                                    value="1"
                                                    {{ ($matrix[$permission->id][$action] ?? false) ? 'checked' : '' }}
                                                    class="matrix-checkbox w-4 h-4 rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]/20">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all shadow-sm flex items-center gap-2">
                        <i class="ph-bold ph-check"></i>
                        Enregistrer les Autorisations
                    </button>
                </div>
            </div>
        </form>
        @endif
    @endif

    <!-- Create Role Modal -->
    <div x-show="createOpen" x-cloak class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" style="display:none">
        <div @click.outside="createOpen = false" class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-[18px] font-extrabold text-[#0F172A] mb-4">Nouveau Rôle</h3>
            <form action="{{ route('school.roles.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Nom du rôle</label>
                    <input type="text" name="name" required placeholder="Ex: Secrétaire, Comptable" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] font-medium rounded-xl px-4 py-3 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10">
                </div>
                <label class="flex items-start gap-3 px-4 py-3 rounded-xl border border-purple-200 bg-purple-50/50 cursor-pointer">
                    <input type="checkbox" name="is_branch_director" value="1" class="mt-0.5 w-4 h-4 rounded border-purple-300 text-purple-700 focus:ring-purple-500/20">
                    <span class="text-[12.5px] font-bold text-purple-900">Rôle de Directeur de Succursale</span>
                </label>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="createOpen = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[13.5px] rounded-xl hover:bg-slate-50">Annuler</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[13.5px] rounded-xl hover:bg-[#031C5B]/90">Créer</button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedRole)
    <!-- Rename Modal -->
    <div x-show="renameOpen" x-cloak class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" style="display:none">
        <div @click.outside="renameOpen = false" class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-[18px] font-extrabold text-[#0F172A] mb-4">Modifier l'intitulé du rôle</h3>
            <form action="{{ route('school.roles.rename', $selectedRole->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Nom du rôle</label>
                    <input type="text" name="name" required value="{{ $selectedRole->name }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] font-medium rounded-xl px-4 py-3 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10">
                </div>
                <label class="flex items-start gap-3 px-4 py-3 rounded-xl border border-purple-200 bg-purple-50/50 cursor-pointer">
                    <input type="checkbox" name="is_branch_director" value="1" {{ $selectedRole->is_branch_director ? 'checked' : '' }} class="mt-0.5 w-4 h-4 rounded border-purple-300 text-purple-700 focus:ring-purple-500/20">
                    <span class="text-[12.5px] font-bold text-purple-900">Rôle de Directeur de Succursale</span>
                </label>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="renameOpen = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[13.5px] rounded-xl hover:bg-slate-50">Annuler</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[13.5px] rounded-xl hover:bg-[#031C5B]/90">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

<style>[x-cloak]{display:none!important}</style>
<script>
    function setAllCheckboxes(checked) {
        document.querySelectorAll('#matrixForm .matrix-checkbox').forEach(cb => cb.checked = checked);
    }
</script>
@endsection
