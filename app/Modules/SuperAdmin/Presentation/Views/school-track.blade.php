@extends('SuperAdmin::layouts.app')

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[28px] font-extrabold text-[#111827]">School Track</h2>
            <p class="text-[15px] text-slate-500 mt-1">Module payant de découverte/comparaison d'écoles réservé aux parents (web &amp; mobile).</p>
        </div>
        <form action="{{ route('superadmin.school-track.toggle') }}" method="POST" class="shrink-0">
            @csrf
            @method('PATCH')
            <button type="submit" class="flex items-center gap-3 bg-white border border-slate-200 rounded-xl px-4 py-2.5 shadow-xs hover:bg-slate-50 transition">
                <span class="text-xs font-bold text-slate-700">{{ $moduleEnabled ? 'Module actif' : 'Module désactivé' }}</span>
                <span class="w-9 h-5 rounded-full relative transition-colors {{ $moduleEnabled ? 'bg-[#031C5B]' : 'bg-slate-200' }}">
                    <span class="absolute top-[2px] left-[2px] bg-white border border-slate-300 rounded-full h-4 w-4 transition-all {{ $moduleEnabled ? 'translate-x-full border-white' : '' }}"></span>
                </span>
            </button>
        </form>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-2">
            <i class="ph ph-check-circle text-lg text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif

    @unless($moduleEnabled)
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs font-semibold flex items-center gap-2">
            <i class="ph ph-warning-circle text-lg text-amber-600"></i>
            Le module est désactivé : plus aucun parent n'y a accès sur le web ou l'app mobile, même avec un abonnement actif.
        </div>
    @endunless

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Revenu Total</p>
                <i class="ph ph-money text-2xl text-emerald-500 font-bold"></i>
            </div>
            <h3 class="text-[28px] font-extrabold text-[#0f172a] leading-none">
                {{ number_format($kpis['total_revenue'], 0, ',', ' ') }} {{ $systemCurrency ?? 'FCFA' }}
            </h3>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Abonnés Actifs</p>
                <i class="ph ph-users text-2xl text-blue-500 font-bold"></i>
            </div>
            <h3 class="text-[28px] font-extrabold text-[#0f172a] leading-none">{{ $kpis['active_count'] }}</h3>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Plan Mensuel</p>
                <i class="ph ph-calendar text-2xl text-indigo-500 font-bold"></i>
            </div>
            <h3 class="text-[28px] font-extrabold text-[#0f172a] leading-none">{{ $kpis['monthly_count'] }}</h3>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Plan Annuel</p>
                <i class="ph ph-calendar-check text-2xl text-purple-500 font-bold"></i>
            </div>
            <h3 class="text-[28px] font-extrabold text-[#0f172a] leading-none">{{ $kpis['yearly_count'] }}</h3>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap items-center gap-3 mb-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un parent…" class="border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold w-64">
        <select name="status" class="border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold">
            <option value="">Tous les statuts</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expiré</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
        </select>
        <select name="plan" class="border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold">
            <option value="">Tous les plans</option>
            <option value="mensuel" {{ request('plan') === 'mensuel' ? 'selected' : '' }}>Mensuel</option>
            <option value="annuel" {{ request('plan') === 'annuel' ? 'selected' : '' }}>Annuel</option>
        </select>
        <button type="submit" class="px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-xs font-bold hover:bg-blue-950 transition">Filtrer</button>
    </form>

    {{-- Subscriptions table --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
        <div class="p-6 border-b border-slate-200 bg-[#FCFDFE]">
            <h3 class="text-[20px] font-extrabold text-[#111827]">Abonnements</h3>
            <p class="text-xs text-slate-500 mt-0.5">Historique des souscriptions parents.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                <thead>
                    <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-widest bg-slate-50 border-b border-slate-200">
                        <th class="py-4 px-6">PARENT</th>
                        <th class="py-4 px-4">PLAN</th>
                        <th class="py-4 px-4">MONTANT</th>
                        <th class="py-4 px-4">MOYEN DE PAIEMENT</th>
                        <th class="py-4 px-4">SOUSCRIT LE</th>
                        <th class="py-4 px-4">EXPIRE LE</th>
                        <th class="py-4 px-6 text-center">STATUT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[14px]">
                    @forelse($subscriptions as $sub)
                        @php
                            $badgeClass = match($sub->status) {
                                'active' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
                                default => 'bg-amber-100 text-amber-800 border-amber-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6 font-bold text-slate-900">{{ $sub->parent->name ?? '—' }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-700">{{ ucfirst($sub->plan) }}</td>
                            <td class="py-4 px-4 font-extrabold text-slate-900">{{ number_format($sub->amount_paid, 0, ',', ' ') }} {{ $systemCurrency ?? 'FCFA' }}</td>
                            <td class="py-4 px-4 text-slate-600 font-medium">{{ \App\Modules\Finance\Domain\Models\Payment::METHODS[$sub->payment_method] ?? $sub->payment_method }}</td>
                            <td class="py-4 px-4 text-slate-600 font-medium">{{ $sub->subscribed_at?->format('d/m/Y') }}</td>
                            <td class="py-4 px-4 text-slate-600 font-medium">{{ $sub->expires_at?->format('d/m/Y') }}</td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center text-[11px] font-bold px-3 py-1 rounded-full {{ $badgeClass }} border">
                                    {{ ucfirst($sub->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-sm">Aucun abonnement pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $subscriptions->links() }}
        </div>
        @endif
    </div>
@endsection
