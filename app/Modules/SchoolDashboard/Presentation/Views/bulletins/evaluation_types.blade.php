@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('school.academic.bulletins.grades') }}" class="text-[12.5px] font-bold text-slate-500 hover:text-[#031C5B] inline-flex items-center gap-1 mb-2">
                <i class="ph-bold ph-arrow-left"></i> Saisie des Notes
            </a>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Types d'Évaluation & Coefficients</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">
                @if(auth()->user()->teacher)
                    Consultez les types d'évaluation (Interrogation, Devoir surveillé, Composition...) et leur coefficient. Seul un administrateur peut les modifier.
                @else
                    Configurez les types d'évaluation (Interrogation, Devoir surveillé, Composition...) et leur coefficient. La moyenne de chaque matière est calculée automatiquement à partir de ces coefficients.
                @endif
            </p>
        </div>
        @if(!auth()->user()->teacher)
        <div x-data="{ open: {{ $errors->any() && !old('_edit_id') ? 'true' : 'false' }} }">
            <button @click="open = true" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
                <i class="ph-bold ph-plus text-lg"></i>
                Nouveau Type
            </button>

            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
                <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-[18px] font-extrabold text-[#031C5B]">Nouveau Type d'Évaluation</h3>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
                    </div>
                    <form action="{{ route('school.academic.bulletins.evaluation-types.store') }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Nom <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="Ex: Interrogation" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Coefficient <span class="text-red-500">*</span></label>
                            <input type="number" name="coefficient" required step="0.25" min="0.25" max="10" value="1" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Source des notes</label>
                            <select name="linked_homework_type" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] appearance-none">
                                <option value="">Saisie manuelle ici</option>
                                <option value="devoir_maison">Automatique — Devoirs Maison (module Devoirs & Interrogations)</option>
                                <option value="interrogation">Automatique — Interrogations en direct (module Devoirs & Interrogations)</option>
                            </select>
                            <p class="text-[11.5px] text-slate-400 mt-1">Si lié, les notes viennent automatiquement du module Devoirs & Interrogations — plus de saisie manuelle possible ici pour ce type.</p>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-xl hover:bg-slate-50 transition-all">Annuler</button>
                            <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all">Créer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 mb-4 text-sm text-red-800 rounded-xl bg-red-50 border border-red-100" role="alert">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($types as $type)
        <div x-data="{ open: {{ (string) old('_edit_id') === (string) $type->id ? 'true' : 'false' }} }" class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <h3 class="text-xl font-bold text-slate-900">{{ $type->name }}</h3>
            <div class="mt-2">
                <span class="text-2xl font-extrabold text-[#031C5B]">{{ rtrim(rtrim(number_format($type->coefficient, 2), '0'), '.') }}</span>
                <span class="text-[13px] font-semibold text-slate-500">coefficient</span>
            </div>
            @if($type->linked_homework_type)
                <span class="inline-flex items-center gap-1 mt-3 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700">
                    <i class="ph-bold ph-link"></i>
                    Auto — {{ $type->linked_homework_type === 'devoir_maison' ? 'Devoirs Maison' : 'Interrogations' }}
                </span>
            @endif

            @if(!auth()->user()->teacher)
            <div class="mt-5 flex gap-2">
                <button @click="open = true" class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[13px] rounded-lg transition">Modifier</button>
                <form action="{{ route('school.academic.bulletins.evaluation-types.destroy', $type->id) }}" method="POST" onsubmit="return confirm('Supprimer ce type d\'évaluation ? Les notes déjà saisies pour ce type resteront en base mais ne seront plus modifiables depuis la grille de saisie.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-2.5 bg-white border border-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200 text-slate-500 rounded-lg transition">
                        <i class="ph-bold ph-trash"></i>
                    </button>
                </form>
            </div>

            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
                <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-[18px] font-extrabold text-[#031C5B]">Modifier — {{ $type->name }}</h3>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
                    </div>
                    <form action="{{ route('school.academic.bulletins.evaluation-types.update', $type->id) }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_edit_id" value="{{ $type->id }}">
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Nom <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required value="{{ $type->name }}" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Coefficient <span class="text-red-500">*</span></label>
                            <input type="number" name="coefficient" required step="0.25" min="0.25" max="10" value="{{ $type->coefficient }}" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Source des notes</label>
                            <select name="linked_homework_type" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] appearance-none">
                                <option value="" {{ !$type->linked_homework_type ? 'selected' : '' }}>Saisie manuelle ici</option>
                                <option value="devoir_maison" {{ $type->linked_homework_type === 'devoir_maison' ? 'selected' : '' }}>Automatique — Devoirs Maison (module Devoirs & Interrogations)</option>
                                <option value="interrogation" {{ $type->linked_homework_type === 'interrogation' ? 'selected' : '' }}>Automatique — Interrogations en direct (module Devoirs & Interrogations)</option>
                            </select>
                            <p class="text-[11.5px] text-slate-400 mt-1">Si lié, les notes viennent automatiquement du module Devoirs & Interrogations — plus de saisie manuelle possible ici pour ce type.</p>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-xl hover:bg-slate-50 transition-all">Annuler</button>
                            <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="lg:col-span-3 bg-white rounded-2xl p-10 border border-slate-100 shadow-sm text-center text-slate-500">
            Aucun type d'évaluation configuré. Cliquez sur "Nouveau Type" pour commencer (ex: Interrogation, Devoir surveillé, Composition).
        </div>
        @endforelse
    </div>
</div>
@endsection
