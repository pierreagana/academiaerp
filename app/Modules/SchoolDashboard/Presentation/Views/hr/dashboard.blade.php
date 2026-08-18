@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::hr._tabs')

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Pilotage RH</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Centre de commandement des ressources humaines.</p>
        </div>
        <a href="{{ route('school.hr.payroll') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
            <i class="ph-bold ph-play"></i> Traiter la Paie
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Masse Salariale</p>
                <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center text-blue-600"><i class="ph-fill ph-money text-lg"></i></div>
            </div>
            <h3 class="text-2xl font-bold text-slate-800">{{ number_format($stats['payrollMass'], 0, ',', ' ') }} FCFA</h3>
            <p class="text-[12px] text-slate-400 font-semibold mt-1">Salaires actifs cumulés</p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Contrats Actifs</p>
                <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600"><i class="ph-fill ph-users-three text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $stats['activeContracts'] }}</h3>
            <p class="text-[12px] text-slate-400 font-semibold mt-1">Personnel enseignant & admin</p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Arrivées (Mois)</p>
                <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600"><i class="ph-fill ph-user-plus text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $stats['arrivals'] }}</h3>
            <p class="text-[12px] text-slate-400 font-semibold mt-1">Embauches ce mois-ci</p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Départs (Mois)</p>
                <div class="w-9 h-9 rounded-full bg-red-50 flex items-center justify-center text-red-600"><i class="ph-fill ph-user-minus text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $stats['departures'] }}</h3>
            <p class="text-[12px] text-slate-400 font-semibold mt-1">Statut passé à inactif</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tendances Dépenses de Paie -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Tendances Dépenses de Paie</h3>
            @php $maxAmount = max(1, collect($payrollTrend)->max('amount') ?: 1); @endphp
            <div class="flex items-end justify-between gap-3 h-56">
                @foreach($payrollTrend as $row)
                    @php $heightPct = $row['amount'] > 0 ? max(6, round(($row['amount'] / $maxAmount) * 100)) : 3; @endphp
                    <div class="flex-1 flex flex-col items-center justify-end h-full gap-2">
                        <span class="text-[10px] font-bold text-slate-500">{{ $row['amount'] > 0 ? number_format($row['amount'] / 1000000, 1) . 'M' : '' }}</span>
                        <div class="w-full max-w-[48px] bg-[#1E3A8A] rounded-t-lg" style="height: {{ $heightPct }}%"></div>
                        <span class="text-[11px] font-semibold text-slate-500 text-center capitalize">{{ $row['label'] }}</span>
                    </div>
                @endforeach
            </div>
            @if(collect($payrollTrend)->sum('amount') == 0)
                <p class="text-center text-slate-400 text-[12.5px] mt-3">Aucune dépense salariale enregistrée pour l'instant — lancez la paie pour commencer à alimenter ce graphique.</p>
            @endif
        </div>

        <!-- Alertes RH -->
        <div class="bg-gradient-to-br from-[#F5F3FF] to-purple-50/50 border border-purple-100 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2">
                <i class="ph-fill ph-shield-warning text-purple-600 text-lg"></i>
                <h3 class="font-extrabold text-slate-800 text-[15px]">Alertes RH</h3>
            </div>

            @if($unconfiguredSalaries->isNotEmpty())
                <div class="bg-white/70 rounded-xl p-4 border border-amber-100">
                    <p class="text-[11.5px] font-bold text-amber-600 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="ph-fill ph-warning"></i> Salaires Non Configurés</p>
                    <p class="text-[13px] text-slate-700 leading-relaxed">{{ $unconfiguredSalaries->count() }} employé{{ $unconfiguredSalaries->count() > 1 ? 's' : '' }} actif{{ $unconfiguredSalaries->count() > 1 ? 's' : '' }} sans salaire renseigné, exclu{{ $unconfiguredSalaries->count() > 1 ? 's' : '' }} de la génération de paie : {{ $unconfiguredSalaries->pluck('name')->take(3)->implode(', ') }}{{ $unconfiguredSalaries->count() > 3 ? '…' : '' }}</p>
                </div>
            @endif

            @if($upcomingContractEnds->isNotEmpty())
                <div class="bg-white/70 rounded-xl p-4 border border-slate-100">
                    <p class="text-[11.5px] font-bold text-slate-600 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="ph-fill ph-clock"></i> {{ $upcomingContractEnds->count() }} Contrat{{ $upcomingContractEnds->count() > 1 ? 's' : '' }} à Échéance</p>
                    <div class="space-y-1.5">
                        @foreach($upcomingContractEnds->take(4) as $c)
                            <p class="text-[12.5px] text-slate-700">{{ $c['name'] }} — {{ \Illuminate\Support\Carbon::parse($c['date'])->format('d M Y') }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($unconfiguredSalaries->isEmpty() && $upcomingContractEnds->isEmpty())
                <div class="bg-white/70 rounded-xl p-4 border border-slate-100">
                    <p class="text-[11.5px] font-bold text-emerald-600 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="ph-fill ph-check-circle"></i> Tout est en ordre</p>
                    <p class="text-[13px] text-slate-700 leading-relaxed">Aucun salaire manquant, aucun contrat à échéance dans les 30 prochains jours.</p>
                </div>
            @endif

            <a href="{{ route('school.hr.directory') }}" class="inline-flex items-center justify-center w-full gap-2 border border-purple-300 text-purple-700 px-4 py-2.5 rounded-xl text-[13px] font-bold hover:bg-purple-50 transition">
                Voir l'Annuaire
            </a>
        </div>
    </div>
</div>
@endsection
