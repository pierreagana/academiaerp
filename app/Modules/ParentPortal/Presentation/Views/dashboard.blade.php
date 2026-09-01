@extends('ParentPortal::layout')

@section('title', 'Mes Enfants')

@section('content')

@php
    $feeBadge = [
        'paid' => ['label' => 'À jour', 'class' => 'bg-emerald-50 text-emerald-700'],
        'late' => ['label' => 'En retard', 'class' => 'bg-red-50 text-red-700'],
        'partial' => ['label' => 'Partiel', 'class' => 'bg-amber-50 text-amber-700'],
        'pending' => ['label' => 'En attente', 'class' => 'bg-slate-100 text-slate-500'],
        'unconfigured' => ['label' => '—', 'class' => 'bg-slate-100 text-slate-400'],
    ];
@endphp

<h1 class="text-[26px] font-bold text-slate-900 mb-1">Bonjour, {{ $parent->name }} !</h1>
<p class="text-[14px] text-slate-500 mb-6">Voici un résumé de la scolarité de vos enfants.</p>

@php
    $stActive = $schoolTrackStatus['active'];
    $stEnabled = $schoolTrackStatus['moduleEnabled'];
    $stLocked = $stEnabled && !$stActive;
@endphp
<div class="mb-6" x-data="{ open: false }">
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 flex items-center justify-between gap-4 {{ !$stActive ? 'opacity-70' : '' }} {{ $stLocked ? 'cursor-pointer hover:border-[#031C5B]/20' : '' }}"
        @if($stLocked) x-on:click="open = true" @endif>
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-11 h-11 rounded-2xl {{ $stActive ? 'bg-violet-50 text-violet-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center shrink-0">
                <i class="ph-fill ph-compass text-[22px]"></i>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h2 class="text-[16px] font-extrabold {{ $stActive ? 'text-slate-800' : 'text-slate-500' }}">School Track</h2>
                    @if($stActive)
                        <span class="text-[10.5px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600">Actif</span>
                    @elseif(!$stEnabled)
                        <span class="text-[10.5px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full bg-slate-100 text-slate-400">Indisponible</span>
                    @else
                        <span class="text-[10.5px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full bg-slate-100 text-slate-400">Verrouillé</span>
                    @endif
                </div>
                <p class="text-[13px] {{ $stActive ? 'text-slate-500' : 'text-slate-400' }}">
                    @if($stActive)
                        Abonnement actif jusqu'au {{ \Carbon\Carbon::parse($schoolTrackStatus['expiresAt'])->translatedFormat('d M Y') }}. Retrouvez la comparaison d'écoles dans l'application mobile.
                    @elseif(!$stEnabled)
                        Ce module n'est pas disponible pour le moment.
                    @else
                        Découvrez, comparez et trouvez la meilleure école pour vos enfants.
                    @endif
                </p>
            </div>
        </div>
        @if($stLocked)
        <i class="ph-bold ph-lock-simple text-slate-300 text-[20px] shrink-0"></i>
        @endif
    </div>

    @if($stLocked)
    <div x-show="open" style="display:none;" class="fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4" x-on:keydown.escape.window="open=false">
        <div class="bg-white rounded-3xl max-w-md w-full p-6" x-on:click.outside="open=false">
            <div class="flex items-start justify-between mb-3">
                <h3 class="text-[18px] font-extrabold text-slate-900">School Track</h3>
                <button type="button" x-on:click="open=false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-[18px]"></i></button>
            </div>
            <p class="text-[13.5px] text-slate-500 mb-5">Comparez les écoles à proximité, consultez leurs taux de réussite, équipements et avis pour choisir le meilleur établissement pour vos enfants. La recherche et la comparaison se font depuis l'application mobile une fois votre abonnement actif.</p>

            <form action="{{ route('parent.school-track.subscribe') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <p class="text-[12px] font-bold text-slate-600 mb-2">Choisissez un plan</p>
                    <div class="space-y-2">
                        @foreach($schoolTrackStatus['plans'] as $planKey => $plan)
                        <label class="flex items-center justify-between gap-2 border border-slate-200 rounded-xl px-3 py-2.5 cursor-pointer">
                            <span class="flex items-center gap-2">
                                <input type="radio" name="plan" value="{{ $planKey }}" {{ $loop->first ? 'checked' : '' }} class="accent-[#031C5B]">
                                <span class="text-[13px] font-bold text-slate-700">{{ $plan['label'] }}</span>
                            </span>
                            <span class="text-[13px] font-extrabold text-slate-800">{{ number_format($plan['price'], 0, ',', ' ') }} FCFA</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="text-[12px] font-bold text-slate-600 mb-2">Moyen de paiement</p>
                    <select name="payment_method" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-[13px] font-bold text-slate-700">
                        @foreach($schoolTrackStatus['paymentMethods'] as $methodKey => $methodLabel)
                        <option value="{{ $methodKey }}" {{ $methodKey === 'cash' ? 'selected' : '' }}>{{ $methodLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13.5px] py-3 rounded-xl transition">Souscrire maintenant</button>
            </form>
        </div>
    </div>
    @endif
</div>

@if($children->isEmpty())
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400 text-[13.5px]">
    <p class="mb-4">Aucun enfant n'est encore rattaché à votre compte.</p>
    <a href="{{ route('parent.children.add-form') }}" class="inline-flex items-center gap-1.5 bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-5 py-2.5 rounded-xl transition">
        <i class="ph-bold ph-user-plus"></i> Ajouter un enfant
    </a>
</div>
@else

<!-- Child switcher pills -->
<div class="flex items-center gap-2 mb-6 overflow-x-auto pb-1">
    @foreach($children as $kid)
    <a href="{{ route('parent.bulletin', $kid->id) }}" class="shrink-0 flex items-center gap-2 pl-2 pr-4 py-2 rounded-full bg-white border border-slate-200 hover:border-[#031C5B]/30 transition">
        @if($kid->photo_path)
            <img src="{{ asset('storage/' . $kid->photo_path) }}" class="w-7 h-7 rounded-full object-cover">
        @else
            <span class="w-7 h-7 rounded-full bg-[#EEF2F6] text-[#334155] font-bold text-[11px] flex items-center justify-center">{{ substr($kid->first_name, 0, 1) }}{{ substr($kid->last_name, 0, 1) }}</span>
        @endif
        <span class="text-[13px] font-bold text-slate-700">{{ $kid->first_name }}</span>
    </a>
    @endforeach
</div>

<!-- AI advisor placeholder (no real AI layer wired yet — shown disabled, no fabricated content) -->
<div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-6 opacity-60">
    <div class="flex items-center gap-3 mb-2">
        <div class="w-11 h-11 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
            <i class="ph-fill ph-brain text-[22px]"></i>
        </div>
        <div class="flex items-center gap-2">
            <h2 class="text-[16px] font-extrabold text-slate-500">Conseiller IA Famille</h2>
            <span class="text-[10.5px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full bg-slate-100 text-slate-400">Bientôt</span>
        </div>
    </div>
    <p class="text-[13px] text-slate-400">Des analyses personnalisées sur la progression de vos enfants seront bientôt disponibles ici.</p>
</div>

<!-- Per-child stat cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    @foreach($children as $kid)
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 hover:border-[#031C5B]/30 transition">
        <a href="{{ route('parent.bulletin', $kid->id) }}" class="block">
            <div class="flex items-center gap-3 mb-5">
                @if($kid->photo_path)
                    <img src="{{ asset('storage/' . $kid->photo_path) }}" class="w-11 h-11 rounded-full object-cover">
                @else
                    <span class="w-11 h-11 rounded-full bg-[#EEF2F6] text-[#334155] font-bold text-[14px] flex items-center justify-center">{{ substr($kid->first_name, 0, 1) }}{{ substr($kid->last_name, 0, 1) }}</span>
                @endif
                <div class="min-w-0">
                    <p class="font-bold text-slate-900 text-[14.5px] truncate">{{ $kid->first_name }} {{ $kid->last_name }}</p>
                    <p class="text-[12px] text-slate-500 truncate">{{ $kid->academicClass->name ?? '—' }}</p>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-[12.5px] text-slate-500">Moyenne</span>
                    <span class="text-[15px] font-extrabold text-[#031C5B]">{{ $kid->average !== null ? number_format($kid->average, 1) . '/20' : '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[12.5px] text-slate-500">Présence</span>
                    <span class="text-[12.5px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">{{ $kid->attendanceRate !== null ? $kid->attendanceRate . '%' : '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[12.5px] text-slate-500">Scolarité</span>
                    <span class="text-[11.5px] font-bold px-2.5 py-1 rounded-full {{ $feeBadge[$kid->feeStatus]['class'] }}">{{ $feeBadge[$kid->feeStatus]['label'] }}</span>
                </div>
            </div>
        </a>
        @if($kid->latestAward)
        <a href="{{ route('parent.diplomes', $kid->id) }}" class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100 group">
            <span class="text-[12.5px] text-slate-500 group-hover:text-slate-700 transition">Dernier diplôme</span>
            <span class="text-[11.5px] font-bold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 flex items-center gap-1"><i class="ph-fill ph-medal"></i> {{ \Illuminate\Support\Str::limit($kid->latestAward->type->name ?? '', 22) }}</span>
        </a>
        @endif
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Upcoming homework/exams -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-[15px] font-extrabold text-slate-800 mb-4">Devoirs & Examens imminents</h2>
        <div class="space-y-3">
            @forelse($upcoming as $item)
            <a href="{{ route('parent.homework', $item->studentId) }}" class="flex items-start gap-2.5 group">
                <span class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ $item->type === 'interrogation' ? 'bg-red-500' : 'bg-blue-500' }}"></span>
                <div class="min-w-0">
                    <p class="text-[13px] font-bold text-slate-800 group-hover:text-[#031C5B] transition truncate">{{ $item->title }} ({{ $item->studentFirstName }})</p>
                    <p class="text-[11.5px] text-slate-400">{{ $item->scheduled_at?->translatedFormat('D d M, H:i') }}</p>
                </div>
            </a>
            @empty
            <p class="text-[13px] text-slate-400">Aucun devoir ou examen à venir.</p>
            @endforelse
        </div>
    </div>

    <!-- School news -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-[15px] font-extrabold text-slate-800 mb-4">Dernières actualités</h2>
        <div class="space-y-4">
            @forelse($news as $event)
            <div>
                <p class="text-[11px] font-bold text-[#031C5B] uppercase tracking-wide">{{ $event->school->name ?? '—' }}</p>
                <p class="text-[13px] text-slate-700"><span class="font-bold">{{ $event->title }}</span>@if($event->description) — {{ \Illuminate\Support\Str::limit($event->description, 80) }}@endif</p>
                <p class="text-[11.5px] text-slate-400 mt-0.5">{{ $event->start_at->translatedFormat('d M Y') }}</p>
            </div>
            @empty
            <p class="text-[13px] text-slate-400">Aucune actualité pour le moment.</p>
            @endforelse
        </div>
    </div>
</div>

@endif
@endsection
