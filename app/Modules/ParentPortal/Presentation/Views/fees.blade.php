@extends('ParentPortal::layout')

@section('title', 'Frais Scolaires - ' . $child->first_name)

@section('content')

<!-- HEADER WITH BREADCRUMB & WALLET LINK -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Frais de Scolarité &bull; {{ $child->first_name }}</h1>
        <p class="text-sm font-medium text-slate-500 mt-0.5">Classe: <span class="font-bold text-slate-800">{{ $child->academicClass->name ?? 'Non assignée' }}</span>
            @if($academicYear ?? null) &bull; Année: {{ $academicYear }} @endif
        </p>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('parent.finance') }}" 
           class="inline-flex items-center gap-2 bg-blue-50 hover:bg-blue-100 text-[#061536] font-bold text-xs px-4 py-2.5 rounded-xl transition">
            <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
            <span>Portefeuille & Transactions</span>
        </a>
    </div>
</div>

@php
    $feeTypeLabels = ['tuition' => 'Frais de Scolarité', 'cantine' => 'Frais de Cantine', 'transport' => 'Frais de Transport'];
    $feeTypeIcons = ['tuition' => 'school', 'cantine' => 'restaurant', 'transport' => 'directions_bus'];
@endphp

<div x-data="{ activeFeeTab: '{{ array_key_first($fees) }}' }">

    <!-- FEE TYPE TABS -->
    <div class="flex items-center gap-1.5 mb-6 border-b border-slate-200">
        @foreach($fees as $feeType => $feeData)
        <button type="button"
                x-on:click="activeFeeTab = '{{ $feeType }}'"
                :class="activeFeeTab === '{{ $feeType }}' ? 'bg-[#061536] text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100'"
                class="flex items-center gap-1.5 px-4 py-2.5 rounded-t-xl text-xs font-bold transition">
            <span class="material-symbols-outlined text-[16px]">{{ $feeTypeIcons[$feeType] ?? 'receipt' }}</span>
            <span>{{ $feeTypeLabels[$feeType] ?? ucfirst($feeType) }}</span>
        </button>
        @endforeach
    </div>

@foreach($fees as $feeType => $feeData)
<div x-show="activeFeeTab === '{{ $feeType }}'" x-cloak class="mb-8">

    @if(!$feeData['feeLevel'])
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8 text-center text-slate-400 text-sm">
        <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-3">
            <span class="material-symbols-outlined text-[24px]">receipt</span>
        </div>
        <p class="font-bold text-slate-700 mb-1">Aucun barème configuré</p>
        <p class="text-xs text-slate-400">L'administration scolaire n'a pas encore défini de barème {{ strtolower($feeTypeLabels[$feeType] ?? $feeType) }} pour ce niveau.</p>
    </div>
    @else

    <!-- 3 METRIC CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
        <div class="bg-white border border-slate-100 rounded-3xl p-5 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Annuel Dû</span>
            <div class="text-2xl font-black text-slate-900">{{ number_format($feeData['total'], 0, ',', ' ') }} <span class="text-xs font-bold text-slate-400">FCFA</span></div>
        </div>

        <div class="bg-white border border-slate-100 rounded-3xl p-5 shadow-xs">
            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block mb-1">Montant Déjà Réglé</span>
            <div class="text-2xl font-black text-emerald-600">{{ number_format($feeData['paid'], 0, ',', ' ') }} <span class="text-xs font-bold text-slate-400">FCFA</span></div>
        </div>

        <div class="bg-[#061536] text-white rounded-3xl p-5 shadow-md shadow-blue-950/20">
            <span class="text-[11px] font-bold text-blue-200/80 uppercase tracking-wider block mb-1">Reste à Payer</span>
            <div class="text-2xl font-black text-white">{{ number_format($feeData['remaining'], 0, ',', ' ') }} <span class="text-xs font-bold text-blue-300/60">FCFA</span></div>
        </div>
    </div>

    <!-- SCHEDULE TABLE -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-slate-900">Échéancier des Versements</h3>
            <span class="text-xs font-bold bg-slate-100 text-slate-600 px-3 py-1 rounded-xl">{{ count($feeData['schedule']) }} Échéances</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3.5">Échéance</th>
                        <th class="px-4 py-3.5">Montant</th>
                        <th class="px-4 py-3.5">Date Limite</th>
                        <th class="px-4 py-3.5 text-right">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach($feeData['schedule'] as $line)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4 font-bold text-slate-900">{{ $line['label'] }}</td>
                            <td class="px-4 py-4 font-extrabold text-slate-800">{{ number_format($line['amount'], 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-4 font-medium text-slate-500">{{ $line['due_date']->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-4 text-right">
                                @if($line['status'] === 'paid')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        <span class="material-symbols-outlined text-[13px]">check</span> Payé
                                    </span>
                                @elseif($line['status'] === 'due')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200/60">
                                        <span class="material-symbols-outlined text-[13px]">schedule</span> À payer
                                    </span>
                                @else
                                    <span class="inline-block text-[11px] font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-500">
                                        À venir
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endforeach

</div>

@endsection
