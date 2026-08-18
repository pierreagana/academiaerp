@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::finance.expenses._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Gestion des Budgets</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Définissez et suivez vos allocations financières par catégorie.</p>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="p-4 mb-4 text-sm text-red-800 rounded-xl bg-red-50 border border-red-100">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Aperçu IA -->
        <div class="bg-violet-50 rounded-2xl p-6 border border-violet-100 shadow-sm">
            <h3 class="text-lg font-bold text-violet-900 mb-2 flex items-center gap-2"><i class="ph-fill ph-sparkle"></i> Aperçu IA</h3>
            <p class="text-[13.5px] text-violet-700">Basé sur les dépenses passées, revoyez régulièrement vos plafonds pour les catégories dont la consommation dépasse 80%.</p>
            @php
                $globalConsumed = $budgets->sum(fn($b) => $b->consumed);
                $globalAmount = $budgets->sum('amount');
                $globalPct = $globalAmount > 0 ? round(($globalConsumed / $globalAmount) * 100) : 0;
            @endphp
            <div class="mt-5 bg-white/60 rounded-xl p-4">
                <p class="text-[11px] font-bold text-violet-500 uppercase tracking-wider">Consommation Globale</p>
                <p class="text-2xl font-extrabold text-violet-900 mt-1">{{ $globalPct }}%</p>
                <div class="w-full h-2 bg-white rounded-full mt-2 overflow-hidden">
                    <div class="h-full bg-violet-600 rounded-full" style="width: {{ min($globalPct, 100) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Nouveau plafond -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Nouveau Plafond Budgétaire</h3>
            <form action="{{ route('school.finance.expenses.budgets.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Catégorie <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Périodicité <span class="text-red-500">*</span></label>
                        <select name="period" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                            @foreach(\App\Modules\Finance\Domain\Models\ExpenseBudget::PERIODS as $key => $label)
                                <option value="{{ $key }}" {{ $key === 'annual' ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <input type="hidden" name="academic_year" value="{{ $currentYear }}">
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Montant Alloué (FCFA) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" min="1" step="1" placeholder="Ex: 5000000" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition">Enregistrer le Budget</button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-900">Suivi des Budgets par Catégorie</h3>
            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[11px] font-bold rounded-full uppercase">Année {{ $currentYear }}</span>
        </div>
        <div class="space-y-5">
            @forelse($budgets as $budget)
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 shrink-0">
                    <i class="ph-fill {{ \App\Modules\Finance\Domain\Models\Expense::iconFor($budget->category) }} text-lg"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <p class="text-[14px] font-bold text-slate-800">{{ $budget->category }}</p>
                            <p class="text-[11px] text-slate-400">{{ \App\Modules\Finance\Domain\Models\ExpenseBudget::PERIODS[$budget->period] ?? $budget->period }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-[15px] font-extrabold text-slate-900">{{ number_format($budget->consumed / 1000, 0) }}k</span>
                            <span class="text-[13px] text-slate-400"> / {{ number_format($budget->amount / 1000000, 1) }}M FCFA</span>
                        </div>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full {{ $budget->percentage >= 90 ? 'bg-red-500' : ($budget->percentage >= 70 ? 'bg-amber-500' : 'bg-[#031C5B]') }} rounded-full" style="width: {{ min($budget->percentage, 100) }}%"></div>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-[11px] {{ $budget->percentage >= 90 ? 'text-red-600' : 'text-slate-500' }} font-semibold">{{ $budget->percentage }}% Consommé</span>
                        <span class="text-[11px] {{ $budget->remaining <= 0 ? 'text-red-600' : 'text-[#031C5B]' }} font-bold">
                            @if($budget->remaining <= 0) Dépassé @else Reste: {{ number_format($budget->remaining / 1000, 0) }}k @endif
                        </span>
                    </div>
                </div>
                <form action="{{ route('school.finance.expenses.budgets.destroy', $budget->id) }}" method="POST" onsubmit="return confirm('Supprimer ce budget ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-slate-300 hover:text-red-500 transition"><i class="ph-bold ph-trash"></i></button>
                </form>
            </div>
            @empty
            <p class="text-slate-400 text-[13px] text-center py-10">Aucun budget configuré pour le moment.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
