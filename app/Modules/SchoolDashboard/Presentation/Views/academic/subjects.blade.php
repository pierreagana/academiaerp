@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Gestion des Matières</h1>
            <p class="text-[13.5px] text-slate-500 mt-1">Gérez le catalogue de matières enseignées dans l'établissement.</p>
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

    <!-- Top Section: Add/Edit Subject Form -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill {{ isset($editSubject) ? 'ph-pencil-simple' : 'ph-plus-circle' }} text-primary-dynamic"></i>
                {{ isset($editSubject) ? 'Modifier la matière' : 'Ajouter une matière' }}
            </h2>
            @if(isset($editSubject))
            <a href="{{ route('school.academic.subjects') }}" class="text-[12px] font-medium text-slate-500 hover:text-slate-800 transition">Annuler l'édition</a>
            @endif
        </div>
        <div class="p-5">
            <form action="{{ isset($editSubject) ? route('school.academic.subjects.update', $editSubject->id) : route('school.academic.subjects.store') }}" method="POST">
                @csrf
                @if(isset($editSubject))
                    @method('PUT')
                @endif
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Nom -->
                    <div>
                        <label for="name" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Nom de la matière <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" required value="{{ old('name', $editSubject->name ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="Ex: Mathématiques Avancées">
                    </div>
                    
                    <!-- Code -->
                    <div>
                        <label for="code" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Code de la matière <span class="text-red-500">*</span></label>
                        <input type="text" id="code" name="code" required value="{{ old('code', $editSubject->code ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm uppercase"
                            placeholder="Ex: MATH101">
                    </div>

                    <!-- Langue -->
                    <div>
                        <label for="language_id" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Langue <span class="text-red-500">*</span></label>
                        <select id="language_id" name="language_id" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                            <option value="">Sélectionner une langue</option>
                            @foreach($languages as $language)
                            <option value="{{ $language->id }}" {{ old('language_id', $editSubject->language_id ?? '') == $language->id ? 'selected' : '' }}>{{ $language->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="type" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Type <span class="text-red-500">*</span></label>
                        <select id="type" name="type" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                            <option value="theory" {{ old('type', $editSubject->type ?? '') == 'theory' ? 'selected' : '' }}>Théorie</option>
                            <option value="practical" {{ old('type', $editSubject->type ?? '') == 'practical' ? 'selected' : '' }}>Pratique</option>
                            <option value="both" {{ old('type', $editSubject->type ?? '') == 'both' ? 'selected' : '' }}>Les deux (Théorie & Pratique)</option>
                        </select>
                    </div>

                    <!-- Coefficient -->
                    <div>
                        <label for="coefficient" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Coefficient <span class="text-red-500">*</span></label>
                        <input type="number" id="coefficient" name="coefficient" required min="0.5" step="0.5" value="{{ old('coefficient', $editSubject->coefficient ?? 1) }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="Ex: 2">
                    </div>

                    <!-- Couleur -->
                    <div>
                        <label for="color" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Couleur</label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="color" name="color" value="{{ old('color', $editSubject->color ?? '#2F5F76') }}"
                                class="h-11 w-11 rounded-lg cursor-pointer border-0 p-1 bg-[#FAFBFC] shadow-sm">
                            <span class="text-[12px] text-slate-500 font-medium">Choisissez une couleur</span>
                        </div>
                    </div>

                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] px-6 py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                        <i class="ph-bold ph-floppy-disk"></i>
                        {{ isset($editSubject) ? 'Mettre à jour' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bottom Section: Subjects List -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mt-8" x-data="{ isEmpty: {{ $subjects->isEmpty() ? 'true' : 'false' }} }">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill ph-list-dashes text-primary-dynamic"></i>
                Liste des matières
            </h2>
            
            <div class="relative w-full sm:w-auto flex items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" placeholder="Rechercher une matière..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[13px] rounded-lg pl-9 pr-3 py-2 outline-none focus:border-[#2F5F76] transition">
                </div>
            </div>
        </div>
        
        <!-- Empty State -->
        <div class="p-8" x-show="isEmpty">
            @include('SchoolDashboard::components.empty-state', [
                'title' => 'Aucune matière trouvée',
                'description' => 'Vous n\'avez pas encore ajouté de matière. Utilisez le formulaire ci-dessus pour enregistrer votre première matière.',
                'icon' => 'ph-fill ph-books'
            ])
        </div>

        <div class="overflow-x-auto" x-show="!isEmpty" style="display: none;">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">ID</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 w-12">Couleur</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Code</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Nom</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Langue</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Type</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Coef.</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($subjects as $subject)
                    <tr class="hover:bg-slate-50/50 transition group">
                        <td class="px-5 py-4 text-[13px] font-medium text-slate-500">{{ $subject->id }}</td>
                        <td class="px-5 py-4">
                            @if($subject->color)
                            <div class="w-6 h-6 rounded border border-slate-200 shadow-sm" style="background-color: {{ $subject->color }}"></div>
                            @else
                            <div class="w-6 h-6 rounded border border-slate-200 shadow-sm bg-slate-100"></div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-[13px] font-bold text-[#2F5F76] uppercase">{{ $subject->code }}</td>
                        <td class="px-5 py-4 text-[14px] font-bold text-slate-700">{{ $subject->name }}</td>
                        <td class="px-5 py-4 text-[13px] font-medium text-slate-600">{{ $subject->language ? $subject->language->name : '-' }}</td>
                        <td class="px-5 py-4">
                            @if($subject->type == 'theory')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-100">Théorie</span>
                            @elseif($subject->type == 'practical')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-green-50 text-green-700 border border-green-100">Pratique</span>
                            @else
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-100">Théorie</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-green-50 text-green-700 border border-green-100">Pratique</span>
                            </div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-[13px] font-bold text-slate-700">{{ $subject->coefficient }}</td>
                        <td class="px-5 py-4 text-right flex justify-end gap-2">
                            <a href="?edit={{ $subject->id }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition inline-flex items-center justify-center" title="Éditer">
                                <i class="ph ph-pencil-simple text-[16px]"></i>
                            </a>
                            <form action="{{ route('school.academic.subjects.destroy', $subject->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette matière ?');">
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
            <span class="text-[13px] font-medium text-slate-500">Affichage de {{ $subjects->count() }} matière(s) au total</span>
        </div>
    </div>

</div>
@endsection
