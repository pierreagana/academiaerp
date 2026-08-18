@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Modèle de Bulletin</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Configurez les éléments affichés sur les bulletins générés.</p>
        </div>
        @if($previewStudent)
            <a href="{{ route('school.academic.bulletins.print', $previewStudent->id) }}" target="_blank"
               class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition">
                <i class="ph-bold ph-eye text-lg"></i> Aperçu ({{ $previewStudent->first_name }} {{ $previewStudent->last_name }})
            </a>
        @else
            <span class="text-[12.5px] text-slate-400 italic">Aperçu indisponible — aucun élève actif dans cette école.</span>
        @endif
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <form action="{{ route('school.academic.bulletins.template.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Nom du modèle</label>
            <input type="text" name="name" value="{{ old('name', $template->name) }}" required class="w-full max-w-md bg-slate-50 border border-slate-200 text-slate-900 text-[14px] font-medium rounded-xl px-4 py-3 outline-none focus:border-[#031C5B]">
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-[15px] font-extrabold text-slate-900 mb-4">Colonnes à Afficher</h3>
            <div class="space-y-3">
                @foreach([
                    'show_coefficient' => 'Coefficient',
                    'show_class_average' => 'Rang par Matière',
                    'show_highest_lowest' => 'Meilleure / Plus Faible Note',
                    'show_ranking' => 'Classement',
                ] as $field => $label)
                <label class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-200 hover:bg-slate-50/60 cursor-pointer transition">
                    <span class="text-[13.5px] font-semibold text-slate-700">{{ $label }}</span>
                    <input type="checkbox" name="{{ $field }}" value="1" {{ old($field, $template->$field) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]/20">
                </label>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-[15px] font-extrabold text-slate-900 mb-4">Autres Éléments</h3>
            <label class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-200 hover:bg-slate-50/60 cursor-pointer transition">
                <span class="text-[13.5px] font-semibold text-slate-700">Zone de Signature (Professeur Principal / Directeur)</span>
                <input type="checkbox" name="show_signature_area" value="1" {{ old('show_signature_area', $template->show_signature_area) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]/20">
            </label>
        </div>

        <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-6">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="suggested_remarks_enabled" value="1" {{ old('suggested_remarks_enabled', $template->suggested_remarks_enabled) ? 'checked' : '' }}
                    class="mt-1 w-4 h-4 rounded border-blue-300 text-[#031C5B] focus:ring-[#031C5B]/20">
                <span>
                    <span class="block text-[13.5px] font-bold text-slate-800">Appréciations Suggérées par Barème</span>
                    <span class="block text-[12px] text-slate-500 mt-1">Si aucune appréciation n'a été saisie pour une note, le bulletin imprimé affiche une appréciation générée selon un barème fixe (≥16 Excellent, ≥14 Bien, ≥12 Assez bien, ≥10 Passable, &lt;10 Insuffisant) — pas de génération par intelligence artificielle, un barème déterministe et modifiable à tout moment lors de la saisie.</span>
                </span>
            </label>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all shadow-sm flex items-center gap-2">
                <i class="ph-bold ph-check"></i> Enregistrer le Modèle
            </button>
        </div>
    </form>
</div>
@endsection
