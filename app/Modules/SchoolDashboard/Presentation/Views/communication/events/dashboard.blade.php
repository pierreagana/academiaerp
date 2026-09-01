@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::communication.events._tabs')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Vue d'ensemble stratégique</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Gérez et suivez les performances de vos événements scolaires.</p>
        </div>
        <a href="{{ route('school.communication.events.create') }}" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
            <i class="ph-bold ph-plus text-lg"></i>
            Nouvel Événement
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center text-[#031C5B]">
                    <i class="ph-bold ph-arrows-left-right text-xl"></i>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 uppercase tracking-wider">Ce mois</span>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ number_format($stats['events_this_month'], 0, ',', ' ') }}</h3>
            <p class="text-[13px] text-slate-500 font-semibold mt-1">Événements Prévus</p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                    <i class="ph-bold ph-users-three text-xl"></i>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 uppercase tracking-wider">Toutes classes</span>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $stats['participation_rate'] }}%</h3>
            <p class="text-[13px] text-slate-500 font-semibold mt-1">Taux de Participation</p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                    <i class="ph-bold ph-wallet text-xl"></i>
                </div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ number_format($stats['budget_engaged'], 0, ',', ' ') }} <span class="text-base text-slate-400 font-semibold">FCFA</span></h3>
            <p class="text-[13px] text-slate-500 font-semibold mt-1">Budget Engagé</p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center text-red-600">
                    <i class="ph-bold ph-calendar-check text-xl"></i>
                </div>
                @if($stats['pending_validation'] > 0)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-600 text-white uppercase tracking-wider">Action Requise</span>
                @endif
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $stats['pending_validation'] }}</h3>
            <p class="text-[13px] text-slate-500 font-semibold mt-1">En Attente de Validation</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Répartition par Type -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Répartition par Type</h3>
            @php
                $typeColors = ['scolaire' => '#1E3A8A', 'sportif' => '#7C3AED', 'culturel' => '#047857', 'administratif' => '#CBD5E1'];
                $typeLabels = ['scolaire' => 'Scolaire', 'sportif' => 'Sportif', 'culturel' => 'Culturel', 'administratif' => 'Administratif'];
                $gradientParts = [];
                $cursor = 0;
                foreach ($typeBreakdown as $t) {
                    $color = $typeColors[$t['type']] ?? '#94A3B8';
                    $start = $cursor;
                    $cursor += $t['percent'];
                    $gradientParts[] = "{$color} {$start}% {$cursor}%";
                }
                $gradient = count($gradientParts) ? implode(', ', $gradientParts) : '#E2E8F0 0% 100%';
                $totalEvents = collect($typeBreakdown)->sum('count');
            @endphp
            <div class="flex items-center gap-8">
                <div class="w-36 h-36 rounded-full shrink-0 relative" style="background: conic-gradient({{ $gradient }});">
                    <div class="absolute inset-3 bg-white rounded-full flex items-center justify-center">
                        <span class="text-2xl font-bold text-slate-800">{{ $totalEvents }}</span>
                    </div>
                </div>
                <div class="space-y-2.5">
                    @forelse($typeBreakdown as $t)
                        <div class="flex items-center gap-2 text-[13px] font-semibold text-slate-600">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $typeColors[$t['type']] ?? '#94A3B8' }}"></span>
                            {{ $typeLabels[$t['type']] ?? $t['type'] }} ({{ $t['percent'] }}%)
                        </div>
                    @empty
                        <p class="text-slate-400 text-[13px]">Aucun événement enregistré.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Aperçu (données réelles) -->
        <div class="bg-gradient-to-br from-[#F5F3FF] to-purple-50/50 border border-purple-100 rounded-2xl p-6 shadow-sm flex flex-col">
            <div class="flex items-center gap-2 mb-3">
                <i class="ph-fill ph-sparkle text-purple-600 text-xl"></i>
                <h3 class="font-extrabold text-slate-800 text-[16px]">Aperçu</h3>
            </div>
            <p class="text-[13px] text-slate-600 font-medium leading-relaxed flex-1">
                @if($scheduleGap)
                    Aucun événement planifié pendant {{ $scheduleGap['days'] }} jours à partir du {{ $scheduleGap['from'] }} — une fenêtre disponible pour organiser un nouvel événement.
                @else
                    Votre calendrier est déjà bien rempli sur la période à venir — aucune grande fenêtre libre détectée.
                @endif
                Taux de participation validée ce mois-ci : <strong>{{ $stats['participation_rate'] }}%</strong>.
            </p>
            <a href="{{ route('school.communication.events.create') }}" class="mt-5 inline-flex items-center justify-center gap-2 bg-purple-600 text-white px-4 py-2.5 rounded-xl text-[13px] font-bold hover:bg-purple-700 transition">
                <i class="ph-bold ph-calendar-plus"></i>
                Planifier un événement
            </a>
        </div>
    </div>

    <!-- Événements à venir -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Événements à venir</h3>
            <a href="{{ route('school.communication.events.calendar') }}" class="text-[#031C5B] font-bold text-[13px] hover:underline">Voir tout</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Nom de l'événement</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Date & Heure</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Lieu</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($upcoming as $event)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <a href="{{ route('school.communication.events.show', $event->id) }}" class="text-[14px] font-bold text-[#031C5B] hover:underline">{{ $event->title }}</a>
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $event->start_at->format('d M Y, H:i') }}</td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $event->location_type === 'internal' ? ($event->room->name ?? '-') : ($event->external_address ?? '-') }}</td>
                        <td class="px-5 py-4">
                            @php
                                $statusStyles = ['draft' => 'bg-slate-100 text-slate-600', 'confirmed' => 'bg-emerald-100 text-emerald-700', 'pending' => 'bg-amber-100 text-amber-700', 'cancelled' => 'bg-red-100 text-red-700'];
                                $statusLabels = ['draft' => 'Brouillon', 'confirmed' => 'Confirmé', 'pending' => 'En attente', 'cancelled' => 'Annulé'];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $statusStyles[$event->status] ?? '' }}">
                                {{ $statusLabels[$event->status] ?? $event->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun événement à venir.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
