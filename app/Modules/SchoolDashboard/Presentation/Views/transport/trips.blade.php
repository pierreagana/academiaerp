@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6" x-data="{ logOpen: false }">
    @include('SchoolDashboard::transport._tabs')

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Journal des Trajets</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Suivez la ponctualité, la présence et les incidents.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('school.transport.trips.export') }}" class="inline-flex items-center gap-2 px-4 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-[13px] font-bold hover:bg-slate-50 transition">
                <i class="ph-bold ph-download-simple"></i> Exporter le Rapport
            </a>
            <button @click="logOpen = true" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
                <i class="ph-bold ph-plus"></i> Journaliser un Trajet
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Résumé 7 derniers jours -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Taux de Ponctualité</p>
            <h3 class="text-3xl font-bold text-slate-800">{{ $summary['punctualityRate'] !== null ? $summary['punctualityRate'] . '%' : '—' }}</h3>
            <p class="text-[12px] text-slate-400 font-semibold mt-1">7 derniers jours · {{ $summary['totalTrips'] }} trajet{{ $summary['totalTrips'] > 1 ? 's' : '' }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Présence Moyenne</p>
            <h3 class="text-3xl font-bold text-slate-800">{{ $summary['averageAttendance'] !== null ? $summary['averageAttendance'] . '%' : '—' }}</h3>
            <p class="text-[12px] text-slate-400 font-semibold mt-1">Rapport présence / attendus</p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Incidents Signalés</p>
            <h3 class="text-3xl font-bold text-slate-800">{{ $summary['incidentCount'] }}</h3>
            <p class="text-[12px] text-slate-400 font-semibold mt-1">7 derniers jours</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3 flex-wrap">
        <select name="bus_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] outline-none focus:border-[#031C5B]">
            <option value="">Tous les bus</option>
            @foreach($buses as $bus)
                <option value="{{ $bus->id }}" {{ ($filters['bus_id'] ?? null) == $bus->id ? 'selected' : '' }}>{{ $bus->bus_number }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] outline-none focus:border-[#031C5B]">
        @if(!empty($filters['bus_id']) || !empty($filters['date']))
            <a href="{{ route('school.transport.trips') }}" class="text-[12.5px] font-bold text-slate-500 hover:text-slate-700">Réinitialiser</a>
        @endif
    </form>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-3">
            @php
                $statusStyles = ['complete' => 'border-slate-100', 'incident' => 'border-red-200 bg-red-50/40'];
            @endphp
            @forelse($trips as $trip)
            <div class="bg-white rounded-2xl border shadow-sm p-5 {{ $statusStyles[$trip->status] ?? '' }}">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="text-[14.5px] font-bold text-slate-800">{{ \App\Modules\Transport\Domain\Models\TripLog::SHIFTS[$trip->shift] ?? $trip->shift }} — {{ $trip->bus->bus_number ?? 'Bus non assigné' }}</p>
                        <p class="text-[12px] text-slate-500 mt-0.5">{{ $trip->route->name ?? 'Route non assignée' }} · {{ $trip->trip_date->format('d M Y') }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $trip->status === 'incident' ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ \App\Modules\Transport\Domain\Models\TripLog::STATUSES[$trip->status] ?? $trip->status }}
                    </span>
                </div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="bg-slate-50 rounded-lg py-2.5">
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Présence</p>
                        <p class="text-[14px] font-extrabold text-slate-800 mt-0.5">{{ $trip->attendance_count ?? '-' }}/{{ $trip->expected_count ?? '-' }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-lg py-2.5">
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Distance</p>
                        <p class="text-[14px] font-extrabold text-slate-800 mt-0.5">{{ $trip->distance_km ? $trip->distance_km . ' km' : '-' }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-lg py-2.5">
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Chauffeur</p>
                        <p class="text-[14px] font-extrabold text-slate-800 mt-0.5">{{ $trip->bus->driver->first_name ?? '-' }}</p>
                    </div>
                </div>
                @if($trip->status === 'incident' && $trip->incident_notes)
                <p class="text-[12.5px] text-red-700 mt-3 flex items-start gap-1.5"><i class="ph-fill ph-warning mt-0.5"></i> {{ $trip->incident_notes }}</p>
                @endif
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-500 font-medium">Aucun trajet journalisé.</div>
            @endforelse

            <div class="mt-2">{{ $trips->appends($filters)->links() }}</div>
        </div>

        <!-- Analyse Prédictive IA -->
        <div class="bg-gradient-to-br from-[#F5F3FF] to-purple-50/50 border border-purple-100 rounded-2xl p-5 shadow-sm h-fit">
            <div class="flex items-center gap-2 mb-3">
                <i class="ph-fill ph-sparkle text-purple-600 text-lg"></i>
                <h3 class="font-extrabold text-slate-800 text-[15px]">Analyse Prédictive IA</h3>
            </div>
            <p class="text-[13px] text-slate-700 leading-relaxed">Les retards sont historiquement plus fréquents en début de semaine et par forte pluie. Anticipez les tournées du lundi matin.</p>
        </div>
    </div>

    @include('SchoolDashboard::transport._log_trip_modal')
</div>
@endsection
