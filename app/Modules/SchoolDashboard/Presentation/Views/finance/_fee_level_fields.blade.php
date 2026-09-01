@php
    $defaultYear = old('academic_year', $feeLevel->academic_year ?? (now()->month >= 8 ? now()->year . '-' . (now()->year + 1) : (now()->year - 1) . '-' . now()->year));
@endphp

<input type="hidden" name="type" value="{{ $type }}">

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
@if($type === 'tuition')
<div class="space-y-1.5">
    <label class="block text-[13px] font-bold text-slate-700">Niveau <span class="text-red-500">*</span></label>

    @if(isset($feeLevel))
        <input type="text" name="level" list="fee-level-options" value="{{ old('level', $feeLevel->level) }}" placeholder="Ex: Terminale, 6ème" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
        <datalist id="fee-level-options">
            @foreach($levels ?? [] as $level)
                <option value="{{ $level }}"></option>
            @endforeach
        </datalist>
    @else
        @php $selectedLevels = old('level', []); @endphp
        <div x-data="{ open: false, checked: {{ json_encode($selectedLevels) }} }" class="relative" @click.outside="open = false">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                <span x-text="checked.length ? checked.length + ' niveau(x) sélectionné(s)' : 'Sélectionner un ou plusieurs niveaux'" class="text-slate-600 truncate"></span>
                <i class="ph-bold ph-caret-down text-[14px] text-slate-400 transition-transform shrink-0" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" style="display:none;" class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                @forelse($levels ?? [] as $level)
                <label class="flex items-center gap-2.5 px-4 py-2 hover:bg-slate-50 cursor-pointer text-[13.5px] text-slate-700">
                    <input type="checkbox" name="level[]" value="{{ $level }}" x-model="checked" class="w-4 h-4 rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]/20">
                    {{ $level }}
                </label>
                @empty
                <p class="px-4 py-2 text-[13px] text-slate-400">Aucun niveau de classe trouvé — créez d'abord des classes.</p>
                @endforelse
            </div>
        </div>
    @endif
</div>
@elseif($type === 'transport')
<div class="space-y-1.5">
    <label class="block text-[13px] font-bold text-slate-700">Zone <span class="text-red-500">*</span></label>
    @if(isset($zone))
        <div class="w-full bg-slate-100 border border-slate-200 text-slate-700 text-[14px] font-semibold rounded-lg px-4 py-2.5">{{ $zone }}</div>
        <input type="hidden" name="level" value="{{ $zone }}">
    @else
        <input type="text" name="level" list="fee-zone-options" value="{{ old('level', $feeLevel->level ?? '') }}" placeholder="Ex: zone1" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
        <datalist id="fee-zone-options">
            @foreach($zones ?? [] as $zoneOption)
                <option value="{{ $zoneOption }}"></option>
            @endforeach
        </datalist>
    @endif
    <p class="text-[11.5px] text-slate-400">Les routes d'une même zone partagent ce tarif — c'est ce que verra le parent avant de choisir un arrêt.</p>
</div>
@else
<div class="text-[13px] font-semibold text-slate-600 bg-slate-50 rounded-xl px-4 py-3 flex items-center">
    Tarif unique {{ \App\Modules\Finance\Domain\Models\FeeLevel::TYPES[$type] }} pour toute l'école (non spécifique à un niveau).
</div>
@endif

<div class="space-y-1.5">
    <label class="block text-[13px] font-bold text-slate-700">Année Académique <span class="text-red-500">*</span></label>
    <input type="text" name="academic_year" value="{{ $defaultYear }}" placeholder="Ex: 2025-2026" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
</div>
</div>

@php
    $existingAmounts = old('monthly_amounts', $feeLevel->monthly_amounts ?? []);
    $hasCustomAmounts = !empty($existingAmounts);
    $initialTotalScolarite = $hasCustomAmounts
        ? array_sum($existingAmounts)
        : (float) ($feeLevel->monthly_fee ?? 0) * (int) ($feeLevel->installments_count ?? 9);
@endphp
<div x-data="{
        custom: {{ $hasCustomAmounts ? 'true' : 'false' }},
        installmentsCount: {{ (int) old('installments_count', $feeLevel->installments_count ?? 9) }},
        totalScolarite: {{ (float) old('total_scolarite', $initialTotalScolarite) }},
        registrationFee: {{ (float) old('registration_fee', $feeLevel->registration_fee ?? 0) }},
        amounts: {{ json_encode(array_values(array_map('floatval', $existingAmounts))) }},
        get flatMonthly() { return this.installmentsCount > 0 ? this.totalScolarite / this.installmentsCount : 0; },
        get total() { return this.amounts.slice(0, this.installmentsCount).reduce((a, b) => a + (parseFloat(b) || 0), 0); },
        get grandCeiling() { return this.totalScolarite + this.registrationFee; },
        get grandTotal() { return this.total + this.registrationFee; },
        syncAmountsLength() {
            const n = Math.max(0, this.installmentsCount || 0);
            while (this.amounts.length < n) this.amounts.push(0);
            this.amounts.length = n;
        }
    }"
    x-init="syncAmountsLength()"
>
    <div class="space-y-1.5">
        <label class="block text-[13px] font-bold text-slate-700">Scolarité (Somme Totale, FCFA) <span class="text-red-500">*</span></label>
        <input type="number" name="total_scolarite" min="0" step="1" x-model.number="totalScolarite" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
        <p class="text-[11.5px] text-slate-400">Total des mensualités uniquement — les frais d'inscription sont un montant séparé, ci-dessous.</p>
    </div>

    <div class="grid grid-cols-2 gap-4 mt-4">
        <div class="space-y-1.5">
            <label class="block text-[13px] font-bold text-slate-700">Frais d'inscription (FCFA) <span class="text-red-500">*</span></label>
            <input type="number" name="registration_fee" min="0" step="1" x-model.number="registrationFee" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
        </div>
        <div class="space-y-1.5" x-show="!custom">
            <label class="block text-[13px] font-bold text-slate-700">Mensualité (FCFA)</label>
            <div class="w-full bg-slate-100 border border-slate-200 text-slate-500 text-[14px] rounded-lg px-4 py-2.5" x-text="flatMonthly.toLocaleString('fr-FR', {maximumFractionDigits: 2}) + ' FCFA / mois'"></div>
            <p class="text-[11.5px] text-slate-400">Calculée automatiquement : Scolarité ÷ Nombre de mensualités.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mt-4">
        <div class="space-y-1.5">
            <label class="block text-[13px] font-bold text-slate-700">Nombre de mensualités <span class="text-red-500">*</span></label>
            <input type="number" name="installments_count" min="0" max="12" x-model.number="installmentsCount" @input="syncAmountsLength()" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
        </div>
        <div class="space-y-1.5">
            <label class="block text-[13px] font-bold text-slate-700">1ère échéance (Inscription) <span class="text-red-500">*</span></label>
            <input type="date" name="start_date" value="{{ old('start_date', isset($feeLevel) ? $feeLevel->start_date->format('Y-m-d') : '') }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
            <p class="text-[11.5px] text-slate-400">La 1ère échéance regroupe l'inscription + le 1er versement. Les échéances suivantes sont espacées d'un mois à partir de cette date.</p>
        </div>
    </div>

    <div class="mt-4">
        <label class="flex items-center gap-2 text-[13px] font-semibold text-slate-700 cursor-pointer">
            <input type="checkbox" x-model="custom" class="w-4 h-4 rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]/20">
            Répartition personnalisée par mois
        </label>

        <template x-if="custom">
            <div class="mt-3 bg-slate-50 rounded-xl p-4 space-y-2 border border-slate-200">
                <p class="text-[12px] text-slate-500 mb-2">
                    Un montant par mois — le total des mensualités ne doit pas dépasser la Scolarité (Somme Totale) saisie ci-dessus :
                    <span class="font-bold" x-text="totalScolarite.toLocaleString('fr-FR') + ' FCFA'"></span>.
                </p>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <template x-for="(amount, i) in amounts" :key="i">
                        <div class="space-y-1">
                            <label class="block text-[12px] font-medium text-slate-600" x-text="'Mois ' + (i + 1)"></label>
                            <input type="number" min="0" step="1" :name="'monthly_amounts[' + i + ']'" x-model.number="amounts[i]" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-3 py-2 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                        </div>
                    </template>
                </div>

                <div class="flex justify-between items-center pt-2 border-t border-slate-200 text-[13px] font-bold">
                    <span class="text-slate-600">Total réparti (Scolarité + Frais d'inscription) :</span>
                    <span :class="total > totalScolarite ? 'text-red-600' : 'text-emerald-600'" x-text="grandTotal.toLocaleString('fr-FR') + ' / ' + grandCeiling.toLocaleString('fr-FR') + ' FCFA'"></span>
                </div>
            </div>
        </template>
    </div>
</div>

@if($errors->any())
<div class="text-[13px] text-red-600 font-medium space-y-1">
    @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
    @endforeach
</div>
@endif
