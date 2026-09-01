@php
    $zone = $route->zone;
    $isEdit = $feeLevel !== null;
    $formAction = $isEdit
        ? route('school.finance.fees.config.update', $feeLevel->id)
        : route('school.finance.fees.config.store');
@endphp
<div x-show="feeOpen === {{ $route->id }}" x-cloak x-init="if ({{ $errors->any() && old('level') === $zone ? 'true' : 'false' }}) feeOpen = {{ $route->id }}" class="fixed inset-0 z-[9999] flex items-start justify-center overflow-y-auto bg-black/40 p-4 py-8" style="display: none;">
    <div @click.away="feeOpen = null" class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden my-auto">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-[18px] font-extrabold text-[#031C5B]">{{ $isEdit ? 'Modifier le Tarif' : 'Nouveau Tarif' }} — Zone {{ $zone }}</h3>
            <button type="button" @click="feeOpen = null" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
        </div>
        <form action="{{ $formAction }}" method="POST" class="p-6 space-y-4">
            @csrf
            @if($isEdit) @method('PUT') @endif
            @include('SchoolDashboard::finance._fee_level_fields', ['type' => 'transport', 'feeLevel' => $feeLevel, 'zone' => $zone])
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="feeOpen = null" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-xl hover:bg-slate-50 transition-all">Annuler</button>
                <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all">{{ $isEdit ? 'Enregistrer' : 'Créer' }}</button>
            </div>
        </form>
    </div>
</div>
