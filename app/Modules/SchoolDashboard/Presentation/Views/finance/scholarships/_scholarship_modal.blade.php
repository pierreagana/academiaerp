@php
    $hasScholarshipErrors = $errors->hasAny(['student_id', 'scholarship_type_id', 'monthly_amount', 'declared_average', 'awarded_date', 'expiry_date', 'notes']);
    $typeAmounts = collect($types ?? [])->mapWithKeys(fn ($t) => [$t->id => (float) $t->default_monthly_amount]);
@endphp
<div x-data="{ open: {{ $hasScholarshipErrors ? 'true' : 'false' }}, amounts: {{ $typeAmounts->toJson() }}, monthlyAmount: '{{ old('monthly_amount') }}' }">
    <button @click="open = true" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
        <i class="ph-bold ph-plus text-lg"></i>
        {{ $buttonLabel ?? 'Nouvelle Bourse' }}
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
        <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-[18px] font-extrabold text-[#031C5B]">Nouvelle Attribution de Bourse</h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <form action="{{ route('school.finance.scholarships.store') }}" method="POST" class="p-6 space-y-4">
                @csrf

                @if(isset($fixedStudent))
                    <input type="hidden" name="student_id" value="{{ $fixedStudent->id }}">
                    <div class="text-[14px] font-semibold text-slate-700 bg-slate-50 rounded-xl px-4 py-3">
                        Étudiant : {{ $fixedStudent->first_name }} {{ $fixedStudent->last_name }}
                    </div>
                @else
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Étudiant <span class="text-red-500">*</span></label>
                        <select name="student_id" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                            <option value="">Sélectionner un étudiant</option>
                            @foreach($allStudents ?? [] as $s)
                                <option value="{{ $s->id }}" {{ old('student_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->first_name }} {{ $s->last_name }}@if($s->academicClass) — {{ $s->academicClass->name }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Type de Bourse <span class="text-red-500">*</span></label>
                    <select name="scholarship_type_id" required @change="monthlyAmount = amounts[$event.target.value] ?? monthlyAmount" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                        <option value="">Sélectionner un type</option>
                        @foreach($types ?? [] as $type)
                            <option value="{{ $type->id }}" {{ old('scholarship_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @if(($types ?? collect())->isEmpty())
                        <p class="text-[12px] text-slate-500">Aucun type défini. <a href="{{ route('school.finance.scholarships.criteria') }}" class="text-[#031C5B] font-bold hover:underline">Créez-en un</a> d'abord.</p>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Montant Mensuel (FCFA) <span class="text-red-500">*</span></label>
                        <input type="number" name="monthly_amount" min="0" step="1" x-model="monthlyAmount" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Moyenne Déclarée (/20)</label>
                        <input type="number" name="declared_average" min="0" max="20" step="0.1" value="{{ old('declared_average') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Date d'attribution</label>
                        <input type="date" name="awarded_date" value="{{ old('awarded_date') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Date d'expiration</label>
                        <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                    </div>
                </div>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="priority" value="1" {{ old('priority') ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]/20">
                    <span class="text-[13px] font-semibold text-slate-700">Marquer comme priorité haute</span>
                </label>

                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Notes</label>
                    <textarea name="notes" rows="2" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">{{ old('notes') }}</textarea>
                </div>

                @if($hasScholarshipErrors)
                <div class="text-[13px] text-red-600 font-medium space-y-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <p class="text-[12px] text-slate-500">La bourse sera créée avec le statut "En attente" et devra être approuvée depuis le Tableau de Bord.</p>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-xl hover:bg-slate-50 transition-all">
                        Annuler
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all">
                        Créer la Demande
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
