@extends('ParentPortal::layout')

@section('title', 'Cantine Scolaire - ' . $child->first_name)

@section('content')

<!-- HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Cantine Scolaire &bull; {{ $child->first_name }}</h1>
        <p class="text-sm font-medium text-slate-500 mt-0.5">Menu hebdomadaire, réservations et solde repas.</p>
    </div>
</div>

@unless($canteenEnrolled)
<div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] p-6 mb-6">
    @if($canteenEnrollmentRequest && $canteenEnrollmentRequest->status === 'pending')
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-800 text-xs font-bold flex items-center gap-2.5">
            <span class="material-symbols-outlined text-[18px]">hourglass_empty</span>
            <span>Votre demande d'inscription à la cantine est actuellement en cours de traitement par l'établissement.</span>
        </div>
    @else
        @if($canteenEnrollmentRequest && $canteenEnrollmentRequest->status === 'withdrawn')
            <p class="text-slate-500 text-xs mb-4">Cet élève a été désinscrit de la cantine. Vous pouvez réitérer votre demande dès maintenant.</p>
        @else
            @if($canteenEnrollmentRequest && $canteenEnrollmentRequest->status === 'rejected' && $canteenEnrollmentRequest->rejection_reason)
                <div class="p-4 mb-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-700 text-xs font-medium">
                    <strong class="font-bold">Demande précédente refusée :</strong> {{ $canteenEnrollmentRequest->rejection_reason }}
                </div>
            @endif
            <p class="text-slate-600 text-xs mb-4 leading-relaxed">Cet élève n'est pas encore inscrit au service de restauration scolaire. Transmettez une demande d'inscription pour lui permettre de déjeuner au réfectoire.</p>
        @endif
        
        <form method="POST" action="{{ route('parent.canteen.request', $child->id) }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs px-5 py-3 rounded-2xl transition shadow-md">
                <span class="material-symbols-outlined text-[16px]">restaurant</span>
                <span>Demander l'inscription à la cantine</span>
            </button>
        </form>
    @endif
</div>
@endunless

<!-- CANTEEN BALANCE TILE -->
<div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
            <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 block">Solde Compte Cantine</span>
            <div class="text-2xl font-black {{ ($account?->balance ?? 0) < 0 ? 'text-rose-600' : 'text-slate-900' }}">
                {{ number_format($account->balance ?? 0, 0, ',', ' ') }} FCFA
            </div>
        </div>
    </div>

    <a href="{{ route('parent.finance') }}" class="text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 px-3.5 py-2 rounded-xl transition">
        Recharger
    </a>
</div>

<!-- ORDERING WINDOW STATUS BANNER -->
@php
    $isOrderingWindow = now()->hour >= 18 && now()->hour < 20;
@endphp
<div class="mb-6 p-4 rounded-2xl border {{ $isOrderingWindow ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-blue-50/70 border-blue-100 text-blue-900' }} flex items-center gap-3">
    <span class="material-symbols-outlined text-[20px] {{ $isOrderingWindow ? 'text-emerald-600' : 'text-blue-600' }}">schedule</span>
    <div class="text-xs">
        <strong class="font-bold">{{ $isOrderingWindow ? 'Réservations ouvertes :' : 'Créneau horaire de réservation :' }}</strong>
        {{ $isOrderingWindow ? 'Vous pouvez réserver ou modifier les repas jusqu\'à 20h00 ce soir.' : 'Les réservations et annulations de repas s\'effectuent chaque jour entre 18h00 et 20h00.' }}
    </div>
</div>

<!-- WEEK MENU -->
<div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden mb-6">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-sm font-extrabold text-slate-900">Menu de la Semaine</h2>
        <span class="text-xs font-bold bg-slate-100 text-slate-600 px-3 py-1 rounded-xl">Semaine en cours</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <tbody class="divide-y divide-slate-100 text-xs">
                @forelse($weekMenu as $item)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4 font-bold text-slate-900 w-36">{{ $item->date->translatedFormat('D d M') }}</td>
                        <td class="px-4 py-4 font-semibold text-slate-500 w-28">{{ ucfirst($item->slot) }}</td>
                        <td class="px-4 py-4 font-bold text-slate-800">{{ $item->title }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-8 text-center text-slate-400">Le menu n'a pas encore été publié pour cette semaine.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- RECENT MEALS HISTORY -->
<div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden">
    <div class="p-5 border-b border-slate-100">
        <h2 class="text-sm font-extrabold text-slate-900">Derniers Repas Enregistrés</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <tbody class="divide-y divide-slate-100 text-xs">
                @forelse($recentMeals as $meal)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4 font-bold text-slate-900">{{ $meal->date->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-4 text-right font-black text-slate-800">- {{ number_format($meal->price, 0, ',', ' ') }} FCFA</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-5 py-8 text-center text-slate-400">Aucun repas consommé récemment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
