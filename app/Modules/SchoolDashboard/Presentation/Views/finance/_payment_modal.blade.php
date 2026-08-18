@php
    $hasPaymentErrors = $errors->hasAny(['student_id', 'amount', 'method', 'paid_at', 'reference', 'note']);
@endphp
<div x-data="{ open: {{ $hasPaymentErrors ? 'true' : 'false' }} }">
    <button @click="open = true" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
        <i class="ph-bold ph-plus text-lg"></i>
        {{ $buttonLabel ?? 'Nouveau Paiement' }}
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
        <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-[18px] font-extrabold text-[#031C5B]">Nouveau Paiement</h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <form action="{{ route('school.finance.fees.payments.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="type" value="{{ $type ?? 'tuition' }}">

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

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Montant (FCFA) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" min="1" step="1" value="{{ old('amount') }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="paid_at" value="{{ old('paid_at', date('Y-m-d')) }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Méthode <span class="text-red-500">*</span></label>
                    <select name="method" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                        @foreach(\App\Modules\Finance\Domain\Models\Payment::METHODS as $key => $label)
                            <option value="{{ $key }}" {{ old('method') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Référence</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" placeholder="Ex: TRX-9982-WAV" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                </div>

                @if($hasPaymentErrors)
                <div class="text-[13px] text-red-600 font-medium space-y-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-xl hover:bg-slate-50 transition-all">
                        Annuler
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
