@extends('ParentPortal::layout')

@section('title', 'Gestion Financière & Portefeuille')

@section('content')

<!-- PAGE TITLE -->
<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Gestion Financière & Portefeuille</h1>
    <p class="text-sm font-medium text-slate-500 mt-1">Gérez vos soldes, réglez les frais de scolarité et suivez vos transactions.</p>
</div>

<!-- TOP GRID: MON PORTEFEUILLE + OPTIONS DE RECHARGEMENT RAPIDE -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{ rechargeModal: false, selectedProvider: 'orange' }">
    
    <!-- MON PORTEFEUILLE CARD (Col 5) -->
    <div class="lg:col-span-5 bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between relative overflow-hidden">
        <!-- Subtle decorative glow -->
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-blue-500/5 rounded-full blur-xl pointer-events-none"></div>

        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[19px]">account_balance_wallet</span>
                    </div>
                    <h2 class="text-sm font-extrabold text-slate-900">Mon Portefeuille</h2>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition">
                    <span class="material-symbols-outlined text-[20px]">more_vert</span>
                </button>
            </div>

            <span class="text-[12px] font-bold text-slate-400 block mb-1">Solde Disponible</span>
            <div class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight flex items-baseline gap-2">
                <span>{{ number_format($wallet->balance ?? 0, 0, ',', ' ') }}</span>
                <span class="text-sm font-extrabold text-slate-500">{{ $wallet->currency ?? 'FCFA' }}</span>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="button" @click="rechargeModal = true; selectedProvider = 'orange'"
                    class="flex-1 inline-flex items-center justify-center gap-2 bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs py-3 px-4 rounded-2xl transition shadow-md shadow-blue-950/20">
                <span class="material-symbols-outlined text-[17px]">add</span>
                <span>Recharger</span>
            </button>

            <button type="button" @click="rechargeModal = true; selectedProvider = 'qr'" title="Scanner un QR Code"
                    class="w-11 h-11 rounded-2xl bg-blue-50 hover:bg-blue-100 text-[#061536] flex items-center justify-center transition shrink-0">
                <span class="material-symbols-outlined text-[20px]">qr_code_scanner</span>
            </button>
        </div>
    </div>

    <!-- OPTIONS DE RECHARGEMENT RAPIDE (Col 7) -->
    <div class="lg:col-span-7 bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center gap-2.5 mb-4">
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-[19px]">credit_card</span>
            </div>
            <h2 class="text-sm font-extrabold text-slate-900">Options de Rechargement Rapide</h2>
        </div>

        <div class="grid grid-cols-3 gap-3">
            
            <!-- Orange Money -->
            <button type="button" @click="rechargeModal = true; selectedProvider = 'orange'"
                    class="flex flex-col items-center justify-center p-4 rounded-2xl border border-slate-100 hover:border-orange-200 hover:bg-orange-50/30 transition group text-center">
                <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center mb-2.5 group-hover:scale-105 transition shadow-xs">
                    <span class="material-symbols-outlined text-[22px]">phone_android</span>
                </div>
                <span class="text-xs font-bold text-slate-800 group-hover:text-orange-600 transition leading-tight">Orange Money</span>
            </button>

            <!-- Wave -->
            <button type="button" @click="rechargeModal = true; selectedProvider = 'wave'"
                    class="flex flex-col items-center justify-center p-4 rounded-2xl border border-slate-100 hover:border-cyan-200 hover:bg-cyan-50/30 transition group text-center">
                <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center mb-2.5 group-hover:scale-105 transition shadow-xs">
                    <span class="material-symbols-outlined text-[22px]">waves</span>
                </div>
                <span class="text-xs font-bold text-slate-800 group-hover:text-cyan-600 transition leading-tight">Wave</span>
            </button>

            <!-- Carte Bancaire -->
            <button type="button" @click="rechargeModal = true; selectedProvider = 'card'"
                    class="flex flex-col items-center justify-center p-4 rounded-2xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50/30 transition group text-center">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-2.5 group-hover:scale-105 transition shadow-xs">
                    <span class="material-symbols-outlined text-[22px]">credit_card</span>
                </div>
                <span class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition leading-tight">Carte Bancaire</span>
            </button>
        </div>
    </div>

    <!-- RECHARGE MODAL POPUP -->
    <div x-show="rechargeModal" style="display:none;" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-4" x-on:keydown.escape.window="rechargeModal=false">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl" x-on:click.outside="rechargeModal=false">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#061536] flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Recharger le Portefeuille</h3>
                        <p class="text-[11px] text-slate-500">Paiement sécurisé instantané</p>
                    </div>
                </div>
                <button type="button" x-on:click="rechargeModal=false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
            </div>

            <form action="{{ route('parent.dashboard') }}" method="GET" class="space-y-4">
                <div>
                    <label class="text-[12px] font-bold text-slate-700 block mb-1.5">Méthode de Paiement</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex flex-col items-center p-3 rounded-xl border cursor-pointer transition text-center" :class="selectedProvider === 'orange' ? 'border-orange-500 bg-orange-50/50' : 'border-slate-200'">
                            <input type="radio" name="provider" value="orange" x-model="selectedProvider" class="hidden">
                            <span class="text-xs font-bold text-slate-800">Orange Money</span>
                        </label>
                        <label class="flex flex-col items-center p-3 rounded-xl border cursor-pointer transition text-center" :class="selectedProvider === 'wave' ? 'border-cyan-500 bg-cyan-50/50' : 'border-slate-200'">
                            <input type="radio" name="provider" value="wave" x-model="selectedProvider" class="hidden">
                            <span class="text-xs font-bold text-slate-800">Wave</span>
                        </label>
                        <label class="flex flex-col items-center p-3 rounded-xl border cursor-pointer transition text-center" :class="selectedProvider === 'card' ? 'border-blue-500 bg-blue-50/50' : 'border-slate-200'">
                            <input type="radio" name="provider" value="card" x-model="selectedProvider" class="hidden">
                            <span class="text-xs font-bold text-slate-800">Carte</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="text-[12px] font-bold text-slate-700 block mb-1.5">Montant à recharger (FCFA)</label>
                    <input type="number" step="1000" min="1000" placeholder="Ex: 50 000" required 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="text-[12px] font-bold text-slate-700 block mb-1.5">Numéro de Téléphone (Orange / Wave)</label>
                    <input type="text" placeholder="Ex: +225 07 00 00 00 00" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-800 outline-none focus:border-blue-500">
                </div>

                <button type="submit" @click="rechargeModal = false" class="w-full bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs py-3.5 rounded-xl transition shadow-md">
                    Procéder au Paiement
                </button>
            </form>
        </div>
    </div>
</div>

<!-- IA INSIGHT - ÉCHÉANCES PROCHES BANNER -->
<div class="bg-blue-50/80 border border-blue-100 rounded-3xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-start sm:items-center gap-3.5">
        <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-blue-500/30">
            <span class="material-symbols-outlined text-[20px]">smart_toy</span>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-blue-900">IA Insight &bull; Échéances Proches</span>
            </div>
            <p class="text-xs text-slate-700 mt-0.5 leading-relaxed">
                @if(isset($aiInsight) && $aiInsight)
                    Basé sur votre historique, nous vous rappelons que la tranche des frais de scolarité pour <strong class="text-slate-900">{{ $aiInsight['kidName'] }} ({{ $aiInsight['className'] }})</strong> est attendue le <strong class="text-slate-900">{{ $aiInsight['dueDate'] }}</strong>. Votre solde actuel ({{ number_format($aiInsight['walletBalance'], 0, ',', ' ') }} F) @if($aiInsight['covers']) couvre cette dépense ({{ number_format($aiInsight['amount'], 0, ',', ' ') }} F). @else est insuffisant pour couvrir cette dépense ({{ number_format($aiInsight['amount'], 0, ',', ' ') }} F). @endif
                @else
                    Toutes vos échéances de scolarité sont à jour. Vous pouvez recharger votre portefeuille à tout moment pour anticiper les prochains règlements.
                @endif
            </p>
        </div>
    </div>

    @if(isset($aiInsight) && $aiInsight)
    <a href="{{ route('parent.fees', $aiInsight['studentId']) }}" 
       class="shrink-0 bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition shadow-md whitespace-nowrap self-start sm:self-auto">
        Payer Maintenant
    </a>
    @endif
</div>

<!-- MIDDLE GRID: ÉTAT DES FRAIS DE SCOLARITÉ + RÉCENT -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    
    <!-- ÉTAT DES FRAIS DE SCOLARITÉ (Col 8) -->
    <div class="lg:col-span-8 bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                    <span class="material-symbols-outlined text-[19px]">receipt_long</span>
                </div>
                <h2 class="text-sm font-extrabold text-slate-900">État des Frais de Scolarité</h2>
            </div>
            @if($academicYear)
            <span class="text-xs font-bold bg-slate-100 text-slate-600 px-3 py-1 rounded-xl">Année {{ $academicYear }}</span>
            @endif
        </div>

        <div class="space-y-3.5">
            @forelse($children as $kid)
            <div class="p-4 rounded-2xl border border-slate-100 hover:border-blue-100 hover:bg-slate-50/40 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-11 h-11 rounded-2xl overflow-hidden shrink-0 shadow-sm bg-gradient-to-tr from-slate-800 to-slate-950 flex items-center justify-center text-white font-bold text-xs">
                        @if($kid->photo_path)
                            <img src="{{ asset('storage/' . $kid->photo_path) }}" alt="{{ $kid->first_name }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($kid->first_name, 0, 1) }}{{ substr($kid->last_name, 0, 1) }}
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-extrabold text-slate-900 truncate">{{ $kid->first_name }} {{ $kid->last_name }}</h3>
                        <p class="text-xs font-medium text-slate-500">Classe: <span class="font-bold text-slate-700">{{ $kid->academicClass->name ?? 'Non assignée' }}</span></p>
                    </div>
                </div>

                <div class="flex items-center justify-between sm:justify-end gap-6 text-right">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Annuel</span>
                        <span class="text-sm font-black text-slate-800">{{ number_format($kid->feeTotal ?? 0, 0, ',', ' ') }} F</span>
                    </div>

                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Prochaine Tranche</span>
                        <span class="text-sm font-black text-slate-800">
                            @if(isset($kid->nextTranche) && $kid->nextTranche)
                                {{ number_format($kid->nextTranche['amount'], 0, ',', ' ') }} F
                            @else
                                —
                            @endif
                        </span>
                    </div>

                    <div>
                        @if(($kid->feeStatus ?? '') === 'paid')
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                <span class="material-symbols-outlined text-[14px]">check</span>
                                <span>Payé</span>
                            </span>
                        @else
                            <a href="{{ route('parent.fees', $kid->id) }}" 
                               class="inline-flex items-center gap-1 text-[11px] font-bold px-3 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200/60 hover:bg-amber-100 transition">
                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                <span>En Attente</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-400 text-xs">
                Aucun enfant rattaché.
            </div>
            @endforelse
        </div>
    </div>

    <!-- RÉCENT (Col 4) -->
    <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-blue-600">history</span>
                    <h2 class="text-sm font-extrabold text-slate-900">Récent</h2>
                </div>
                <a href="#table-transactions" class="text-xs font-bold text-blue-600 hover:underline">Voir tout</a>
            </div>

            <div class="space-y-3.5">
                @foreach($recentTransactions as $tx)
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 {{ $tx['is_positive'] ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600' }}">
                            <span class="material-symbols-outlined text-[18px]">
                                {{ $tx['is_positive'] ? 'arrow_downward' : ($tx['type'] === 'BOUTIQUE' ? 'shopping_bag' : 'arrow_upward') }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-800 truncate leading-tight">{{ $tx['description'] }}</p>
                            <p class="text-[11px] font-medium text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($tx['date'])->translatedFormat('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-xs font-black {{ $tx['is_positive'] ? 'text-emerald-600' : 'text-slate-800' }}">
                            {{ $tx['is_positive'] ? '+' : '-' }} {{ number_format($tx['amount'], 0, ',', ' ') }} F
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100">
            <button type="button" onclick="window.print()" 
                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-100/70 hover:bg-blue-200/80 text-[#061536] font-bold text-xs py-2.5 rounded-xl transition">
                <span class="material-symbols-outlined text-[17px]">download</span>
                <span>Relevé Mensuel</span>
            </button>
        </div>
    </div>

</div>

<!-- BOTTOM SECTION: HISTORIQUE COMPLET DES TRANSACTIONS -->
<div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]" id="table-transactions" x-data="{ typeFilter: 'all' }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <h2 class="text-sm font-extrabold text-slate-900">Historique Complet des Transactions</h2>

        <!-- FILTERS -->
        <div class="flex items-center gap-3">
            <select x-model="typeFilter" 
                    class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-2 outline-none focus:border-blue-500">
                <option value="all">Tous les types</option>
                <option value="PAIEMENT">Paiements</option>
                <option value="RECHARGE">Recharges</option>
                <option value="BOUTIQUE">Boutique</option>
            </select>

            <div class="relative">
                <input type="date" value="{{ now()->toDateString() }}" 
                       class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-2 outline-none focus:border-blue-500">
            </div>
        </div>
    </div>

    <!-- TRANSACTIONS TABLE -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                    <th class="py-3 px-4">Date</th>
                    <th class="py-3 px-4">Description</th>
                    <th class="py-3 px-4">Type</th>
                    <th class="py-3 px-4 text-right">Montant (F CFA)</th>
                    <th class="py-3 px-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                @forelse($transactions as $tx)
                <tr class="hover:bg-slate-50/60 transition" x-show="typeFilter === 'all' || typeFilter === '{{ $tx['type'] }}'">
                    <td class="py-3.5 px-4 font-semibold text-slate-600 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($tx['date'])->translatedFormat('d M Y, H:i') }}
                    </td>
                    <td class="py-3.5 px-4 font-bold text-slate-900">
                        {{ $tx['description'] }}
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-block px-2.5 py-0.5 rounded-md text-[10.5px] font-extrabold uppercase {{ $tx['type_badge'] }}">
                            {{ $tx['type'] }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-right font-black {{ $tx['is_positive'] ? 'text-emerald-600' : 'text-slate-900' }}">
                        {{ $tx['is_positive'] ? '+' : '-' }} {{ number_format($tx['amount'], 0, ',', ' ') }}
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        <button type="button" onclick="window.print()" title="Télécharger le reçu"
                                class="text-slate-400 hover:text-blue-600 transition">
                            <span class="material-symbols-outlined text-[19px]">download</span>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-slate-400">Aucune transaction trouvée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION FOOTER -->
    <div class="flex items-center justify-between pt-5 mt-4 border-t border-slate-100 text-xs text-slate-500">
        <span>Affichage 1-{{ count($transactions) }} sur {{ count($transactions) }} transactions</span>
        <div class="flex items-center gap-1 font-bold">
            <button type="button" class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-slate-100 transition"><i class="ph-bold ph-caret-left"></i></button>
            <span class="w-7 h-7 rounded-lg bg-[#061536] text-white flex items-center justify-center">1</span>
            <button type="button" class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-slate-100 transition"><i class="ph-bold ph-caret-right"></i></button>
        </div>
    </div>
</div>

@endsection
