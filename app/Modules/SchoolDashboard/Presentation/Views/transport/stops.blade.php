@extends('SchoolDashboard::layouts.app')

@push('styles')
@endpush

@section('content')
<div class="space-y-6" x-data="{ addOpen: false, assignOpen: null }">
    @include('SchoolDashboard::transport._tabs')

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Arrêts de Bus</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Organisez la séquence d'arrêts par route.</p>
        </div>
        @if($selectedRoute)
        <button @click="addOpen = true; $nextTick(() => window.initAddStopMap && window.initAddStopMap())" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
            <i class="ph-bold ph-plus"></i> Ajouter un point d'arrêt
        </button>
        @endif
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Route selector -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-2 overflow-x-auto">
        @forelse($routes as $route)
            <a href="{{ route('school.transport.stops', ['route' => $route->id]) }}" class="px-4 py-2 rounded-xl text-[13px] font-bold whitespace-nowrap transition {{ $selectedRoute && $selectedRoute->id === $route->id ? 'bg-[#031C5B] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                {{ $route->name }}
                @if($route->bus)
                    <span class="ml-1 text-[10.5px] font-normal opacity-70">— {{ $route->bus->bus_number }} · {{ $route->period ? \App\Modules\Transport\Domain\Models\Route::PERIODS[$route->period] : 'Matin+Soir' }}</span>
                @endif
            </a>
        @empty
            <p class="text-slate-400 text-[13px]">Aucune route disponible — créez-en une dans l'onglet Itinéraires.</p>
        @endforelse
    </div>

    @if($selectedRoute)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Stop sequence -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
                <div class="p-5 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900">Séquence — {{ $selectedRoute->name }}</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($stops as $index => $stop)
                    <div class="p-4 flex items-center gap-3">
                        <div class="flex flex-col gap-0.5">
                            <form method="POST" action="{{ route('school.transport.stops.move', $stop->id) }}">
                                @csrf
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" {{ $index === 0 ? 'disabled' : '' }} class="w-6 h-6 flex items-center justify-center rounded text-slate-400 hover:text-[#031C5B] hover:bg-slate-100 disabled:opacity-30 disabled:hover:bg-transparent"><i class="ph-bold ph-caret-up"></i></button>
                            </form>
                            <form method="POST" action="{{ route('school.transport.stops.move', $stop->id) }}">
                                @csrf
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" {{ $loop->last ? 'disabled' : '' }} class="w-6 h-6 flex items-center justify-center rounded text-slate-400 hover:text-[#031C5B] hover:bg-slate-100 disabled:opacity-30 disabled:hover:bg-transparent"><i class="ph-bold ph-caret-down"></i></button>
                            </form>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-[#031C5B]/10 text-[#031C5B] flex items-center justify-center font-bold text-[12px] shrink-0">{{ $index + 1 }}</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13.5px] font-bold text-slate-800">{{ $stop->name }}</p>
                            <p class="text-[11.5px] text-slate-500">{{ $stop->address ?? 'Adresse non précisée' }} @if($stop->arrival_time) · {{ \Illuminate\Support\Carbon::parse($stop->arrival_time)->format('H:i') }} @endif</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700 whitespace-nowrap">{{ $stop->students_count }} Élève{{ $stop->students_count > 1 ? 's' : '' }}</span>
                        <button @click="assignOpen = {{ $stop->id }}" class="text-slate-400 hover:text-[#031C5B]" title="Assigner des élèves"><i class="ph-bold ph-user-plus"></i></button>
                        <form method="POST" action="{{ route('school.transport.stops.destroy', $stop->id) }}" onsubmit="return confirm('Supprimer cet arrêt ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-600"><i class="ph-bold ph-trash"></i></button>
                        </form>
                    </div>
                    @empty
                    <p class="text-center text-slate-500 font-medium py-10">Aucun arrêt pour cette route.</p>
                    @endforelse
                </div>
            </div>

            <!-- Suggestion (calcul géospatial réel) -->
            <div class="bg-gradient-to-br from-[#F5F3FF] to-purple-50/50 border border-purple-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <i class="ph-fill ph-sparkle text-purple-600 text-lg"></i>
                    <h3 class="font-extrabold text-slate-800 text-[15px]">{{ $nearbyConsecutivePair ? 'Optimisation de Route Détectée' : 'Optimisation de Route' }}</h3>
                </div>
                @if($nearbyConsecutivePair)
                    <p class="text-[13px] text-slate-700 leading-relaxed">Les arrêts <strong>{{ $nearbyConsecutivePair['stop_a'] }}</strong> et <strong>{{ $nearbyConsecutivePair['stop_b'] }}</strong> ne sont qu'à {{ $nearbyConsecutivePair['distance_m'] }}m l'un de l'autre — envisagez de les regrouper.</p>
                @else
                    <p class="text-[13px] text-slate-700 leading-relaxed">Aucun arrêt consécutif proche (moins de 400m) détecté sur cette route.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div id="stops-map" style="height: 280px;"></div>
            </div>
        </div>

        <!-- Capacité du Bus -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 h-fit">
            <h3 class="text-[15px] font-bold text-slate-900 mb-4">Capacité du Bus</h3>
            @php
                $assignedTotal = $stops->sum('students_count');
                $capacity = $selectedRoute->bus->capacity ?? null;
                $occupancy = $capacity ? min(100, round(($assignedTotal / max(1, $capacity)) * 100)) : null;
            @endphp
            @if($capacity)
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[12px] font-semibold text-slate-500">Occupation</span>
                    <span class="text-[12px] font-bold text-slate-700">{{ $occupancy }}%</span>
                </div>
                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden mb-4">
                    <div class="h-full bg-[#031C5B] rounded-full" style="width: {{ $occupancy }}%"></div>
                </div>
            @else
                <p class="text-[12px] text-slate-400 mb-4">Aucun bus assigné à cette route.</p>
            @endif
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-[12.5px] text-slate-500 font-semibold">Élèves Inscrits</span>
                    <span class="text-[13px] font-bold text-slate-800">{{ $assignedTotal }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[12.5px] text-slate-500 font-semibold">Capacité du Bus</span>
                    <span class="text-[13px] font-bold text-slate-800">{{ $capacity ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[12.5px] text-slate-500 font-semibold">Accompagnateurs</span>
                    <span class="text-[13px] font-bold text-slate-800">{{ $selectedRoute->bus?->driver?->has_assistant ? 1 : 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    @include('SchoolDashboard::transport._add_stop_modal')
    @foreach($stops as $stop)
        @include('SchoolDashboard::transport._assign_students_modal', ['stop' => $stop, 'routePeriod' => $selectedRoute->period])
    @endforeach
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var defaultCenter = [{{ $abidjan['lat'] }}, {{ $abidjan['lng'] }}];

        @if($selectedRoute)
        var stopsMap = L.map('stops-map').setView(defaultCenter, 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(stopsMap);

        var stopPoints = @json($mapStops);
        stopPoints.forEach(function (s, i) {
            L.marker([s.lat, s.lng]).addTo(stopsMap).bindPopup('<strong>' + (i + 1) + '. ' + s.name + '</strong>');
        });

        function reverseGeocodeAddStop(lat, lng) {
            var addressInput = document.getElementById('stop_address');
            if (!addressInput) { return; }
            fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data && data.display_name) {
                        addressInput.value = data.display_name;
                        addressInput.dispatchEvent(new Event('input'));
                    }
                })
                .catch(function () {});
        }

        var addMapInitialized = false;
        window.initAddStopMap = function () {
            if (addMapInitialized) { return; }
            addMapInitialized = true;
            var addMap = L.map('add-stop-map').setView(defaultCenter, 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(addMap);
            var marker = null;

            function placeMarker(lat, lng, panTo) {
                document.getElementById('stop_latitude').value = lat.toFixed(7);
                document.getElementById('stop_longitude').value = lng.toFixed(7);
                if (marker) { addMap.removeLayer(marker); }
                marker = L.marker([lat, lng], { draggable: true }).addTo(addMap);
                marker.on('dragend', function () {
                    var pos = marker.getLatLng();
                    document.getElementById('stop_latitude').value = pos.lat.toFixed(7);
                    document.getElementById('stop_longitude').value = pos.lng.toFixed(7);
                    reverseGeocodeAddStop(pos.lat, pos.lng);
                });
                if (panTo) { addMap.setView([lat, lng], 15); }
            }

            addMap.on('click', function (e) {
                placeMarker(e.latlng.lat, e.latlng.lng, false);
                reverseGeocodeAddStop(e.latlng.lat, e.latlng.lng);
            });

            window.setAddStopLocation = function (lat, lng) {
                placeMarker(lat, lng, true);
            };

            setTimeout(function () { addMap.invalidateSize(); }, 200);
        };
        @endif
    });
</script>
@endpush
