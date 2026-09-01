@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6" x-data="{ selected: [] }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Gestion des Enseignants</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Vue détaillée du corps professoral et statuts en temps réel.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('school.academic.teachers.create') }}" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
                <i class="ph-bold ph-user-plus text-lg"></i>
                Ajouter un Enseignant
            </a>
        </div>
    </div>

    <div x-show="selected.length > 0" x-cloak x-transition class="bg-[#031C5B] rounded-2xl shadow-sm px-5 py-3 flex items-center justify-between gap-4">
        <span class="text-white text-[14px] font-bold"><span x-text="selected.length"></span> enseignant(s) sélectionné(s)</span>
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
        <input type="hidden" name="holder_type" value="teacher">
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
        <!-- Card 1 -->
        <div class="bg-white p-6 rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-100 relative overflow-hidden flex flex-col justify-between h-[160px]">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 rounded-xl bg-[#F1F5F9] flex items-center justify-center text-[#031C5B]">
                    <i class="ph-fill ph-users text-2xl"></i>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-[#DCFCE7] text-[#166534] uppercase tracking-wider">
                    +2% Ce mois
                </span>
            </div>
            <div>
                <p class="text-[13px] text-slate-500 font-bold mb-1">Total Enseignants</p>
                <h3 class="text-[36px] font-extrabold text-[#0F172A] leading-none">{{ number_format($stats['total'], 0, ',', ' ') }}</h3>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white p-6 rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-100 relative overflow-hidden flex flex-col justify-between h-[160px]">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 rounded-xl bg-[#F1F5F9] flex items-center justify-center text-emerald-600">
                    <i class="ph-fill ph-user-check text-2xl"></i>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-[#E2E8F0] text-[#475569] uppercase tracking-wider">
                    Aujourd'hui
                </span>
            </div>
            <div>
                <p class="text-[13px] text-slate-500 font-bold mb-1">Présents</p>
                <div class="flex items-baseline gap-1">
                    <h3 class="text-[36px] font-extrabold text-[#0F172A] leading-none">{{ number_format($stats['present'], 0, ',', ' ') }}</h3>
                    <span class="text-slate-400 font-semibold text-lg">/ {{ number_format($stats['total'], 0, ',', ' ') }}</span>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white p-6 rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-100 relative overflow-hidden flex flex-col justify-between h-[160px]">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <i class="ph-fill ph-calendar-x text-2xl"></i>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-700 uppercase tracking-wider">
                    RH
                </span>
            </div>
            <div>
                <p class="text-[13px] text-slate-500 font-bold mb-1">Contrats à Renouveler (30 jours)</p>
                <div class="flex items-baseline gap-2 mb-2">
                    <h3 class="text-[36px] font-extrabold text-[#0F172A] leading-none">{{ $stats['contracts_expiring'] }}</h3>
                    <span class="text-amber-600 font-bold text-sm">enseignant(s)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-100 overflow-hidden flex flex-col">
        <!-- Toolbar -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="text-xl font-bold text-slate-800">Liste Globale</h3>
            <div class="flex items-center gap-3">
                <form action="{{ route('school.academic.teachers') }}" method="GET" class="relative m-0">
                    <select name="subject_id" onchange="this.form.submit()" class="appearance-none bg-[#F8FAFC] border border-slate-200 text-slate-700 py-2.5 pl-10 pr-10 rounded-xl font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-[#031C5B]/20 cursor-pointer">
                        <option value="">Toutes les matières</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ (isset($filters['subject_id']) && $filters['subject_id'] == $subject->id) ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    <i class="ph-bold ph-funnel absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <i class="ph-bold ph-caret-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                </form>
                <button class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                    <i class="ph-bold ph-download-simple text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto min-h-[300px]">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-4 w-10">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-300" @change="selected = $event.target.checked ? {{ $teachers->pluck('id')->toJson() }} : []" :checked="selected.length > 0 && selected.length === {{ $teachers->count() }}">
                        </th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider w-[250px]">Enseignant</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">ID / Matricule</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Spécialité / Matière</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Classes Assignées</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Prof Principal</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($teachers as $teacher)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-5 py-4">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-300" value="{{ $teacher->id }}" x-model="selected">
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($teacher->photo_path)
                                    <img src="{{ asset('storage/' . $teacher->photo_path) }}" alt="{{ $teacher->first_name }}" class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-sm">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-[#031C5B] text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                        {{ substr($teacher->first_name, 0, 1) }}{{ substr($teacher->last_name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-extrabold text-[#0F172A] text-[14px]">{{ $teacher->first_name }} {{ $teacher->last_name }}</div>
                                    <div class="text-[12px] text-slate-500 font-medium">{{ $teacher->employment_type == 'full_time' ? 'Temps plein' : 'Vacataire' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">
                            {{ $teacher->employee_id }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($teacher->subjects->take(2) as $subject)
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-[#E0E7FF] text-[#3730A3] text-[11px] font-bold">
                                        {{ $subject->name }}
                                    </span>
                                @empty
                                    <span class="text-slate-400 font-medium text-[13px]">-</span>
                                @endforelse
                                @if($teacher->subjects->count() > 2)
                                    <span class="inline-flex items-center text-[12px] font-bold text-slate-500 ml-1">
                                        +{{ $teacher->subjects->count() - 2 }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($teacher->classes->take(2) as $class)
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-[#F1F5F9] text-[#475569] text-[11px] font-bold">
                                        {{ $class->name }}
                                    </span>
                                @endforeach
                                @if($teacher->classes->count() > 2)
                                    <span class="inline-flex items-center text-[12px] font-bold text-slate-500 ml-1">
                                        +{{ $teacher->classes->count() - 2 }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($teacher->headTeacherClasses as $class)
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-[#ECFDF5] text-[#047857] text-[11px] font-bold">
                                        <i class="ph-fill ph-star text-[#10B981] mr-1 text-[12px]"></i>
                                        {{ $class->name }}
                                    </span>
                                @empty
                                    <span class="text-slate-400 font-medium text-[13px]">-</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @if($teacher->status == 'active')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[12px] font-bold bg-[#A7F3D0] text-[#065F46]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#059669] mr-1.5"></span> Actif
                            </span>
                            @elseif($teacher->status == 'on_leave')
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
                            <div class="relative flex justify-end" x-data="{ open: false, openUpward: false, edgePx: 0, left: 0 }" @click.away="open = false">
                                <button @click="
                                    open = !open;
                                    const r = $el.getBoundingClientRect();
                                    // position:fixed + viewport math, not position:absolute — this row sits
                                    // inside several nested scroll/overflow-hidden containers (the table's own
                                    // scroll wrapper, the card, the page) that would otherwise silently clip
                                    // the menu no matter which row it's opened from.
                                    openUpward = (window.innerHeight - r.bottom) < 200 && r.top > (window.innerHeight - r.bottom);
                                    edgePx = Math.max(4, openUpward ? (window.innerHeight - r.top + 4) : (r.bottom + 4));
                                    left = r.right - 192;
                                " class="p-1.5 text-slate-400 hover:text-[#031C5B] hover:bg-slate-100 rounded-lg transition">
                                    <i class="ph-bold ph-dots-three-vertical text-xl"></i>
                                </button>

                                <div x-show="open" x-transition.opacity
                                     :style="openUpward ? `position: fixed; bottom: ${edgePx}px; left: ${left}px;` : `position: fixed; top: ${edgePx}px; left: ${left}px;`"
                                     class="w-48 max-h-[70vh] overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-lg z-[60] py-1" style="display: none;">
                                    <form action="{{ route('school.academic.cards.print', 'staff') }}" method="POST" target="_blank">
                                        @csrf
                                        <input type="hidden" name="holder_type" value="teacher">
                                        <input type="hidden" name="ids[]" value="{{ $teacher->id }}">
                                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-[13px] font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#031C5B] transition-colors text-left">
                                            <i class="ph-bold ph-identification-card text-[16px]"></i>
                                            Générer la carte
                                        </button>
                                    </form>
                                    <a href="{{ route('school.academic.teachers.show', $teacher->id) }}" class="flex items-center gap-2 px-4 py-2 text-[13px] font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#031C5B] transition-colors">
                                        <i class="ph-bold ph-eye text-[16px]"></i>
                                        Voir détails
                                    </a>
                                    <a href="{{ route('school.academic.teachers.edit', $teacher->id) }}" class="flex items-center gap-2 px-4 py-2 text-[13px] font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#031C5B] transition-colors">
                                        <i class="ph-bold ph-pencil-simple text-[16px]"></i>
                                        Modifier
                                    </a>
                                    <div class="h-px bg-slate-100 my-1"></div>
                                    <form action="{{ route('school.academic.teachers.destroy', $teacher->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet enseignant ?');">
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
                            Aucun enseignant enregistré.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-5 border-t border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="text-[13px] text-slate-500 font-semibold">
                Affichage {{ $teachers->firstItem() ?? 0 }} à {{ $teachers->lastItem() ?? 0 }} sur {{ number_format($teachers->total(), 0, ',', ' ') }} enseignants
            </div>
            <div class="flex items-center">
                {{ $teachers->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
@endsection
