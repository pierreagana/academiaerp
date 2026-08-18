@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::finance._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Gestion Globale de la Scolarité</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Vue d'ensemble financière de l'année scolaire en cours.</p>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Revenu Attendu</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ number_format($stats['totalExpected'], 0, ',', ' ') }} FCFA</h3>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Impayés</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ number_format($stats['outstanding'], 0, ',', ' ') }} FCFA</h3>
            @if($stats['outstanding'] > 0)
                <span class="inline-flex items-center gap-1 text-[12px] font-bold text-red-600 mt-1"><i class="ph-fill ph-warning-circle"></i> Nécessite attention</span>
            @endif
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Taux de Recouvrement</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ $stats['collectionRate'] }}%</h3>
            <div class="w-full h-1.5 bg-slate-100 rounded-full mt-2 overflow-hidden">
                <div class="h-full bg-[#031C5B] rounded-full" style="width: {{ min($stats['collectionRate'], 100) }}%"></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Élèves Actifs</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ number_format($stats['activeStudents'], 0, ',', ' ') }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Payment Trends -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Tendance des Paiements</h3>
            @php $maxTrend = max(1, collect($trend)->max('total')); @endphp
            <div class="flex items-end justify-between gap-3 h-56">
                @foreach($trend as $point)
                    @php $heightPct = $point['total'] > 0 ? max(4, round(($point['total'] / $maxTrend) * 100)) : 2; @endphp
                    <div class="flex-1 flex flex-col items-center justify-end h-full gap-2">
                        <div class="w-full max-w-[36px] bg-[#1E3A8A] rounded-t-md" style="height: {{ $heightPct }}%" title="{{ number_format($point['total'], 0, ',', ' ') }} FCFA"></div>
                        <span class="text-[11px] font-semibold text-slate-500">{{ $point['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Alertes de Retards -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">Alertes de Retards</h3>
                @if(count($lateAlerts) > 0)
                <span class="px-2.5 py-1 bg-red-50 text-red-600 text-[11px] font-bold rounded-full">{{ count($lateAlerts) }}</span>
                @endif
            </div>
            <div class="p-5 space-y-4 flex-1">
                @forelse($lateAlerts as $alert)
                    <div class="pb-4 border-b border-slate-50 last:border-0 last:pb-0">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-bold text-slate-800 text-[14px]">{{ $alert['student']->first_name }} {{ $alert['student']->last_name }}</p>
                                <p class="text-[12px] text-slate-500">Classe: {{ $alert['student']->academicClass->name ?? '-' }}</p>
                            </div>
                            <span class="font-bold text-slate-800 text-[14px]">{{ number_format($alert['remaining'], 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex items-center gap-1 text-[12px] text-red-600 font-semibold mt-1">
                            <i class="ph ph-clock"></i> Retard: {{ $alert['daysLate'] }} jours
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 text-[13px] text-center py-6">Aucun retard de paiement.</p>
                @endforelse
            </div>
            <div class="p-5 border-t border-slate-100">
                <a href="{{ route('school.finance.fees.payments', ['status' => 'late']) }}" class="text-[#031C5B] font-bold text-[13px] hover:underline">Voir tous les retards</a>
            </div>
        </div>
    </div>
</div>
@endsection
