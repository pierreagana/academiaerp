@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Portefeuille Academia Pay</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Rechargez votre portefeuille et payez vos factures instantanément, sans repasser par une passerelle externe.</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Balance + recharge -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="text-center mb-6">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Solde disponible</p>
                <p class="text-[36px] font-extrabold text-[#031C5B] mt-1">{{ number_format($wallet->balance, 0, ',', ' ') }}</p>
                <p class="text-sm text-slate-500">{{ $wallet->currency }}</p>
            </div>

            <form action="{{ route('school.wallet.recharge') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Montant à recharger</label>
                    <input type="number" name="amount" min="100" step="1" required placeholder="Ex: 10000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-800 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Passerelle</label>
                    <select name="gateway" required class="w-full appearance-none bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-800 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        @forelse($gateways as $gateway)
                            <option value="{{ $gateway->slug }}">{{ $gateway->name }}</option>
                        @empty
                            <option value="" disabled selected>Aucune passerelle active</option>
                        @endforelse
                    </select>
                </div>
                <button type="submit" {{ $gateways->isEmpty() ? 'disabled' : '' }} class="w-full flex items-center justify-center gap-2 bg-[#031C5B] text-white px-6 py-3 rounded-xl text-sm font-bold hover:bg-blue-900 transition disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                    <i class="ph ph-arrow-circle-up"></i> Recharger
                </button>
            </form>
        </div>

        <!-- History -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Historique des mouvements</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($transactions as $tx)
                    @php
                        $isCredit = $tx->type === 'recharge';
                    @endphp
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $isCredit ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">
                                <i class="ph-fill {{ $isCredit ? 'ph-arrow-down-left' : 'ph-arrow-up-right' }}"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $tx->description ?? ($isCredit ? 'Recharge' : 'Paiement') }}</p>
                                <p class="text-xs text-slate-400">{{ $tx->created_at->format('d/m/Y H:i') }} · {{ $tx->reference }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-extrabold {{ $isCredit ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ $isCredit ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', ' ') }}
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">Aucun mouvement pour le moment.</p>
                @endforelse
            </div>
            <div class="p-4">{{ $transactions->links() }}</div>
        </div>
    </div>
</div>
@endsection
