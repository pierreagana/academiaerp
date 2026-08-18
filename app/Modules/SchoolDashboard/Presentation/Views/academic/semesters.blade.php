@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Gestion des Semestres</h1>
            <p class="text-[13.5px] text-slate-500 mt-1">Gérez les semestres, trimestres ou périodes académiques de l'école.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    
    @if($errors->any())
    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <div class="flex items-center gap-2 mb-2">
            <i class="ph-fill ph-warning-circle text-lg"></i>
            <span class="font-bold">Il y a des erreurs dans le formulaire :</span>
        </div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Top Section: Add/Edit Semester Form -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill {{ isset($editSemester) ? 'ph-pencil-simple' : 'ph-plus-circle' }} text-primary-dynamic"></i>
                {{ isset($editSemester) ? 'Modifier le semestre' : 'Ajouter un semestre' }}
            </h2>
            @if(isset($editSemester))
            <a href="{{ route('school.academic.semesters') }}" class="text-[12px] font-medium text-slate-500 hover:text-slate-800 transition">Annuler l'édition</a>
            @endif
        </div>
        <div class="p-5">
            <form action="{{ isset($editSemester) ? route('school.academic.semesters.update', $editSemester->id) : route('school.academic.semesters.store') }}" method="POST">
                @csrf
                @if(isset($editSemester))
                    @method('PUT')
                @endif
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    
                    <!-- Nom -->
                    <div class="md:col-span-1">
                        <label for="name" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Nom du semestre <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" required value="{{ old('name', $editSemester->name ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="Ex: Semestre 1">
                    </div>
                    
                    <!-- Date début -->
                    <div class="md:col-span-1">
                        <label for="start_date" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Date de début <span class="text-red-500">*</span></label>
                        <input type="date" id="start_date" name="start_date" required value="{{ old('start_date', $editSemester->start_date ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm">
                    </div>

                    <!-- Date fin -->
                    <div class="md:col-span-1">
                        <label for="end_date" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Date de fin <span class="text-red-500">*</span></label>
                        <input type="date" id="end_date" name="end_date" required value="{{ old('end_date', $editSemester->end_date ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm">
                    </div>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
                    <!-- Année scolaire -->
                    <div class="md:col-span-1">
                        <label for="academic_year" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Année scolaire <span class="text-slate-400 font-normal">(optionnel)</span></label>
                        <input type="text" id="academic_year" name="academic_year" value="{{ old('academic_year', $editSemester->academic_year ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="Ex: 2026-2027">
                        <p class="text-[11.5px] text-slate-400 mt-1">Regroupe plusieurs trimestres d'une même année scolaire pour calculer la moyenne annuelle finale.</p>
                    </div>

                    <!-- Numéro de trimestre -->
                    <div class="md:col-span-1">
                        <label for="term_number" class="block text-[13px] font-semibold text-slate-700 mb-1.5">N° de trimestre <span class="text-slate-400 font-normal">(optionnel)</span></label>
                        <select id="term_number" name="term_number" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                            <option value="">Non défini</option>
                            @foreach([1, 2, 3] as $n)
                                <option value="{{ $n }}" {{ (string) old('term_number', $editSemester->term_number ?? '') === (string) $n ? 'selected' : '' }}>Trimestre {{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] px-6 py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                        <i class="ph-bold ph-floppy-disk"></i>
                        {{ isset($editSemester) ? 'Mettre à jour' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bottom Section: Semesters List -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mt-8" x-data="{ isEmpty: {{ $semesters->isEmpty() ? 'true' : 'false' }} }">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill ph-list-dashes text-primary-dynamic"></i>
                Liste des semestres
            </h2>
            
            <div class="relative w-full sm:w-auto flex items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" placeholder="Rechercher..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[13px] rounded-lg pl-9 pr-3 py-2 outline-none focus:border-[#2F5F76] transition">
                </div>
            </div>
        </div>
        
        <!-- Empty State -->
        <div class="p-8" x-show="isEmpty">
            @include('SchoolDashboard::components.empty-state', [
                'title' => 'Aucun semestre trouvé',
                'description' => 'Aucun semestre n\'a encore été enregistré. Utilisez le formulaire pour en créer un nouveau.',
                'icon' => 'ph-fill ph-calendar-blank'
            ])
        </div>

        <div class="overflow-x-auto" x-show="!isEmpty" style="display: none;">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/80">
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 w-16">ID</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Nom</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Date de début</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Date de fin</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Année / Trimestre</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-center">Actuel</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($semesters as $semester)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-[13px] font-medium text-slate-500">{{ $semester->id }}</td>
                        <td class="px-5 py-4 text-[14px] font-bold text-slate-700">{{ $semester->name }}</td>
                        <td class="px-5 py-4 text-[13px] font-medium text-slate-600">{{ \Carbon\Carbon::parse($semester->start_date)->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-4 text-[13px] font-medium text-slate-600">{{ \Carbon\Carbon::parse($semester->end_date)->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-4 text-[13px] font-medium text-slate-600">
                            @if($semester->academic_year)
                                {{ $semester->academic_year }}{{ $semester->term_number ? ' · T' . $semester->term_number : '' }}
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($semester->is_current)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                Oui
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-500">
                                Non
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right flex justify-end gap-2">
                            <a href="?edit={{ $semester->id }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition inline-flex items-center justify-center" title="Éditer">
                                <i class="ph ph-pencil-simple text-[16px]"></i>
                            </a>
                            <form action="{{ route('school.academic.semesters.destroy', $semester->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce semestre ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition inline-flex items-center justify-center" title="Supprimer">
                                    <i class="ph ph-trash text-[16px]"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination (mock) -->
        <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between" x-show="!isEmpty" style="display: none;">
            <span class="text-[13px] font-medium text-slate-500">Affichage de {{ $semesters->count() }} semestre(s) au total</span>
        </div>
    </div>

</div>
@endsection
