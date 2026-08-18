@extends('ParentPortal::layout')

@section('title', 'Frais Scolaires')

@section('content')
<h1 class="text-[22px] font-bold text-slate-900 mb-6">Frais Scolaires</h1>

@if(!$feeLevel)
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400 text-[13.5px]">
    Aucun barème de frais configuré pour cet élève pour le moment.
</div>
@else
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white border border-slate-100 rounded-2xl p-5 text-center">
        <div class="text-[22px] font-extrabold text-slate-800">{{ number_format($total, 0, ',', ' ') }}</div>
        <div class="text-[10.5px] uppercase font-bold text-slate-400 mt-1">Total Dû</div>
    </div>
    <div class="bg-white border border-slate-100 rounded-2xl p-5 text-center">
        <div class="text-[22px] font-extrabold text-emerald-600">{{ number_format($paid, 0, ',', ' ') }}</div>
        <div class="text-[10.5px] uppercase font-bold text-slate-400 mt-1">Déjà Payé</div>
    </div>
    <div class="bg-[#031C5B] text-white rounded-2xl p-5 text-center">
        <div class="text-[22px] font-extrabold">{{ number_format($remaining, 0, ',', ' ') }}</div>
        <div class="text-[10.5px] uppercase font-bold opacity-70 mt-1">Restant</div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Échéance</th>
                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Montant</th>
                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Date Limite</th>
                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Statut</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($schedule as $line)
                <tr>
                    <td class="px-5 py-3.5 font-bold text-slate-800 text-[13.5px]">{{ $line['label'] }}</td>
                    <td class="px-4 py-3.5 text-[12.5px] text-slate-600">{{ number_format($line['amount'], 0, ',', ' ') }}</td>
                    <td class="px-4 py-3.5 text-[12.5px] text-slate-500">{{ $line['due_date']->translatedFormat('d M Y') }}</td>
                    <td class="px-4 py-3.5">
                        @if($line['status'] === 'paid')
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700">Payé</span>
                        @elseif($line['status'] === 'due')
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700">À payer</span>
                        @else
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500">À venir</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
