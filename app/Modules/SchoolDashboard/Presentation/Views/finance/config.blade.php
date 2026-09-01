@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::finance._tabs')
    @include('SchoolDashboard::finance._fee_type_tabs')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Configuration des Frais — {{ \App\Modules\Finance\Domain\Models\FeeLevel::TYPES[$type] }}</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">
                @if($type === 'tuition')
                    Gérez et définissez les structures tarifaires par niveau pour l'année académique.
                @else
                    Définissez le tarif et les modalités de paiement {{ strtolower(\App\Modules\Finance\Domain\Models\FeeLevel::TYPES[$type]) }} de l'école pour l'année académique.
                @endif
            </p>
        </div>
        <div x-data="{ open: {{ $errors->any() && !old('_edit_id') ? 'true' : 'false' }} }">
            <button @click="open = true" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
                <i class="ph-bold ph-plus text-lg"></i>
                Nouveau Type de Frais
            </button>

            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4 py-8" style="display: none;">
                <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden my-auto">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-[18px] font-extrabold text-[#031C5B]">Nouvelle Structure Tarifaire</h3>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
                    </div>
                    <form action="{{ route('school.finance.fees.config.store') }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        @include('SchoolDashboard::finance._fee_level_fields', ['zones' => $zones ?? []])
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
    @if(session('error'))
    <div class="p-4 mb-4 text-sm text-red-800 rounded-xl bg-red-50 flex items-center gap-2 border border-red-100" role="alert">
        <i class="ph-fill ph-warning-circle text-lg"></i>
        <span class="font-bold">{{ session('error') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($feeLevels as $feeLevel)
        <div x-data="{ open: {{ (string) old('_edit_id') === (string) $feeLevel->id ? 'true' : 'false' }} }" class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <div class="flex items-start justify-between mb-3">
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[11px] font-bold rounded uppercase tracking-wider">{{ $feeLevel->academic_year }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900">{{ in_array($type, ['tuition', 'transport'], true) ? $feeLevel->level : \App\Modules\Finance\Domain\Models\FeeLevel::TYPES[$type] }}</h3>
            <div class="mt-3">
                <span class="text-2xl font-extrabold text-[#031C5B]">{{ number_format($feeLevel->total_amount, 0, ',', ' ') }}</span>
                <span class="text-[13px] font-semibold text-slate-500">FCFA</span>
            </div>
            <p class="text-[12px] text-slate-500">{{ $type === 'tuition' ? 'Scolarité annuelle totale' : 'Total annuel' }}</p>

            <div class="mt-4 pt-4 border-t border-slate-100 space-y-1.5 text-[13px]">
                <div class="flex justify-between"><span class="text-slate-500">Inscription:</span><span class="font-semibold text-slate-700">{{ number_format($feeLevel->registration_fee, 0, ',', ' ') }} FCFA</span></div>
                @if(!empty($feeLevel->monthly_amounts))
                <div class="flex justify-between"><span class="text-slate-500">Mensualités (x{{ $feeLevel->installments_count }}):</span><span class="font-semibold text-slate-700">Personnalisées</span></div>
                @else
                <div class="flex justify-between"><span class="text-slate-500">Mensualité (x{{ $feeLevel->installments_count }}):</span><span class="font-semibold text-slate-700">{{ number_format($feeLevel->monthly_fee, 0, ',', ' ') }} FCFA</span></div>
                @endif
            </div>

            <div class="mt-5 flex gap-2">
                <button @click="open = true" class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[13px] rounded-lg transition">Modifier la structure</button>
                <form action="{{ route('school.finance.fees.config.destroy', $feeLevel->id) }}?type={{ $type }}" method="POST" onsubmit="return confirm('Supprimer cette structure tarifaire ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-2.5 bg-white border border-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200 text-slate-500 rounded-lg transition">
                        <i class="ph-bold ph-trash"></i>
                    </button>
                </form>
            </div>

            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4 py-8" style="display: none;">
                <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden my-auto">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-[18px] font-extrabold text-[#031C5B]">Modifier la Structure — {{ in_array($type, ['tuition', 'transport'], true) ? $feeLevel->level : \App\Modules\Finance\Domain\Models\FeeLevel::TYPES[$type] }}</h3>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
                    </div>
                    <form action="{{ route('school.finance.fees.config.update', $feeLevel->id) }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_edit_id" value="{{ $feeLevel->id }}">
                        @include('SchoolDashboard::finance._fee_level_fields', ['feeLevel' => $feeLevel, 'zones' => $zones ?? []])
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
            Aucune structure tarifaire configurée. Cliquez sur "Nouveau Type de Frais" pour commencer.
        </div>
        @endforelse
    </div>
</div>
@endsection
