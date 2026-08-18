@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Succursales</h1>
        <p class="text-[13.5px] text-slate-500 mt-1">Gérez les différentes succursales de votre établissement (ex : Lycée, Collège, Primaire, Préscolaire, ou un campus dans une autre ville). Chaque succursale a ses propres classes, élèves, enseignants et personnel.</p>
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
            <span class="font-bold">Il y a des erreurs :</span>
        </div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Add/Edit form -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill {{ isset($editBranch) ? 'ph-pencil-simple' : 'ph-plus-circle' }} text-primary-dynamic"></i>
                {{ isset($editBranch) ? 'Modifier la succursale' : 'Ajouter une succursale' }}
            </h2>
            @if(isset($editBranch))
            <a href="{{ route('school.branches') }}" class="text-[12px] font-medium text-slate-500 hover:text-slate-800 transition">Annuler l'édition</a>
            @endif
        </div>
        <div class="p-5">
            <form action="{{ isset($editBranch) ? route('school.branches.update', $editBranch->id) : route('school.branches.store') }}" method="POST">
                @csrf
                @if(isset($editBranch))
                    @method('PUT')
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Nom de la succursale <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" required value="{{ old('name', $editBranch->name ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="Ex : Lycée, Collège, Campus Abidjan...">
                    </div>
                    <div>
                        <label for="type" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Type (optionnel)</label>
                        <input type="text" id="type" name="type" value="{{ old('type', $editBranch->type ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="Ex : Lycée, Collège, Primaire, Préscolaire...">
                    </div>
                    <div>
                        <label for="city" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Ville (optionnel)</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $editBranch->city ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="Ex : Dakar, Abidjan...">
                    </div>
                    <div>
                        <label for="country" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Pays (optionnel)</label>
                        <input type="text" id="country" name="country" value="{{ old('country', $editBranch->country ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="Ex : Sénégal, Côte d'Ivoire...">
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] px-6 py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                        <i class="ph-bold ph-floppy-disk"></i>
                        {{ isset($editBranch) ? 'Mettre à jour' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill ph-buildings text-primary-dynamic"></i>
                Liste des succursales
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Nom</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Type</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Localisation</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Statut</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($branches as $branch)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-[14px] font-bold text-slate-700">{{ $branch->name }}</td>
                        <td class="px-5 py-4 text-[13px] text-slate-600">{{ $branch->type ?: '-' }}</td>
                        <td class="px-5 py-4 text-[13px] text-slate-600">{{ collect([$branch->city, $branch->country])->filter()->implode(', ') ?: '-' }}</td>
                        <td class="px-5 py-4">
                            @if($branch->is_main)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100">Principale</span>
                            @else
                                <form action="{{ route('school.branches.set-main', $branch->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-bold text-slate-400 hover:text-blue-600 transition">Définir comme principale</button>
                                </form>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right flex justify-end gap-2">
                            <a href="?edit={{ $branch->id }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition inline-flex items-center justify-center" title="Éditer">
                                <i class="ph ph-pencil-simple text-[16px]"></i>
                            </a>
                            @unless($branch->is_main)
                            <form action="{{ route('school.branches.destroy', $branch->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette succursale ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition inline-flex items-center justify-center" title="Supprimer">
                                    <i class="ph ph-trash text-[16px]"></i>
                                </button>
                            </form>
                            @endunless
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-500 text-[13px]">Aucune succursale trouvée.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100">
            <span class="text-[13px] font-medium text-slate-500">{{ $branches->count() }} succursale(s) au total</span>
        </div>
    </div>

</div>
@endsection
