@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Facturation</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Vos factures d'abonnement SaaS et leur statut de paiement.</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-3.5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#031C5B] text-white flex items-center justify-center shrink-0">
                <i class="ph ph-wallet text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Portefeuille Academia Pay</p>
                <p class="text-lg font-extrabold text-slate-900">{{ number_format($wallet->balance, 0, ',', ' ') }} {{ $wallet->currency }}</p>
            </div>
            <a href="{{ route('school.wallet') }}" class="ml-4 text-xs font-bold text-indigo-600 hover:underline">Recharger</a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="p-4 text-sm text-red-800 rounded-xl bg-red-50 flex items-center gap-2 border border-red-100">
        <i class="ph-fill ph-warning-circle text-lg"></i>
        <span class="font-bold">{{ session('error') }}</span>
    </div>
    @endif

    <div class="space-y-4">
        @forelse($invoices as $invoice)
            @php
                $isPaid = $invoice->status === 'paid';
                $canPayWithWallet = !$isPaid && $wallet->balance >= $invoice->amount;
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between p-5 flex-wrap gap-3">
                    <div>
                        <p class="text-sm font-bold text-slate-900">{{ $invoice->invoice_number }} — {{ $invoice->plan_name }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Émise le {{ optional($invoice->issue_date)->format('d/m/Y') }} · Échéance {{ optional($invoice->due_date)->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-lg font-extrabold text-slate-900">{{ number_format($invoice->amount, 0, ',', ' ') }} FCFA</span>
                        @if($isPaid)
                            <span class="inline-flex items-center bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1.5 rounded-full">
                                <i class="ph-fill ph-check-circle mr-1"></i> Payée
                            </span>
                        @else
                            <button type="button" onclick="togglePayPanel('{{ $invoice->id }}')" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2 rounded-xl text-xs font-bold hover:bg-blue-900 transition cursor-pointer">
                                <i class="ph ph-credit-card"></i> Payer
                            </button>
                        @endif
                    </div>
                </div>

                @if(!$isPaid)
                <div id="pay-panel-{{ $invoice->id }}" class="hidden border-t border-slate-100 bg-slate-50/50 p-5 space-y-2.5">
                    @if($canPayWithWallet)
                        <form action="{{ route('school.billing.pay', [$invoice->id, 'wallet']) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 bg-white border border-slate-200 hover:border-[#031C5B] rounded-xl px-4 py-3 text-left transition">
                                <div class="w-9 h-9 rounded-lg bg-[#031C5B] text-white flex items-center justify-center font-black text-[10px] shrink-0">AP</div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-slate-800">Payer avec mon portefeuille Academia Pay</p>
                                    <p class="text-xs text-slate-500">Solde : {{ number_format($wallet->balance, 0, ',', ' ') }} {{ $wallet->currency }}</p>
                                </div>
                            </button>
                        </form>
                    @endif
                    @if($gateways->firstWhere('slug', 'cash'))
                        <form action="{{ route('school.billing.pay', [$invoice->id, 'cash']) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 bg-white border border-slate-200 hover:border-[#031C5B] rounded-xl px-4 py-3 text-left transition">
                                <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-black text-[10px] shrink-0">ESP</div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-slate-800">Payer en espèces</p>
                                    <p class="text-xs text-slate-500">Remise en main propre — confirmée manuellement par la plateforme à réception.</p>
                                </div>
                            </button>
                        </form>
                    @endif
                    @foreach($gateways as $gateway)
                        @if(!in_array($gateway->slug, ['academia_pay', 'cash']))
                            <form action="{{ route('school.billing.pay', [$invoice->id, $gateway->slug]) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 bg-white border border-slate-200 hover:border-[#031C5B] rounded-xl px-4 py-3 text-left transition">
                                    <div class="w-9 h-9 rounded-lg bg-slate-700 text-white flex items-center justify-center font-black text-[10px] shrink-0">{{ strtoupper(substr($gateway->name, 0, 2)) }}</div>
                                    <p class="text-sm font-bold text-slate-800">Payer avec {{ $gateway->name }}</p>
                                </button>
                            </form>
                        @endif
                    @endforeach
                    @if($gateways->whereNotIn('slug', ['academia_pay', 'cash'])->isEmpty() && !$canPayWithWallet && !$gateways->firstWhere('slug', 'cash'))
                        <p class="text-xs text-slate-500 italic">Aucun moyen de paiement n'est configuré pour le moment — contactez la plateforme.</p>
                    @endif
                </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center text-sm text-slate-500">
                Aucune facture pour le moment.
            </div>
        @endforelse
    </div>

    {{ $invoices->links() }}
</div>

<script>
    function togglePayPanel(id) {
        document.querySelectorAll('[id^="pay-panel-"]').forEach(p => { if (p.id !== 'pay-panel-' + id) p.classList.add('hidden'); });
        document.getElementById('pay-panel-' + id)?.classList.toggle('hidden');
    }
</script>
@endsection
