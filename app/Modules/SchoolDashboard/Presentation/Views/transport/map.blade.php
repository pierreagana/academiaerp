@extends('SchoolDashboard::layouts.app')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div class="space-y-6" x-data="{ filter: 'all' }">
    @include('SchoolDashboard::transport._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Carte & Suivi</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Localisation des arrêts et statut du jour par bus.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-wrap items-center gap-3">
                <label class="text-[12.5px] font-bold text-slate-600">Position en direct :</label>
                <select id="position-bus-select" class="text-[13px] border border-slate-200 rounded-lg px-3 py-2 bg-slate-50">
                    <option value="">Choisir un bus...</option>
                    @foreach($buses as $bus)
                        <option value="{{ $bus->id }}">{{ $bus->bus_number }}</option>
                    @endforeach
                </select>
                <span id="position-hint" class="text-[12px] text-slate-400"></span>
                <button
                    id="save-position-btn"
                    type="button"
                    style="display:none"
                    class="ml-auto px-4 py-2 bg-[#031C5B] text-white rounded-lg text-[12.5px] font-bold disabled:opacity-50"
                >
                    Enregistrer la position
                </button>
            </div>
            <div id="tracking-map" style="height: 460px;"></div>
            <p class="text-[11.5px] text-slate-400 px-5 py-3 border-t border-slate-100 flex items-center gap-1.5">
                <i class="ph ph-info"></i> Carte des arrêts assignés aux routes actives, centrée sur Abidjan. Aucun dispositif GPS n'est connecté : sélectionnez un bus puis cliquez sur la carte pour signaler sa position réelle — elle sera transmise en direct aux parents des élèves de ce bus.
            </p>
        </div>

        <!-- Flotte Active -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[15px] font-bold text-slate-900">Flotte Active</h3>
                <div class="flex items-center gap-1 bg-slate-100 rounded-lg p-1">
                    <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-white shadow-sm text-[#031C5B]' : 'text-slate-500'" class="px-3 py-1 rounded-md text-[12px] font-bold transition">Tous</button>
                    <button @click="filter = 'alert'" :class="filter === 'alert' ? 'bg-white shadow-sm text-[#031C5B]' : 'text-slate-500'" class="px-3 py-1 rounded-md text-[12px] font-bold transition">Alertes</button>
                </div>
            </div>
            <div class="space-y-2.5 max-h-[420px] overflow-y-auto">
                @php
                    $toneStyles = ['ok' => 'bg-emerald-50 border-emerald-100 text-emerald-700', 'alert' => 'bg-red-50 border-red-100 text-red-700', 'neutral' => 'bg-slate-50 border-slate-100 text-slate-500'];
                @endphp
                @forelse($buses as $bus)
                    @php $tone = $bus->daily_status['tone']; @endphp
                    <div x-show="filter === 'all' || '{{ $tone }}' === 'alert'" class="border rounded-xl p-3.5 {{ $toneStyles[$tone] }}">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-[13.5px] font-bold text-slate-800">{{ $bus->bus_number }}</p>
                            <span class="text-[10.5px] font-bold uppercase tracking-wider">{{ $bus->daily_status['label'] }}</span>
                        </div>
                        <p class="text-[12px] text-slate-500">{{ $bus->routes->first()->name ?? 'Aucune route assignée' }}</p>
                        @if($tone === 'alert' && !empty($bus->daily_status['detail']))
                            <p class="text-[11.5px] mt-1.5">{{ $bus->daily_status['detail'] }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-slate-400 text-[13px] text-center py-10">Aucun bus enregistré.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var map = L.map('tracking-map').setView([{{ $abidjan['lat'] }}, {{ $abidjan['lng'] }}], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        var stops = @json($mapStops);

        stops.forEach(function (stop) {
            L.marker([stop.lat, stop.lng]).addTo(map).bindPopup('<strong>' + stop.name + '</strong><br>' + stop.route);
        });

        // --- Live bus position reporting ---
        var busSelect = document.getElementById('position-bus-select');
        var hint = document.getElementById('position-hint');
        var saveBtn = document.getElementById('save-position-btn');
        var busIcon = L.divIcon({
            html: '<i class="ph-fill ph-bus" style="font-size:26px;color:#031C5B;filter:drop-shadow(0 1px 2px rgba(0,0,0,.4))"></i>',
            className: '',
            iconSize: [26, 26],
            iconAnchor: [13, 13],
        });
        var pendingMarker = null;
        var pendingLatLng = null;

        function updateHint() {
            if (!busSelect.value) {
                hint.textContent = '';
            } else if (pendingLatLng) {
                hint.textContent = 'Position sélectionnée — confirmez ci-dessous';
                hint.className = 'text-[12px] font-semibold text-amber-600';
            } else {
                hint.textContent = 'Cliquez sur la carte pour placer le bus';
                hint.className = 'text-[12px] text-slate-400';
            }
        }

        busSelect.addEventListener('change', function () {
            if (pendingMarker) { map.removeLayer(pendingMarker); pendingMarker = null; }
            pendingLatLng = null;
            saveBtn.style.display = 'none';
            updateHint();
        });

        map.on('click', function (e) {
            if (!busSelect.value) return;
            pendingLatLng = e.latlng;
            if (pendingMarker) {
                pendingMarker.setLatLng(e.latlng);
            } else {
                pendingMarker = L.marker(e.latlng, { icon: busIcon, draggable: true }).addTo(map);
                pendingMarker.on('dragend', function () { pendingLatLng = pendingMarker.getLatLng(); });
            }
            saveBtn.style.display = 'inline-block';
            updateHint();
        });

        saveBtn.addEventListener('click', function () {
            if (!busSelect.value || !pendingLatLng) return;
            saveBtn.disabled = true;
            saveBtn.textContent = 'Enregistrement...';

            fetch('{{ url("school/transport/buses") }}/' + busSelect.value + '/position', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ latitude: pendingLatLng.lat, longitude: pendingLatLng.lng }),
            })
                .then(function (res) {
                    if (!res.ok) throw new Error('Échec de l\'enregistrement');
                    saveBtn.textContent = 'Position enregistrée ✓';
                    setTimeout(function () {
                        saveBtn.disabled = false;
                        saveBtn.textContent = 'Enregistrer la position';
                        saveBtn.style.display = 'none';
                        pendingLatLng = null;
                        updateHint();
                    }, 1500);
                })
                .catch(function () {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Enregistrer la position';
                    alert("Échec de l'enregistrement de la position.");
                });
        });
    });
</script>
@endpush
