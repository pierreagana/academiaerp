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
    <a href="{{ route('parent.bulletin', $kid->id) }}" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 hover:border-[#031C5B]/30 transition block">
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
