@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-[1600px] w-full mx-auto space-y-6" x-data="{ selected: [] }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-[28px] font-bold text-[#0F172A] tracking-tight">Liste des Étudiants</h1>
            <p class="text-[15px] text-slate-500 mt-1">Gérez et suivez les inscriptions, le statut académique et les profils des étudiants.</p>
        </div>
        <a href="{{ route('school.academic.students.create') }}" class="bg-[#1E40AF] hover:bg-[#1E3A8A] text-white font-bold text-[14px] px-5 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
            <i class="ph-bold ph-user-plus"></i>
            Ajouter un étudiant
        </a>
    </div>

    <div x-show="selected.length > 0" x-cloak x-transition class="bg-[#031C5B] rounded-2xl shadow-sm px-5 py-3 flex items-center justify-between gap-4">
        <span class="text-white text-[14px] font-bold"><span x-text="selected.length"></span> étudiant(s) sélectionné(s)</span>
        <div class="flex items-center gap-2">
            <button type="button" @click="selected = []" class="text-white/70 hover:text-white text-[13px] font-semibold px-3 py-2">Désélectionner</button>
            <button type="button" @click="$refs.printCardsForm.submit()" class="bg-white text-[#031C5B] hover:bg-slate-100 font-bold text-[13px] px-4 py-2 rounded-xl transition flex items-center gap-2">
                <i class="ph-bold ph-identification-card text-[16px]"></i>
                Générer les Cartes
            </button>
        </div>
    </div>
    <form x-ref="printCardsForm" action="{{ route('school.academic.cards.print', 'student') }}" method="POST" target="_blank" class="hidden">
        @csrf
        <input type="hidden" name="holder_type" value="student">
        <template x-for="id in selected" :key="id">
            <input type="hidden" name="ids[]" :value="id">
        </template>
    </form>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Total Students -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-slate-50 rounded-bl-full -mr-4 -mt-4"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="ph-fill ph-users text-[20px]"></i>
                </div>
                <h3 class="text-slate-600 font-semibold text-[14px]">Total Étudiants</h3>
            </div>
            <div class="text-3xl font-bold text-slate-800 mb-1">{{ number_format($stats['total'], 0, ',', ' ') }}</div>
            <div class="flex items-center gap-1 text-[12px] font-bold text-slate-500">
                <span>Enregistrés dans le système</span>
            </div>
        </div>
        
        <!-- Active Students -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-slate-50 rounded-bl-full -mr-4 -mt-4"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                    <i class="ph-fill ph-check-circle text-[20px]"></i>
                </div>
                <h3 class="text-slate-600 font-semibold text-[14px]">Étudiants Actifs</h3>
            </div>
            <div class="text-3xl font-bold text-slate-800 mb-1">{{ number_format($stats['active'], 0, ',', ' ') }}</div>
            <div class="flex items-center gap-1 text-[12px] font-bold text-green-600">
                <span>Statut actif</span>
            </div>
        </div>
        
        <!-- Inactive Students -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-slate-50 rounded-bl-full -mr-4 -mt-4"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                    <i class="ph-fill ph-user-minus text-[20px]"></i>
                </div>
                <h3 class="text-slate-600 font-semibold text-[14px]">Étudiants Inactifs</h3>
            </div>
            <div class="text-3xl font-bold text-slate-800 mb-1">{{ number_format($stats['inactive'], 0, ',', ' ') }}</div>
            <div class="flex items-center gap-1 text-[12px] font-bold text-slate-500">
                <span>Statut inactif</span>
            </div>
        </div>
        
        <!-- Recent Enrollments -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-slate-50 rounded-bl-full -mr-4 -mt-4"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
                    <i class="ph-fill ph-student text-[20px]"></i>
                </div>
                <h3 class="text-slate-600 font-semibold text-[14px]">Nouvelles Inscriptions</h3>
            </div>
            <div class="text-3xl font-bold text-slate-800 mb-1">{{ number_format($stats['recent'], 0, ',', ' ') }}</div>
            <div class="flex items-center gap-1 text-[12px] font-bold text-slate-500">
                <span>Ce mois-ci</span>
            </div>
        </div>
    </div>
    
    <!-- Table Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <form method="GET" action="{{ route('school.academic.students') }}" class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100">
            <div class="relative w-full md:w-80">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Rechercher par nom ou ID..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg pl-9 pr-3 py-2 outline-none focus:border-[#2F5F76] transition">
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto overflow-x-auto">
                <div class="relative min-w-[140px]">
                    <select name="academic_class_id" onchange="this.form.submit()" class="w-full appearance-none bg-white border border-slate-200 text-slate-700 text-[13px] font-medium rounded-lg px-4 py-2 pr-8 outline-none focus:border-[#2F5F76]">
                        <option value="">Classe (Toutes)</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ (string) ($filters['academic_class_id'] ?? '') === (string) $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <i class="ph-bold ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-[12px]"></i>
                </div>
                <div class="relative min-w-[140px]">
                    <select name="status" onchange="this.form.submit()" class="w-full appearance-none bg-white border border-slate-200 text-slate-700 text-[13px] font-medium rounded-lg px-4 py-2 pr-8 outline-none focus:border-[#2F5F76]">
                        <option value="">Statut (Tous)</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                    </select>
                    <i class="ph-bold ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-[12px]"></i>
                </div>
                <button type="submit" class="bg-[#EEF2F6] hover:bg-[#E3E8EF] text-slate-600 px-3 py-2 rounded-lg transition flex items-center justify-center flex-shrink-0">
                    <i class="ph-bold ph-funnel-simple text-[16px]"></i>
                </button>
                @if(!empty($filters['search']) || !empty($filters['academic_class_id']) || !empty($filters['status']))
                <a href="{{ route('school.academic.students') }}" class="text-slate-400 hover:text-red-600 px-2 py-2 rounded-lg transition flex items-center justify-center flex-shrink-0" title="Réinitialiser les filtres">
                    <i class="ph-bold ph-x text-[16px]"></i>
                </a>
                @endif
            </div>
        </form>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-300" @change="selected = $event.target.checked ? {{ $students->pluck('id')->toJson() }} : []" :checked="selected.length > 0 && selected.length === {{ $students->count() }}">
                        </th>
                        <th class="px-6 py-4 text-[13px] font-bold text-slate-700 w-16">Photo</th>
                        <th class="px-6 py-4 text-[13px] font-bold text-slate-700">Nom de l'Étudiant</th>
                        <th class="px-6 py-4 text-[13px] font-bold text-slate-700">ID / Matricule</th>
                        <th class="px-6 py-4 text-[13px] font-bold text-slate-700">Classe / Section</th>
                        <th class="px-6 py-4 text-[13px] font-bold text-slate-700">Parent / Tuteur</th>
                        <th class="px-6 py-4 text-[13px] font-bold text-slate-700">Statut</th>
                        <th class="px-6 py-4 text-[13px] font-bold text-slate-700 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-300" value="{{ $student->id }}" x-model="selected">
                        </td>
                        <td class="px-6 py-4">
                            @if($student->photo_path)
                                <img src="{{ asset('storage/' . $student->photo_path) }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-[#EEF2F6] text-[#334155] font-bold text-[14px] flex items-center justify-center">
                                    {{ substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-[14px] font-bold text-[#0F172A]">{{ $student->first_name }} {{ $student->last_name }}</div>
                            <div class="text-[13px] text-slate-500">{{ $student->email ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 text-[13px] font-bold text-slate-700">{{ $student->roll_number }}</td>
                        <td class="px-6 py-4">
                            <div class="text-[14px] font-semibold text-slate-700">{{ $student->academicClass ? $student->academicClass->name : 'N/A' }}</div>
                            <div class="text-[11px] font-bold text-slate-500">{{ $student->academic_year }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @forelse($student->guardians as $guardian)
                                <div class="text-[13px] font-semibold text-slate-700">{{ $guardian->name }}</div>
                            @empty
                                <div class="text-[11px] font-bold text-slate-400">Non lié</div>
                            @endforelse
                        </td>
                        <td class="px-6 py-4">
                            @if($student->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#E6F4EA] text-[#137333] border border-[#CEEAD6]">
                                    ACTIF
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#FCE8E8] text-[#C5221F] border border-[#F9D2D2]">
                                    INACTIF
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="relative inline-block text-left" x-data="{ open: false, openUpward: false, edgePx: 0, left: 0 }">
                                <button type="button" @click="
                                    open = !open;
                                    const r = $el.getBoundingClientRect();
                                    // Menu height varies per row (some items are permission-gated), so we can't
                                    // know it up front — flipping to anchor via `bottom` instead of `top` when
                                    // space below is tight lets CSS grow the menu upward on its own, with no
                                    // need to guess a fixed height (and no risk of it running off-screen).
                                    openUpward = (window.innerHeight - r.bottom) < 260 && r.top > (window.innerHeight - r.bottom);
                                    edgePx = Math.max(6, openUpward ? (window.innerHeight - r.top + 6) : (r.bottom + 6));
                                    left = r.right - 224;
                                " class="text-slate-500 hover:text-slate-700 bg-slate-100 p-2 rounded-lg transition">
                                    <i class="ph-bold ph-dots-three-vertical text-[18px]"></i>
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak x-transition
                                     :style="openUpward ? `position: fixed; bottom: ${edgePx}px; left: ${left}px;` : `position: fixed; top: ${edgePx}px; left: ${left}px;`"
                                     class="w-56 max-h-[70vh] overflow-y-auto bg-white rounded-xl shadow-lg border border-slate-100 z-50 py-2" style="display: none;">
                                    <a href="{{ route('school.academic.students.show', $student->id) }}" class="flex items-center gap-3 px-4 py-2.5 text-[13.5px] font-semibold text-[#031C5B] hover:bg-slate-50 transition">
                                        <i class="ph-bold ph-identification-badge text-[18px]"></i> Fiche Élève
                                    </a>
                                    <form action="{{ route('school.academic.cards.print', 'student') }}" method="POST" target="_blank">
                                        @csrf
                                        <input type="hidden" name="holder_type" value="student">
                                        <input type="hidden" name="ids[]" value="{{ $student->id }}">
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-[13.5px] font-semibold text-[#031C5B] hover:bg-slate-50 transition text-left">
                                            <i class="ph-bold ph-identification-card text-[18px]"></i> Générer la carte
                                        </button>
                                    </form>
                                    @if(auth()->user()->canAccess('report-card.manage'))
                                    <a href="{{ route('school.report-card.student', $student->id) }}" class="flex items-center gap-3 px-4 py-2.5 text-[13.5px] font-semibold text-purple-600 hover:bg-slate-50 transition">
                                        <i class="ph-bold ph-medal text-[18px]"></i> Livret Scolaire
                                    </a>
                                    @endif
                                    @if(auth()->user()->canAccess('academic.bulletins.manage'))
                                    <a href="{{ route('school.academic.bulletins.print', $student->id) }}" target="_blank" class="flex items-center gap-3 px-4 py-2.5 text-[13.5px] font-semibold text-amber-600 hover:bg-slate-50 transition">
                                        <i class="ph-bold ph-newspaper text-[18px]"></i> Bulletin
                                    </a>
                                    @endif
                                    <a href="{{ route('school.academic.students.edit', $student->id) }}" class="flex items-center gap-3 px-4 py-2.5 text-[13.5px] font-semibold text-blue-500 hover:bg-slate-50 transition">
                                        <i class="ph-bold ph-pencil-simple text-[18px]"></i> Modifier
                                    </a>
                                    <div class="border-t border-slate-100 my-1"></div>
                                    <form action="{{ route('school.academic.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-[13.5px] font-semibold text-red-500 hover:bg-red-50 transition text-left">
                                            <i class="ph-bold ph-trash text-[18px]"></i> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-500">
                            <i class="ph-fill ph-student text-4xl text-slate-300 mb-3 block"></i>
                            Aucun étudiant trouvé.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <span class="text-[14px] font-medium text-slate-500">
                Affichage de {{ $students->firstItem() ?? 0 }} à {{ $students->lastItem() ?? 0 }} sur {{ $students->total() }} entrées
            </span>
            
            <div class="flex items-center">
                {{ $students->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
@endsection
