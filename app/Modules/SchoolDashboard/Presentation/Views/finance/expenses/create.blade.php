@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-[900px] w-full mx-auto space-y-6 pb-20">
    <div>
        <a href="{{ route('school.finance.expenses.transactions') }}" class="text-[13px] text-slate-500 hover:text-slate-700 font-semibold flex items-center gap-1 mb-3">
            <i class="ph-bold ph-arrow-left"></i> Retour aux transactions
        </a>
        <h1 class="text-[32px] font-extrabold text-[#031C5B] tracking-tight">Ajouter une Dépense</h1>
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

    <form action="{{ route('school.finance.expenses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden">
            <div class="p-8 sm:p-10 space-y-6">
                <div class="p-4 bg-violet-50 border border-violet-100 rounded-xl flex items-start gap-3">
                    <i class="ph-fill ph-sparkle text-violet-500 text-xl mt-0.5"></i>
                    <div>
                        <h3 class="text-[14px] font-extrabold text-violet-900">Assistant de saisie intelligent</h3>
                        <p class="text-[12.5px] text-violet-700 mt-0.5">Téléchargez un reçu ci-dessous — l'équipe comptable pourra vérifier le montant, la date et le bénéficiaire depuis le justificatif joint.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label for="title" class="block text-[13.5px] font-bold text-slate-700">Titre de la dépense <span class="text-red-500">*</span></label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" placeholder="Ex: Fournitures scolaires Q1" required class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                    </div>

                    <div class="space-y-2 md:row-span-2">
                        <label for="proof" class="block text-[13.5px] font-bold text-slate-700">Preuve d'achat <span class="text-red-500">*</span></label>
                        <label for="proof" class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-300 rounded-xl p-6 text-center cursor-pointer hover:border-[#031C5B] hover:bg-slate-50 transition h-[calc(100%-28px)]">
                            <i class="ph-fill ph-upload-simple text-3xl text-slate-400"></i>
                            <span class="font-bold text-slate-700 text-[14px]" id="proof-label">Glissez votre reçu ici</span>
                            <span class="text-[12px] text-slate-400">ou cliquez pour parcourir (JPG, PNG, PDF)</span>
                            <input type="file" id="proof" name="proof" accept=".jpg,.jpeg,.png,.pdf" required class="hidden" onchange="document.getElementById('proof-label').textContent = this.files[0]?.name || 'Glissez votre reçu ici'">
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="amount" class="block text-[13.5px] font-bold text-slate-700">Montant <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[13px] font-bold">XOF</span>
                                <input type="number" id="amount" name="amount" min="1" step="1" value="{{ old('amount') }}" required class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl pl-14 pr-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="expense_date" class="block text-[13.5px] font-bold text-slate-700">Date <span class="text-red-500">*</span></label>
                            <input type="date" id="expense_date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="category" class="block text-[13.5px] font-bold text-slate-700">Catégorie <span class="text-red-500">*</span></label>
                        <select id="category" name="category" required class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                            <option value="">Sélectionner une catégorie</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="payee" class="block text-[13.5px] font-bold text-slate-700">Bénéficiaire / Fournisseur</label>
                        <input type="text" id="payee" name="payee" value="{{ old('payee') }}" placeholder="Nom de l'entreprise ou personne" class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label for="note" class="block text-[13.5px] font-bold text-slate-700">Commentaire additionnel</label>
                        <textarea id="note" name="note" rows="3" placeholder="Justification ou notes supplémentaires..." class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="px-8 sm:px-10 py-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <a href="{{ route('school.finance.expenses.transactions') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-xl hover:bg-slate-50 transition-all">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-3 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all shadow-sm flex items-center gap-2">
                    <i class="ph-bold ph-check"></i>
                    Enregistrer la dépense
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
