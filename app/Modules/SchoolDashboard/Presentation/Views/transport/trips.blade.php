@extends('SchoolDashboard::layouts.app')

@push('styles')
@endpush

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
                        <p class="text-[12px] text-slate-500 mt-0.5">{{ $trip->route->name ?? 'Route non assignée' }} · {{ $trip->trip_date->translatedFormat('d M Y') }}</p>
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
                @if($trip->bus_id)
                <div class="flex items-center gap-4 mt-3">
                    <button
                        type="button"
                        class="trip-track-toggle inline-flex items-center gap-1.5 text-[12.5px] font-bold text-[#031C5B] hover:underline"
                        data-bus-id="{{ $trip->bus_id }}"
                        data-date="{{ $trip->trip_date->toDateString() }}"
                        data-target="trip-map-{{ $trip->id }}"
                        data-stops="{{ $trip->route ? $trip->route->stops->map(fn ($s) => ['lat' => $s->latitude, 'lng' => $s->longitude, 'name' => $s->name])->filter(fn ($s) => $s['lat'] !== null && $s['lng'] !== null)->values() : '[]' }}"
                    >
                        <i class="ph-bold ph-clock-counter-clockwise"></i> Revoir le trajet
                    </button>
                    <button
                        type="button"
                        class="trip-track-play inline-flex items-center gap-1.5 text-[12.5px] font-bold text-[#031C5B]"
                        data-target="trip-map-{{ $trip->id }}"
                        style="display: none"
                    >
                        <i class="ph-fill ph-play"></i> Lecture
                    </button>
                </div>
                <div id="trip-map-{{ $trip->id }}" class="trip-track-map mt-3 rounded-xl overflow-hidden border border-slate-100" style="height: 180px; display: none;"></div>
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
            <p class="text-[13px] text-slate-700 leading-relaxed" id="transportAiAnalysisText">Analyse des tournées des 8 dernières semaines en cours...</p>
        </div>
    </div>

    @include('SchoolDashboard::transport._log_trip_modal')
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tracks = {}; // targetId -> { map, points, marker, timer, index, busIcon }
        var playBtns = {};
        document.querySelectorAll('.trip-track-play').forEach(function (btn) {
            playBtns[btn.dataset.target] = btn;
        });

        document.querySelectorAll('.trip-track-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var targetId = btn.dataset.target;
                var container = document.getElementById(targetId);
                var isHidden = container.style.display === 'none';
                container.style.display = isHidden ? 'block' : 'none';
                btn.innerHTML = isHidden
                    ? '<i class="ph-bold ph-map-trifold"></i> Masquer le tracé'
                    : '<i class="ph-bold ph-clock-counter-clockwise"></i> Revoir le trajet';

                if (!isHidden) {
                    stopPlayback(targetId);
                    if (playBtns[targetId]) playBtns[targetId].style.display = 'none';
                } else if (tracks[targetId] && tracks[targetId].points && playBtns[targetId]) {
                    // Already loaded from a previous open — just re-show the play control.
                    playBtns[targetId].style.display = 'inline-flex';
                } else if (!tracks[targetId]) {
                    tracks[targetId] = {};
                    var stops = [];
                    try { stops = JSON.parse(btn.dataset.stops || '[]'); } catch (e) { stops = []; }
                    loadTripTrack(targetId, container, btn.dataset.busId, btn.dataset.date, stops);
                }
            });
        });

        Object.keys(playBtns).forEach(function (targetId) {
            playBtns[targetId].addEventListener('click', function () {
                togglePlayback(targetId);
            });
        });

        function stopPlayback(targetId) {
            var t = tracks[targetId];
            if (t && t.timer) { clearInterval(t.timer); t.timer = null; }
            var btn = playBtns[targetId];
            if (btn) btn.innerHTML = '<i class="ph-fill ph-play"></i> Lecture';
        }

        function togglePlayback(targetId) {
            var t = tracks[targetId];
            if (!t || !t.points || !t.points.length) return;
            var btn = playBtns[targetId];

            if (t.timer) { stopPlayback(targetId); return; }

            t.index = 0;
            if (!t.marker) t.marker = L.marker(t.points[0], { icon: t.busIcon }).addTo(t.map);
            btn.innerHTML = '<i class="ph-fill ph-pause"></i> Pause';
            t.timer = setInterval(function () {
                if (t.index >= t.points.length) { stopPlayback(targetId); return; }
                t.marker.setLatLng(t.points[t.index]);
                t.index++;
            }, 400);
        }

        function loadTripTrack(targetId, container, busId, date, stops) {
            var map = L.map(container, { zoomControl: false, attributionControl: false }).setView([5.36, -4.0], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
            var busIcon = L.divIcon({
                html: '<i class="ph-fill ph-bus" style="font-size:20px;color:#031C5B;filter:drop-shadow(0 1px 2px rgba(0,0,0,.4))"></i>',
                className: '',
                iconSize: [20, 20],
                iconAnchor: [10, 10],
            });
            var stopIcon = L.divIcon({
                html: '<span style="display:block;width:10px;height:10px;border-radius:50%;background:#16A34A;border:2px solid #fff;box-shadow:0 1px 2px rgba(0,0,0,.4)"></span>',
                className: '',
                iconSize: [10, 10],
                iconAnchor: [5, 5],
            });

            var bounds = [];
            (stops || []).forEach(function (stop) {
                L.marker([stop.lat, stop.lng], { icon: stopIcon }).addTo(map).bindPopup(stop.name || '');
                bounds.push([stop.lat, stop.lng]);
            });

            fetch('{{ url("school/transport/buses") }}/' + busId + '/positions?date=' + date, {
                headers: { 'Accept': 'application/json' },
            })
                .then(function (res) { return res.json(); })
                .then(function (body) {
                    var points = (body.points || []).map(function (p) { return [p.lat, p.lng]; });
                    if (points.length > 0) {
                        var line = L.polyline(points, { color: '#031C5B', weight: 3, opacity: 0.7 }).addTo(map);
                        bounds = bounds.concat(points);
                    }
                    if (bounds.length) {
                        map.fitBounds(bounds, { padding: [16, 16] });
                    } else {
                        container.innerHTML = '<p class="text-[12px] text-slate-400 text-center py-16">Aucune position ni arrêt à afficher pour ce trajet.</p>';
                        return;
                    }
                    tracks[targetId] = { map: map, points: points, busIcon: busIcon, marker: null, timer: null, index: 0 };
                    if (points.length > 0 && playBtns[targetId]) playBtns[targetId].style.display = 'inline-flex';
                })
                .catch(function () {
                    container.innerHTML = '<p class="text-[12px] text-red-500 text-center py-16">Échec du chargement du tracé.</p>';
                });
        }
    });
</script>
@endpush

<script>
    document.addEventListener('DOMContentLoaded', function () {
        fetch('{{ route("school.transport.trips.ai-analysis") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            const el = document.getElementById('transportAiAnalysisText');
            el.innerText = data.success ? data.analysis : (data.error || "Analyse IA indisponible pour le moment.");
        })
        .catch(() => {
            document.getElementById('transportAiAnalysisText').innerText = "Erreur de communication avec le serveur.";
        });
    });
</script>
@endsection
