@extends('ParentPortal::layout')

@section('title', 'Cantine')

@section('content')
<h1 class="text-[22px] font-bold text-slate-900 mb-6">Cantine</h1>

<div class="bg-white border border-slate-100 rounded-2xl p-5 mb-6 flex items-center justify-between">
    <span class="text-[13.5px] font-bold text-slate-600">Solde du compte cantine</span>
    <span class="text-[22px] font-extrabold {{ ($account?->balance ?? 0) < 0 ? 'text-red-600' : 'text-[#031C5B]' }}">{{ number_format($account->balance ?? 0, 0, ',', ' ') }}</span>
</div>

<h2 class="text-[15px] font-bold text-slate-800 mb-3">Menu de la Semaine</h2>
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
    <table class="w-full text-left border-collapse">
        <tbody class="divide-y divide-slate-100">
            @forelse($weekMenu as $item)
                <tr>
                    <td class="px-5 py-3.5 font-bold text-slate-800 text-[13.5px] w-32">{{ $item->date->translatedFormat('D d M') }}</td>
                    <td class="px-4 py-3.5 text-[12.5px] text-slate-500 w-24">{{ ucfirst($item->slot) }}</td>
                    <td class="px-4 py-3.5 text-[13px] text-slate-700">{{ $item->title }}</td>
                </tr>
            @empty
                <tr><td class="px-5 py-8 text-center text-slate-400 text-[13.5px]">Menu non publié pour cette semaine.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<h2 class="text-[15px] font-bold text-slate-800 mb-3">Historique des Repas</h2>
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <table class="w-full text-left border-collapse">
        <tbody class="divide-y divide-slate-100">
            @forelse($recentMeals as $meal)
                <tr>
                    <td class="px-5 py-3.5 font-bold text-slate-800 text-[13.5px]">{{ $meal->date->translatedFormat('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-[13px] text-slate-600 text-right">{{ number_format($meal->price, 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr><td class="px-5 py-8 text-center text-slate-400 text-[13.5px]">Aucun repas enregistré.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
