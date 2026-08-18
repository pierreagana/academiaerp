@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::finance.expenses._tabs')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Aperçu des Dépenses</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Suivi mensuel des sorties de fonds.</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('school.finance.expenses.generate-salaries') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition shadow-sm">
                    <i class="ph-bold ph-money text-lg"></i>
                    Générer les salaires du mois
                </button>
            </form>
            <a href="{{ route('school.finance.expenses.create') }}" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
                <i class="ph-bold ph-plus text-lg"></i>
                Ajouter une Dépense
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Dépenses du mois (FCFA)</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ number_format($stats['monthlyTotal'], 0, ',', ' ') }}</h3>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Budget Restant (FCFA)</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ number_format($stats['remaining'], 0, ',', ' ') }}</h3>
            <div class="w-full h-1.5 bg-slate-100 rounded-full mt-2 overflow-hidden">
                <div class="h-full {{ $stats['usagePercentage'] >= 90 ? 'bg-red-500' : 'bg-[#031C5B]' }} rounded-full" style="width: {{ min($stats['usagePercentage'], 100) }}%"></div>
            </div>
        </div>
        <div class="bg-violet-50 rounded-2xl p-5 border border-violet-100 shadow-sm">
            <p class="text-[11px] font-bold text-violet-500 uppercase tracking-wider mb-2 flex items-center gap-1"><i class="ph-fill ph-sparkle"></i> Analyse IA</p>
            <p class="text-[13px] text-violet-900">Surveillez les catégories dont les dépenses augmentent fortement par rapport à la moyenne trimestrielle.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Dépenses par catégorie -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Dépenses par Catégorie</h3>
            @php $maxAmount = max(1, $breakdown->max('amount') ?? 1); @endphp
            <div class="flex items-end justify-between gap-3 h-56">
                @forelse($breakdown as $b)
                    @php $heightPct = $b['amount'] > 0 ? max(4, round(($b['amount'] / $maxAmount) * 100)) : 2; @endphp
                    <div class="flex-1 flex flex-col items-center justify-end h-full gap-2">
                        <div class="w-full max-w-[40px] bg-[#1E3A8A] rounded-t-md" style="height: {{ $heightPct }}%" title="{{ number_format($b['amount'], 0, ',', ' ') }} FCFA"></div>
                        <span class="text-[11px] font-semibold text-slate-500 text-center">{{ $b['category'] }}</span>
                    </div>
                @empty
                    <p class="text-slate-400 text-[13px] text-center w-full">Aucune dépense enregistrée.</p>
                @endforelse
            </div>
        </div>

        <!-- Dépenses récentes -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">Dépenses Récentes</h3>
                <a href="{{ route('school.finance.expenses.transactions') }}" class="text-[#031C5B] font-bold text-[13px] hover:underline">Tout voir</a>
            </div>
            <div class="divide-y divide-slate-50 flex-1">
                @forelse($recent as $expense)
                <div class="p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 shrink-0">
                        <i class="ph-fill {{ \App\Modules\Finance\Domain\Models\Expense::iconFor($expense->category) }} text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-slate-800 truncate">{{ $expense->title }}</p>
                        <p class="text-[11px] text-slate-500">{{ $expense->expense_date->format('d M') }}, {{ $expense->category }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[13px] font-bold text-red-600">-{{ number_format($expense->amount, 0, ',', ' ') }}</p>
                        @php
                            $statusStyles = ['approved' => 'bg-emerald-50 text-emerald-700', 'pending' => 'bg-violet-50 text-violet-700', 'rejected' => 'bg-red-50 text-red-700'];
                        @endphp
                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ $statusStyles[$expense->status] ?? '' }}">{{ \App\Modules\Finance\Domain\Models\Expense::STATUSES[$expense->status] ?? $expense->status }}</span>
                    </div>
                </div>
                @empty
                <p class="text-slate-400 text-[13px] text-center py-10">Aucune dépense récente.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
