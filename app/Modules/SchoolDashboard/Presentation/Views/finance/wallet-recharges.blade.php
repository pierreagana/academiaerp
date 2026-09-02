@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::finance._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Recharges Academia Pay</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Confirmez les dépôts en espèces des parents pour créditer leur portefeuille Academia Pay.</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                <thead>
                    <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-widest bg-slate-50 border-b border-slate-200">
                        <th class="py-3 px-5">Parent</th>
                        <th class="py-3 px-4">Montant</th>
                        <th class="py-3 px-4">Moyen</th>
                        <th class="py-3 px-4">Demandé le</th>
                        <th class="py-3 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[13px]">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-5 font-bold text-slate-900">{{ $req->parent->name ?? '—' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ number_format($req->amount, 0, ',', ' ') }} FCFA</td>
                            <td class="py-3 px-4 text-slate-600">{{ ucfirst($req->method) }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('school.finance.fees.wallet-recharges.approve', $req->id) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-[11.5px] font-bold hover:bg-emerald-700">Confirmer</button>
                                    </form>
                                    <button type="button" onclick="document.getElementById('reject-{{ $req->id }}').classList.toggle('hidden')" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-[11.5px] font-bold hover:bg-red-100">Refuser</button>
                                </div>
                                <form id="reject-{{ $req->id }}" method="POST" action="{{ route('school.finance.fees.wallet-recharges.reject', $req->id) }}" class="hidden mt-2 flex items-center gap-2 justify-end">
                                    @csrf
                                    <input type="text" name="reason" placeholder="Motif (optionnel)" class="border border-slate-200 rounded-lg px-2.5 py-1.5 text-[11.5px] w-40">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-[11.5px] font-bold hover:bg-red-700">Confirmer le refus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-slate-400 text-sm">Aucune demande de recharge en attente.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
