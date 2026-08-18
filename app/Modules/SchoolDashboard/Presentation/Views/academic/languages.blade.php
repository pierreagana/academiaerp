@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Gestion des Langues</h1>
            <p class="text-[13.5px] text-slate-500 mt-1">Ajoutez et gérez les langues enseignées ou parlées dans l'établissement.</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Add/Edit Language Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                        <i class="ph-fill {{ isset($editLanguage) ? 'ph-pencil-simple' : 'ph-plus-circle' }} text-primary-dynamic"></i>
                        {{ isset($editLanguage) ? 'Modifier la langue' : 'Ajouter une langue' }}
                    </h2>
                    @if(isset($editLanguage))
                    <a href="{{ route('school.academic.languages') }}" class="text-[12px] font-medium text-slate-500 hover:text-slate-800 transition">Annuler</a>
                    @endif
                </div>
                <div class="p-5">
                    <form action="{{ isset($editLanguage) ? route('school.academic.languages.update', $editLanguage->id) : route('school.academic.languages.store') }}" method="POST" class="space-y-4">
                        @csrf
                        @if(isset($editLanguage))
                            @method('PUT')
                        @endif
                        <div>
                            <label for="name" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Nom de la langue <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" required value="{{ old('name', $editLanguage->name ?? '') }}"
                                class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                placeholder="Ex: Anglais">
                        </div>
                        
                        <div>
                            <label for="code" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Code (ex: FR, EN) <span class="text-red-500">*</span></label>
                            <input type="text" id="code" name="code" required value="{{ old('code', $editLanguage->code ?? '') }}" maxlength="10"
                                class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm uppercase"
                                placeholder="Ex: EN">
                        </div>
                        
                        <div class="pt-2">
                            <button type="submit" class="w-full bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                                <i class="ph-bold ph-floppy-disk"></i>
                                {{ isset($editLanguage) ? 'Mettre à jour' : 'Enregistrer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Languages List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-[15px] font-bold text-slate-800">Liste des langues</h2>
                    
                    <!-- Optional: Search bar -->
                    <div class="relative w-48">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" placeholder="Rechercher..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[12px] rounded-full pl-9 pr-3 py-1.5 outline-none focus:border-[#2F5F76]">
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">ID</th>
                                <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Nom de la langue</th>
                                <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($languages as $language)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-5 py-3.5 text-[13px] font-medium text-slate-500">{{ $language->id }}</td>
                                <td class="px-5 py-3.5 text-[14px] font-bold text-slate-700">
                                    {{ $language->name }}
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 uppercase">{{ $language->code }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-right flex justify-end gap-2">
                                    <a href="?edit={{ $language->id }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition inline-flex items-center justify-center" title="Modifier">
                                        <i class="ph ph-pencil-simple text-[16px]"></i>
                                    </a>
                                    <form action="{{ route('school.academic.languages.destroy', $language->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette langue ?');">
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
                <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[12px] font-medium text-slate-500">Affichage de {{ $languages->count() }} langue(s) au total</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
