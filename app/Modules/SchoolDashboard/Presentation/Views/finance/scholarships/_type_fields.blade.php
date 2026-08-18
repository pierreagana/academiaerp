<div class="space-y-1.5">
    <label class="block text-[13px] font-bold text-slate-700">Nom du type <span class="text-red-500">*</span></label>
    <input type="text" name="name" value="{{ old('name', $type->name ?? '') }}" placeholder="Ex: Bourse d'Excellence" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
</div>

<div class="space-y-1.5">
    <label class="block text-[13px] font-bold text-slate-700">Montant Mensuel par Défaut (FCFA) <span class="text-red-500">*</span></label>
    <input type="number" name="default_monthly_amount" min="0" step="1" value="{{ old('default_monthly_amount', $type->default_monthly_amount ?? '') }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
</div>

<div class="grid grid-cols-2 gap-4">
    <div class="space-y-1.5">
        <label class="block text-[13px] font-bold text-slate-700">Moyenne Minimale (/20)</label>
        <input type="number" name="min_average" min="0" max="20" step="0.1" value="{{ old('min_average', $type->min_average ?? '') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
    </div>
    <div class="space-y-1.5">
        <label class="block text-[13px] font-bold text-slate-700">Taux d'Assiduité Minimum (%)</label>
        <input type="number" name="min_attendance_rate" min="0" max="100" step="1" value="{{ old('min_attendance_rate', $type->min_attendance_rate ?? '') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div class="space-y-1.5">
        <label class="block text-[13px] font-bold text-slate-700">Revenu Familial Annuel Max (FCFA)</label>
        <input type="number" name="max_family_income" min="0" step="1" value="{{ old('max_family_income', $type->max_family_income ?? '') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
    </div>
    <div class="space-y-1.5">
        <label class="block text-[13px] font-bold text-slate-700">Niveau de Compétition Minimum</label>
        <input type="text" name="min_competition_level" value="{{ old('min_competition_level', $type->min_competition_level ?? '') }}" placeholder="Ex: Niveau National" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
    </div>
</div>

<div class="space-y-1.5">
    <label class="block text-[13px] font-bold text-slate-700">Documents Obligatoires</label>
    <input type="text" name="required_documents" value="{{ old('required_documents', $type->required_documents ?? '') }}" placeholder="Ex: Fiche de paie parents, Certificat d'indigence" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
    <p class="text-[11px] text-slate-400">Séparez les documents par des virgules.</p>
</div>

@if($errors->any())
<div class="text-[13px] text-red-600 font-medium space-y-1">
    @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
    @endforeach
</div>
@endif
