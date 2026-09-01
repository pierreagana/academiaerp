@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Attribuer une Récompense</h2>
            <p class="text-slate-500 text-sm mt-1">Diplôme, distinction ou récompense pour un élève, un enseignant ou un membre du personnel.</p>
        </div>
        <a href="{{ route('school.academic.awards.index') }}" class="px-4 py-2 bg-white border border-slate-200 hover:border-slate-300 text-slate-600 font-bold text-[13px] rounded-xl hover:bg-slate-50 transition">
            <i class="ph-bold ph-arrow-left"></i> Retour
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <div class="flex items-center gap-2 mb-2">
            <i class="ph-fill ph-warning-circle text-lg"></i>
            <span class="font-bold">Il y a des erreurs dans le formulaire :</span>
        </div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('school.academic.awards.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5" x-data="{ recipientType: '{{ old('recipient_type', request('recipient_type', 'student')) }}' }">
        @csrf

        <div>
            <label class="block text-[13px] font-bold text-slate-700 mb-2">Type de destinataire <span class="text-red-500">*</span></label>
            <div class="flex gap-2">
                @foreach(\App\Modules\Academic\Domain\Models\Award::RECIPIENT_TYPES as $key => $label)
                <label class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 cursor-pointer transition"
                    :class="recipientType === '{{ $key }}' ? 'border-[#031C5B] bg-blue-50/50 text-[#031C5B]' : 'border-slate-200 text-slate-500'">
                    <input type="radio" name="recipient_type" value="{{ $key }}" x-model="recipientType" class="hidden">
                    <span class="text-[13px] font-bold">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div>
            <label class="block text-[13px] font-bold text-slate-700 mb-2">Destinataire <span class="text-red-500">*</span></label>

            <select x-show="recipientType === 'student'" :disabled="recipientType !== 'student'" name="recipient_id" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                <option value="">Sélectionner un élève...</option>
                @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ old('recipient_id', request('recipient_id')) == $s->id ? 'selected' : '' }}>{{ $s->first_name }} {{ $s->last_name }} — #{{ $s->roll_number }}</option>
                @endforeach
            </select>

            <select x-show="recipientType === 'teacher'" :disabled="recipientType !== 'teacher'" name="recipient_id" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                <option value="">Sélectionner un enseignant...</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ old('recipient_id') == $t->id ? 'selected' : '' }}>{{ $t->first_name }} {{ $t->last_name }} — {{ $t->employee_id }}</option>
                @endforeach
            </select>

            <select x-show="recipientType === 'staff'" :disabled="recipientType !== 'staff'" name="recipient_id" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                <option value="">Sélectionner un membre du personnel...</option>
                @foreach($staffMembers as $st)
                    <option value="{{ $st->id }}" {{ old('recipient_id') == $st->id ? 'selected' : '' }}>{{ $st->first_name }} {{ $st->last_name }} — {{ $st->employee_id }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="award_type_id" class="block text-[13px] font-bold text-slate-700">Récompense <span class="text-red-500">*</span></label>
                <a href="{{ route('school.academic.awards.models.index') }}" class="text-[12px] font-bold text-[#031C5B] hover:underline">+ Créer un modèle</a>
            </div>
            <select id="award_type_id" name="award_type_id" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                <option value="">Sélectionner...</option>
                @foreach($awardTypes as $category => $types)
                    <optgroup label="{{ $category }}">
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ old('award_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        <div>
            <label for="material_reward" class="block text-[13px] font-bold text-slate-700 mb-2">Récompense concrète (optionnel)</label>
            <select id="material_reward" name="material_reward" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                <option value="">Aucune</option>
                @foreach(\App\Modules\Academic\Domain\Models\Award::MATERIAL_REWARDS as $reward)
                    <option value="{{ $reward }}" {{ old('material_reward') === $reward ? 'selected' : '' }}>{{ $reward }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="awarded_date" class="block text-[13px] font-bold text-slate-700 mb-2">Date <span class="text-red-500">*</span></label>
                <input type="date" id="awarded_date" name="awarded_date" required value="{{ old('awarded_date', now()->format('Y-m-d')) }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
            </div>
        </div>

        <div>
            <label for="reason" class="block text-[13px] font-bold text-slate-700 mb-2">Motif / Commentaire (optionnel)</label>
            <textarea id="reason" name="reason" rows="3" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">{{ old('reason') }}</textarea>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="px-6 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">Attribuer la récompense</button>
        </div>
    </form>
</div>
@endsection
