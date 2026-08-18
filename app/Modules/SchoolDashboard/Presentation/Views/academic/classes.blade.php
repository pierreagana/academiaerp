@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Gestion des Classes</h1>
            <p class="text-[13.5px] text-slate-500 mt-1">Ajoutez et gérez les classes de votre établissement.</p>
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

    <!-- Top Section: Add/Edit Class Form -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill {{ isset($editClass) ? 'ph-pencil-simple' : 'ph-plus-circle' }} text-primary-dynamic"></i>
                {{ isset($editClass) ? 'Modifier la classe' : 'Ajouter une classe' }}
            </h2>
            @if(isset($editClass))
            <a href="{{ route('school.academic.classes') }}" class="text-[12px] font-medium text-slate-500 hover:text-slate-800 transition">Annuler l'édition</a>
            @endif
        </div>
        <div class="p-5">
            <form action="{{ isset($editClass) ? route('school.academic.classes.update', $editClass->id) : route('school.academic.classes.store') }}" method="POST">
                @csrf
                @if(isset($editClass))
                    @method('PUT')
                @endif
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Nom -->
                    <div>
                        <label for="name" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Nom de la classe <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" required value="{{ old('name', $editClass->name ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="Ex: 6ème A">
                    </div>

                    <!-- Niveau -->
                    <div>
                        <label for="level" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Niveau <span class="text-red-500">*</span></label>
                        <input type="text" id="level" name="level" required value="{{ old('level', $editClass->level ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="Ex: 6ème, 5ème, CP...">
                        <p class="text-[11.5px] text-slate-400 mt-1">Utilisé pour le transfert (même niveau) et la promotion (niveau différent) des élèves.</p>
                    </div>

                    <!-- Cycle -->
                    <div>
                        <label for="cycle" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Cycle</label>
                        <select id="cycle" name="cycle"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm">
                            <option value="">Non défini</option>
                            @foreach(['Cycle 1', 'Cycle 2', 'Cycle 3'] as $cycleOption)
                                <option value="{{ $cycleOption }}" {{ old('cycle', $editClass->cycle ?? '') === $cycleOption ? 'selected' : '' }}>{{ $cycleOption }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11.5px] text-slate-400 mt-1">Utilisé par le Livret Scolaire pour rattacher le bon référentiel de compétences.</p>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] px-6 py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                        <i class="ph-bold ph-floppy-disk"></i>
                        {{ isset($editClass) ? 'Mettre à jour' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bottom Section: Classes List -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mt-8" x-data="{ isEmpty: {{ $classes->isEmpty() ? 'true' : 'false' }} }">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill ph-list-dashes text-primary-dynamic"></i>
                Liste des classes
            </h2>
            
            <div class="relative w-full sm:w-auto flex items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" placeholder="Rechercher une classe..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[13px] rounded-lg pl-9 pr-3 py-2 outline-none focus:border-[#2F5F76] transition">
                </div>
            </div>
        </div>
        
        <!-- Empty State -->
        <div class="p-8" x-show="isEmpty">
            @include('SchoolDashboard::components.empty-state', [
                'title' => 'Aucune classe trouvée',
                'description' => 'Vous n\'avez pas encore ajouté de classes. Utilisez le formulaire ci-dessus pour créer votre première classe.',
                'icon' => 'ph-fill ph-users-three'
            ])
        </div>

        <div class="overflow-x-auto" x-show="!isEmpty" style="display: none;">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">ID</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Nom de la classe</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Niveau</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($classes as $class)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-[13px] font-medium text-slate-500">{{ $class->id }}</td>
                        <td class="px-5 py-4 text-[14px] font-bold text-slate-700">{{ $class->name }}</td>
                        <td class="px-5 py-4">
                            @if($class->level)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">{{ $class->level }}</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-orange-50 text-orange-600 border border-orange-100">Non défini</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right flex justify-end gap-2">
                            <a href="?edit={{ $class->id }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition inline-flex items-center justify-center" title="Éditer">
                                <i class="ph ph-pencil-simple text-[16px]"></i>
                            </a>
                            <form action="{{ route('school.academic.classes.destroy', $class->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette classe ?');">
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
            <span class="text-[13px] font-medium text-slate-500">Affichage de {{ $classes->count() }} classe(s) au total</span>
        </div>
    </div>

</div>
@endsection
