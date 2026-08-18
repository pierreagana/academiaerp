@extends('SchoolDashboard::layouts.app')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div class="space-y-6" x-data="{ createOpen: false, editOpen: null }">
    @include('SchoolDashboard::transport._tabs')

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Itinéraires Actifs</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Gérez les routes de transport et leur affectation.</p>
        </div>
        <button @click="createOpen = true" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
            <i class="ph-bold ph-plus"></i> Nouvelle Route
        </button>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Aperçu Réseau -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Aperçu du Réseau</h3>
            </div>
            <div id="network-map" style="height: 340px;"></div>
        </div>

        <div class="space-y-6">
            <!-- Efficacité du Réseau -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Efficacité du Réseau</p>
                @if($efficiency)
                    <h3 class="text-3xl font-bold text-slate-800">{{ $efficiency['percent'] }}%</h3>
                    <p class="text-[12px] text-slate-400 font-semibold mt-1">Trajets à l'heure — {{ $efficiency['period'] }} ({{ $efficiency['totalTrips'] }} trajet{{ $efficiency['totalTrips'] > 1 ? 's' : '' }})</p>
                @else
                    <h3 class="text-2xl font-bold text-slate-400">—</h3>
                    <p class="text-[12px] text-slate-400 font-semibold mt-1">Pas encore de trajets journalisés</p>
                @endif
            </div>

            <!-- Suggestion IA -->
            <div class="bg-gradient-to-br from-[#F5F3FF] to-purple-50/50 border border-purple-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <i class="ph-fill ph-sparkle text-purple-600 text-lg"></i>
                    <h3 class="font-extrabold text-slate-800 text-[15px]">Suggestion IA</h3>
                </div>
                <p class="text-[13px] text-slate-700 leading-relaxed">Envisagez de fusionner les routes couvrant des zones géographiques proches afin de réduire la distance totale parcourue et le nombre de bus mobilisés aux heures de pointe.</p>
            </div>
        </div>
    </div>

    <!-- Liste des Routes -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Liste des Routes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Nom</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Zone</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Arrêts</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Distance</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Fréquence</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Assignation</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                @php
                    $routeStatusStyles = ['actif' => 'bg-emerald-100 text-emerald-700', 'planifie' => 'bg-blue-100 text-blue-700', 'inactif' => 'bg-slate-200 text-slate-600'];
                @endphp
                <tbody class="divide-y divide-slate-100">
                    @forelse($routes as $route)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <p class="text-[13.5px] font-bold text-slate-800">{{ $route->name }}</p>
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $route->zone ?? '-' }}</td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $route->stops_count }}</td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $route->distance_km ? $route->distance_km . ' km' : '-' }}</td>
                        <td class="px-5 py-4">
                            @if($route->schedule_type === 'occasional')
                                <span class="px-2 py-1 rounded-full text-[10.5px] font-bold bg-amber-100 text-amber-700">Occasionnel</span>
                            @else
                                <p class="text-[12px] font-semibold text-slate-600">
                                    {{ !empty($route->recurring_days) ? collect($route->recurring_days)->map(fn($d) => \App\Modules\Transport\Domain\Models\Route::DAYS[$d] ?? $d)->implode(', ') : 'Tous les jours' }}
                                </p>
                            @endif
                            @if($route->first_stop_time)
                                <p class="text-[11px] text-slate-400 mt-0.5">Départ {{ \Illuminate\Support\Carbon::parse($route->first_stop_time)->format('H:i') }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">
                            @if($route->bus)
                                {{ $route->bus->bus_number }}
                                @if($route->bus->driver) — {{ $route->bus->driver->first_name }} {{ $route->bus->driver->last_name }} @endif
                            @else
                                <span class="text-slate-400">Non assignée</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $routeStatusStyles[$route->status] ?? '' }}">
                                {{ \App\Modules\Transport\Domain\Models\Route::STATUSES[$route->status] ?? $route->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <button @click="editOpen = {{ $route->id }}" class="text-[#031C5B] font-bold text-[13px] hover:underline">Modifier</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-slate-500 font-medium">Aucune route enregistrée.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('SchoolDashboard::transport._route_modal', ['route' => null])
    @foreach($routes as $route)
        @include('SchoolDashboard::transport._route_modal', ['route' => $route])
    @endforeach
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var map = L.map('network-map').setView([5.3599517, -4.0082563], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        var stops = @json($mapStops);

        stops.forEach(function (stop) {
            L.marker([stop.lat, stop.lng]).addTo(map).bindPopup('<strong>' + stop.name + '</strong><br>' + stop.route);
        });
    });
</script>
@endpush
