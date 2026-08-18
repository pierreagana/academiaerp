@php
    $defaultYear = old('academic_year', $feeLevel->academic_year ?? (now()->month >= 8 ? now()->year . '-' . (now()->year + 1) : (now()->year - 1) . '-' . now()->year));
@endphp

<input type="hidden" name="type" value="{{ $type }}">

@if($type === 'tuition')
<div class="space-y-1.5">
    <label class="block text-[13px] font-bold text-slate-700">Niveau <span class="text-red-500">*</span></label>
    <input type="text" name="level" list="fee-level-options" value="{{ old('level', $feeLevel->level ?? '') }}" placeholder="Ex: Terminale, 6ème" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
    <datalist id="fee-level-options">
        @foreach($levels ?? [] as $level)
            <option value="{{ $level }}"></option>
        @endforeach
    </datalist>
</div>
@else
<div class="text-[13px] font-semibold text-slate-600 bg-slate-50 rounded-xl px-4 py-3">
    Tarif unique {{ \App\Modules\Finance\Domain\Models\FeeLevel::TYPES[$type] }} pour toute l'école (non spécifique à un niveau).
</div>
@endif

<div class="space-y-1.5">
    <label class="block text-[13px] font-bold text-slate-700">Année Académique <span class="text-red-500">*</span></label>
    <input type="text" name="academic_year" value="{{ $defaultYear }}" placeholder="Ex: 2025-2026" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
</div>

<div class="grid grid-cols-2 gap-4">
    <div class="space-y-1.5">
        <label class="block text-[13px] font-bold text-slate-700">Frais d'inscription (FCFA) <span class="text-red-500">*</span></label>
        <input type="number" name="registration_fee" min="0" step="1" value="{{ old('registration_fee', $feeLevel->registration_fee ?? '') }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
    </div>
    <div class="space-y-1.5">
        <label class="block text-[13px] font-bold text-slate-700">Mensualité (FCFA) <span class="text-red-500">*</span></label>
        <input type="number" name="monthly_fee" min="0" step="1" value="{{ old('monthly_fee', $feeLevel->monthly_fee ?? '') }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div class="space-y-1.5">
        <label class="block text-[13px] font-bold text-slate-700">Nombre de mensualités <span class="text-red-500">*</span></label>
        <input type="number" name="installments_count" min="0" max="12" value="{{ old('installments_count', $feeLevel->installments_count ?? 9) }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
    </div>
    <div class="space-y-1.5">
        <label class="block text-[13px] font-bold text-slate-700">1ère échéance (Inscription) <span class="text-red-500">*</span></label>
        <input type="date" name="start_date" value="{{ old('start_date', isset($feeLevel) ? $feeLevel->start_date->format('Y-m-d') : '') }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
    </div>
</div>

@if($errors->any())
<div class="text-[13px] text-red-600 font-medium space-y-1">
    @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
    @endforeach
</div>
@endif
