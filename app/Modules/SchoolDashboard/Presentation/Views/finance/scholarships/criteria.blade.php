@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::finance.scholarships._tabs')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Critères d'Éligibilité</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Définissez les seuils requis pour la présélection des candidats aux différentes bourses.</p>
        </div>
        <div x-data="{ open: {{ $errors->any() && !old('_edit_id') ? 'true' : 'false' }} }">
            <button @click="open = true" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
                <i class="ph-bold ph-plus text-lg"></i>
                Nouveau Type de Bourse
            </button>

            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
                <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden max-h-[90vh] overflow-y-auto">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-[18px] font-extrabold text-[#031C5B]">Nouveau Type de Bourse</h3>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
                    </div>
                    <form action="{{ route('school.finance.scholarships.criteria.store') }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        @include('SchoolDashboard::finance.scholarships._type_fields')
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-xl hover:bg-slate-50 transition-all">Annuler</button>
                            <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all">Créer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($types as $type)
        <div x-data="{ open: {{ (string) old('_edit_id') === (string) $type->id ? 'true' : 'false' }} }" class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <h3 class="text-xl font-bold text-slate-900">{{ $type->name }}</h3>
            <div class="mt-2">
                <span class="text-2xl font-extrabold text-[#031C5B]">{{ number_format($type->default_monthly_amount, 0, ',', ' ') }}</span>
                <span class="text-[13px] font-semibold text-slate-500">FCFA / mois</span>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 space-y-1.5 text-[13px]">
                @if($type->min_average)
                    <div class="flex justify-between"><span class="text-slate-500">Moyenne minimale:</span><span class="font-semibold text-slate-700">{{ number_format($type->min_average, 1) }}/20</span></div>
                @endif
                @if($type->min_attendance_rate)
                    <div class="flex justify-between"><span class="text-slate-500">Assiduité minimum:</span><span class="font-semibold text-slate-700">{{ $type->min_attendance_rate }}%</span></div>
                @endif
                @if($type->max_family_income)
                    <div class="flex justify-between"><span class="text-slate-500">Revenu familial max:</span><span class="font-semibold text-slate-700">{{ number_format($type->max_family_income, 0, ',', ' ') }} FCFA</span></div>
                @endif
                @if($type->min_competition_level)
                    <div class="flex justify-between"><span class="text-slate-500">Niveau requis:</span><span class="font-semibold text-slate-700">{{ $type->min_competition_level }}</span></div>
                @endif
            </div>

            @if(!empty($type->requiredDocumentLabels()))
            <div class="mt-4 flex flex-wrap gap-1.5">
                @foreach($type->requiredDocumentLabels() as $doc)
                    <span class="px-2 py-1 bg-slate-100 text-slate-600 text-[11px] font-semibold rounded">{{ $doc }}</span>
                @endforeach
            </div>
            @endif

            <div class="mt-5 flex gap-2">
                <button @click="open = true" class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[13px] rounded-lg transition">Modifier les critères</button>
                <form action="{{ route('school.finance.scholarships.criteria.destroy', $type->id) }}" method="POST" onsubmit="return confirm('Supprimer ce type de bourse ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-2.5 bg-white border border-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200 text-slate-500 rounded-lg transition">
                        <i class="ph-bold ph-trash"></i>
                    </button>
                </form>
            </div>

            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
                <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden max-h-[90vh] overflow-y-auto">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-[18px] font-extrabold text-[#031C5B]">Modifier — {{ $type->name }}</h3>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
                    </div>
                    <form action="{{ route('school.finance.scholarships.criteria.update', $type->id) }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_edit_id" value="{{ $type->id }}">
                        @include('SchoolDashboard::finance.scholarships._type_fields', ['type' => $type])
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-xl hover:bg-slate-50 transition-all">Annuler</button>
                            <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="lg:col-span-3 bg-white rounded-2xl p-10 border border-slate-100 shadow-sm text-center text-slate-500">
            Aucun type de bourse configuré. Cliquez sur "Nouveau Type de Bourse" pour commencer.
        </div>
        @endforelse
    </div>
</div>
@endsection
