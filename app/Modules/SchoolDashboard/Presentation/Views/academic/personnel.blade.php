@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6" x-data="{ selected: [] }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Gestion du Personnel</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Vue détaillée du personnel administratif et technique.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('school.academic.personnel.create') }}" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
                <i class="ph-bold ph-user-plus text-lg"></i>
                Ajouter un Membre du Personnel
            </a>
        </div>
    </div>

    <div x-show="selected.length > 0" x-cloak x-transition class="bg-[#031C5B] rounded-2xl shadow-sm px-5 py-3 flex items-center justify-between gap-4">
        <span class="text-white text-[14px] font-bold"><span x-text="selected.length"></span> membre(s) sélectionné(s)</span>
        <div class="flex items-center gap-2">
            <button type="button" @click="selected = []" class="text-white/70 hover:text-white text-[13px] font-semibold px-3 py-2">Désélectionner</button>
            <button type="button" @click="$refs.printCardsForm.submit()" class="bg-white text-[#031C5B] hover:bg-slate-100 font-bold text-[13px] px-4 py-2 rounded-xl transition flex items-center gap-2">
                <i class="ph-bold ph-identification-card text-[16px]"></i>
                Générer les Cartes
            </button>
        </div>
    </div>
    <form x-ref="printCardsForm" action="{{ route('school.academic.cards.print', 'staff') }}" method="POST" target="_blank" class="hidden">
        @csrf
        <input type="hidden" name="holder_type" value="staff">
        <template x-for="id in selected" :key="id">
            <input type="hidden" name="ids[]" :value="id">
        </template>
    </form>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-100 relative overflow-hidden flex flex-col justify-between h-[160px]">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 rounded-xl bg-[#F1F5F9] flex items-center justify-center text-[#031C5B]">
                    <i class="ph-fill ph-identification-badge text-2xl"></i>
                </div>
            </div>
            <div>
                <p class="text-[13px] text-slate-500 font-bold mb-1">Total Personnel</p>
                <h3 class="text-[36px] font-extrabold text-[#0F172A] leading-none">{{ number_format($stats['total'], 0, ',', ' ') }}</h3>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-100 relative overflow-hidden flex flex-col justify-between h-[160px]">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 rounded-xl bg-[#F1F5F9] flex items-center justify-center text-emerald-600">
                    <i class="ph-fill ph-user-check text-2xl"></i>
                </div>
            </div>
            <div>
                <p class="text-[13px] text-slate-500 font-bold mb-1">Actifs</p>
                <div class="flex items-baseline gap-1">
                    <h3 class="text-[36px] font-extrabold text-[#0F172A] leading-none">{{ number_format($stats['active'], 0, ',', ' ') }}</h3>
                    <span class="text-slate-400 font-semibold text-lg">/ {{ number_format($stats['total'], 0, ',', ' ') }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-100 relative overflow-hidden flex flex-col justify-between h-[160px]">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 rounded-xl bg-[#DCFCE7] flex items-center justify-center text-[#166534]">
                    <i class="ph-fill ph-user-plus text-2xl"></i>
                </div>
            </div>
            <div>
                <p class="text-[13px] text-slate-500 font-bold mb-1">Ajoutés ce mois-ci</p>
                <h3 class="text-[36px] font-extrabold text-[#0F172A] leading-none">{{ number_format($stats['recent'], 0, ',', ' ') }}</h3>
            </div>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-100 overflow-hidden flex flex-col">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="text-xl font-bold text-slate-800">Liste Globale</h3>
        </div>

        <div class="overflow-x-auto min-h-[300px]">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-4 w-10">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-300" @change="selected = $event.target.checked ? {{ $staffMembers->pluck('id')->toJson() }} : []" :checked="selected.length > 0 && selected.length === {{ $staffMembers->count() }}">
                        </th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider w-[250px]">Membre</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">ID / Matricule</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Poste</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Département</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($staffMembers as $staffMember)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-5 py-4">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-300" value="{{ $staffMember->id }}" x-model="selected">
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($staffMember->photo_path)
                                    <img src="{{ asset('storage/' . $staffMember->photo_path) }}" alt="{{ $staffMember->first_name }}" class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-sm">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-[#031C5B] text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                        {{ substr($staffMember->first_name, 0, 1) }}{{ substr($staffMember->last_name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-extrabold text-[#0F172A] text-[14px]">{{ $staffMember->first_name }} {{ $staffMember->last_name }}</div>
                                    <div class="text-[12px] text-slate-500 font-medium">{{ $staffMember->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">
                            {{ $staffMember->employee_id }}
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">
                            {{ $staffMember->role ?? '-' }}
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">
                            {{ $staffMember->department ?? '-' }}
                        </td>
                        <td class="px-5 py-4">
                            @if($staffMember->status == 'active')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[12px] font-bold bg-[#A7F3D0] text-[#065F46]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#059669] mr-1.5"></span> Actif
                            </span>
                            @elseif($staffMember->status == 'on_leave')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[12px] font-bold bg-[#FECDD3] text-[#9F1239]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#E11D48] mr-1.5"></span> En Congé
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[12px] font-bold bg-slate-200 text-slate-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-1.5"></span> Inactif
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="relative flex justify-end" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open" class="p-1.5 text-slate-400 hover:text-[#031C5B] hover:bg-slate-100 rounded-lg transition">
                                    <i class="ph-bold ph-dots-three-vertical text-xl"></i>
                                </button>

                                <div x-show="open" x-transition.opacity class="absolute right-0 top-full mt-1 w-48 bg-white border border-slate-200 rounded-xl shadow-lg z-20 py-1" style="display: none;">
                                    <form action="{{ route('school.academic.cards.print', 'staff') }}" method="POST" target="_blank">
                                        @csrf
                                        <input type="hidden" name="holder_type" value="staff">
                                        <input type="hidden" name="ids[]" value="{{ $staffMember->id }}">
                                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-[13px] font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#031C5B] transition-colors text-left">
                                            <i class="ph-bold ph-identification-card text-[16px]"></i>
                                            Générer la carte
                                        </button>
                                    </form>
                                    <a href="{{ route('school.academic.personnel.edit', $staffMember->id) }}" class="flex items-center gap-2 px-4 py-2 text-[13px] font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#031C5B] transition-colors">
                                        <i class="ph-bold ph-pencil-simple text-[16px]"></i>
                                        Modifier
                                    </a>
                                    <div class="h-px bg-slate-100 my-1"></div>
                                    <form action="{{ route('school.academic.personnel.destroy', $staffMember->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce membre du personnel ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-[13px] font-semibold text-red-600 hover:bg-red-50 transition-colors text-left">
                                            <i class="ph-bold ph-trash text-[16px]"></i>
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-500 font-medium">
                            Aucun membre du personnel enregistré.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-5 border-t border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="text-[13px] text-slate-500 font-semibold">
                Affichage {{ $staffMembers->firstItem() ?? 0 }} à {{ $staffMembers->lastItem() ?? 0 }} sur {{ number_format($staffMembers->total(), 0, ',', ' ') }} membres
            </div>
            <div class="flex items-center">
                {{ $staffMembers->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
@endsection
