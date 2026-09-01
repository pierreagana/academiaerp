@extends('ParentPortal::layout')

@section('title', 'Cantine')

@section('content')
<h1 class="text-[22px] font-bold text-slate-900 mb-6">Cantine</h1>

@unless($canteenEnrolled)
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
    @if($canteenEnrollmentRequest && $canteenEnrollmentRequest->status === 'pending')
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[13.5px] font-bold flex items-center gap-2">
            <i class="ph-bold ph-hourglass"></i> Votre demande d'inscription à la cantine est en attente de validation par l'école.
        </div>
    @else
        @if($canteenEnrollmentRequest && $canteenEnrollmentRequest->status === 'withdrawn')
            <p class="text-slate-500 text-[13.5px] mb-4">Cet élève a été retiré de la cantine par l'école. Vous pouvez refaire une demande si vous le souhaitez.</p>
        @else
            @if($canteenEnrollmentRequest && $canteenEnrollmentRequest->status === 'rejected' && $canteenEnrollmentRequest->rejection_reason)
                <div class="p-4 mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-[13px]">
                    <span class="font-bold">Demande précédente refusée :</span> {{ $canteenEnrollmentRequest->rejection_reason }}
                </div>
            @endif
            <p class="text-slate-500 text-[13.5px] mb-4">Cet élève n'est pas encore inscrit à la cantine. Envoyez une demande d'inscription à l'école pour pouvoir réserver des repas.</p>
        @endif
        <form method="POST" action="{{ route('parent.canteen.request', $child->id) }}">
            @csrf
            <button type="submit" class="bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13.5px] px-5 py-2.5 rounded-xl transition">Demander l'inscription</button>
        </form>
    @endif
</div>
@endunless

<div class="bg-white border border-slate-100 rounded-2xl p-5 mb-6 flex items-center justify-between">
    <span class="text-[13.5px] font-bold text-slate-600">Solde du compte cantine</span>
    <span class="text-[22px] font-extrabold {{ ($account?->balance ?? 0) < 0 ? 'text-red-600' : 'text-[#031C5B]' }}">{{ number_format($account->balance ?? 0, 0, ',', ' ') }}</span>
</div>

@php
    $isOrderingWindow = now()->hour >= 18 && now()->hour < 20;
@endphp
<div class="mb-6 p-4 rounded-xl border {{ $isOrderingWindow ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-50 border-amber-200 text-amber-800' }} flex items-center gap-3">
    <i class="ph-bold {{ $isOrderingWindow ? 'ph-clock-countdown text-emerald-600' : 'ph-clock text-amber-600' }} text-xl"></i>
    <div class="text-[13px]">
        <span class="font-bold">{{ $isOrderingWindow ? 'Commandes et modifications ouvertes :' : 'Créneau horaire de réservation :' }}</span>
        {{ $isOrderingWindow ? 'Vous pouvez réserver ou modifier les repas jusqu\'à 20h00.' : 'Les choix et modifications de repas sont autorisés uniquement entre 18h00 et 20h00.' }}
    </div>
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
