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
                    <option value="">Tous les bus</option>
                    @foreach($buses as $bus)
                        <option value="{{ $bus->id }}">{{ $bus->bus_number }}</option>
                    @endforeach
                </select>
                <span id="live-badge" style="display:none" class="ml-auto text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-full px-2.5 py-1 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> En direct
                </span>
            </div>
            <div id="tracking-map" style="height: 460px;"></div>
            <p class="text-[11.5px] text-slate-400 px-5 py-3 border-t border-slate-100 flex items-center gap-1.5">
                <i class="ph ph-info"></i> Position en direct transmise par l'app chauffeur quand un trajet est en cours — aucune saisie manuelle.
            </p>
            <div id="replay-bar" style="display:none" class="p-4 border-t border-slate-100 flex flex-wrap items-center gap-3 bg-slate-50">
                <span class="text-[12.5px] font-bold text-slate-600"><i class="ph-bold ph-clock-counter-clockwise"></i> Revoir ce trajet :</span>
                <button id="replay-play-btn" type="button" class="px-3 py-2 bg-[#031C5B] text-white rounded-lg text-[12.5px] font-bold">
                    <i class="ph-fill ph-play"></i> Lecture
                </button>
                <span id="replay-hint" class="text-[12px] text-slate-400"></span>
                <a href="{{ route('school.transport.map') }}" class="ml-auto text-[12px] font-bold text-slate-500 hover:text-slate-700">Quitter le replay</a>
            </div>
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
                    <div
                        x-show="filter === 'all' || '{{ $tone }}' === 'alert'"
                        class="fleet-bus-card cursor-pointer border rounded-xl p-3.5 transition hover:ring-2 hover:ring-[#031C5B]/30 {{ $toneStyles[$tone] }}"
                        data-bus-id="{{ $bus->id }}"
                    >
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
    /**
     * Minimal Laravel Reverb (Pusher-protocol) WebSocket client — same
     * hand-rolled approach as the Flutter apps (no pusher-js/laravel-echo
     * install: this dashboard has no bundler step today, loading Leaflet
     * straight from a CDN, so a raw client keeps that same pattern). One
     * physical socket, multiplexed across every bus's channel at once (the
     * whole fleet is tracked live simultaneously — a bus selection in the
     * UI only changes what's *shown*, not what's subscribed), same as a
     * real Pusher/Reverb client would: `subscribe(channel, onEvent)` for
     * each channel, one `pusher:subscribe` frame per channel over the same
     * connection.
     */
    class ReverbClient {
        constructor({ host, port, key }) {
            this.host = host;
            this.port = port;
            this.key = key;
            this.socket = null;
            this.socketId = null;
            this.channels = {}; // wireChannel -> onEvent
            this._connect();
        }

        _connect() {
            const scheme = location.protocol === 'https:' ? 'wss' : 'ws';
            this.socket = new WebSocket(scheme + '://' + this.host + ':' + this.port + '/app/' + this.key + '?protocol=7&client=js&version=1.0&flash=false');
            this.socket.onmessage = (raw) => this._handleMessage(raw.data);
            this.socket.onclose = () => { this.socketId = null; setTimeout(() => this._connect(), 5000); };
            this.socket.onerror = () => {};
        }

        async _handleMessage(raw) {
            let message;
            try { message = JSON.parse(raw); } catch (e) { return; }

            if (message.event === 'pusher:connection_established') {
                const data = typeof message.data === 'string' ? JSON.parse(message.data) : message.data;
                this.socketId = data.socket_id;
                Object.keys(this.channels).forEach((wireChannel) => this._authorizeAndSubscribe(wireChannel));
                return;
            }

            if (message.event === 'bus.position.updated' || message.event === 'client-position-updated') {
                const onEvent = this.channels[message.channel];
                if (!onEvent) return;
                const data = typeof message.data === 'string' ? JSON.parse(message.data) : message.data;
                onEvent(data);
            }
        }

        /** Subscribes (or, after a reconnect, re-subscribes) to [channelName], invoking onEvent for either a server broadcast or a driver's direct client event on it. */
        subscribe(channelName, onEvent) {
            const wireChannel = 'private-' + channelName;
            this.channels[wireChannel] = onEvent;
            if (this.socketId) this._authorizeAndSubscribe(wireChannel);
        }

        async _authorizeAndSubscribe(wireChannel) {
            if (!this.socketId || !this.socket) return;
            try {
                const res = await fetch('{{ url("broadcasting/auth") }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ socket_id: this.socketId, channel_name: wireChannel }),
                });
                if (!res.ok) return;
                const auth = await res.json();
                this.socket.send(JSON.stringify({ event: 'pusher:subscribe', data: { auth: auth.auth, channel: wireChannel } }));
            } catch (e) { /* channel stays unsubscribed — that one bus just won't move live until the next reconnect */ }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var map = L.map('tracking-map').setView([{{ $abidjan['lat'] }}, {{ $abidjan['lng'] }}], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        var allStops = @json($mapStops);
        var allBuses = @json($buses->map(fn ($b) => ['id' => $b->id, 'busNumber' => $b->bus_number]));

        // Every bus is tracked live all the time (see the ReverbClient
        // subscriptions below) — this holds each one's latest known
        // position, seeded from the last one saved in the database and
        // updated in place as live events arrive, whether or not that bus
        // is currently shown. Bus selection only changes what's rendered.
        var liveBusPositions = {};
        @foreach($busPositions as $pos)
            liveBusPositions[{{ $pos['id'] }}] = { lat: {{ $pos['lat'] }}, lng: {{ $pos['lng'] }}, busNumber: @js($pos['busNumber']) };
        @endforeach

        var stopsLayer = L.layerGroup().addTo(map);
        var busesLayer = L.layerGroup().addTo(map);
        var busMarkersById = {};

        // No bus selected → every stop and every bus's known position.
        // A bus selected → only its own stops and its own marker.
        function renderStops(filterBusId) {
            stopsLayer.clearLayers();
            allStops
                .filter(function (stop) { return !filterBusId || String(stop.busId) === String(filterBusId); })
                .forEach(function (stop) {
                    L.marker([stop.lat, stop.lng]).addTo(stopsLayer).bindPopup('<strong>' + stop.name + '</strong><br>' + stop.route);
                });
        }

        function renderBusPositions(filterBusId) {
            busesLayer.clearLayers();
            busMarkersById = {};
            Object.keys(liveBusPositions)
                .filter(function (id) { return !filterBusId || String(id) === String(filterBusId); })
                .forEach(function (id) {
                    var pos = liveBusPositions[id];
                    busMarkersById[id] = L.marker([pos.lat, pos.lng], { icon: busIcon }).addTo(busesLayer).bindPopup(pos.busNumber || '');
                });
        }

        // --- Bus position display — the driver app's websocket push is the
        // only source now, nothing here ever writes a position back.
        var busSelect = document.getElementById('position-bus-select');
        var busIcon = L.divIcon({
            html: '<i class="ph-fill ph-bus" style="font-size:26px;color:#031C5B;filter:drop-shadow(0 1px 2px rgba(0,0,0,.4))"></i>',
            className: '',
            iconSize: [26, 26],
            iconAnchor: [13, 13],
        });

        renderStops(null);
        renderBusPositions(null);

        // --- Live tracking (websocket) — the whole fleet, all the time ---
        var liveBadge = document.getElementById('live-badge');
        var reverb = new ReverbClient({
            host: location.hostname,
            port: {{ (int) ($reverb['port'] ?? 8080) }},
            key: '{{ $reverb['key'] }}',
        });

        allBuses.forEach(function (bus) {
            reverb.subscribe('transport.bus.' + bus.id, function (data) {
                if (typeof data.latitude !== 'number' || typeof data.longitude !== 'number') return;
                liveBusPositions[bus.id] = { lat: data.latitude, lng: data.longitude, busNumber: bus.busNumber };

                var selected = busSelect.value;
                if (selected && String(selected) !== String(bus.id)) return; // shown filtered out — position is still kept up to date above
                var latlng = [data.latitude, data.longitude];
                if (busMarkersById[bus.id]) {
                    busMarkersById[bus.id].setLatLng(latlng);
                } else {
                    busMarkersById[bus.id] = L.marker(latlng, { icon: busIcon }).addTo(busesLayer).bindPopup(bus.busNumber);
                }
                liveBadge.style.display = 'inline-flex';
            });
        });

        function applyBusSelection(selected) {
            busSelect.value = selected || '';
            renderStops(selected);
            renderBusPositions(selected);
            if (selected) {
                var selectedStops = allStops.filter(function (stop) { return String(stop.busId) === String(selected); });
                var bounds = selectedStops.map(function (s) { return [s.lat, s.lng]; });
                var pos = liveBusPositions[selected];
                if (pos) bounds.push([pos.lat, pos.lng]);
                if (bounds.length) map.fitBounds(bounds, { padding: [30, 30] });
            } else if (allStops.length) {
                map.fitBounds(allStops.map(function (s) { return [s.lat, s.lng]; }), { padding: [30, 30] });
            }
        }

        busSelect.addEventListener('change', function () {
            applyBusSelection(busSelect.value || null);
        });

        // Clicking a bus in "Flotte Active" is the same as picking it above.
        document.querySelectorAll('.fleet-bus-card').forEach(function (card) {
            card.addEventListener('click', function () {
                applyBusSelection(card.dataset.busId);
                document.getElementById('tracking-map').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        });

        // --- Replay — reached via "Revoir le trajet" on the Journal des
        // Trajets page (school/transport/trips), which links here with
        // ?bus=X&date=Y. Nothing to pick on this page itself; landing with
        // both params just loads and shows that trip's path.
        var replayBar = document.getElementById('replay-bar');
        var replayPlayBtn = document.getElementById('replay-play-btn');
        var replayHint = document.getElementById('replay-hint');

        var replayLine = null;
        var replayMarker = null;
        var replayPoints = [];
        var replayTimer = null;
        var replayIndex = 0;

        function loadReplay(busId, date) {
            replayBar.style.display = 'flex';
            replayHint.textContent = 'Chargement...';

            fetch('{{ url("school/transport/buses") }}/' + busId + '/positions?date=' + date, {
                headers: { 'Accept': 'application/json' },
            })
                .then(function (res) { return res.json(); })
                .then(function (body) {
                    replayPoints = (body.points || []).map(function (p) { return [p.lat, p.lng]; });
                    if (replayPoints.length === 0) {
                        replayHint.textContent = 'Aucune position enregistrée ce jour-là.';
                        return;
                    }
                    replayLine = L.polyline(replayPoints, { color: '#031C5B', weight: 3, opacity: 0.6 }).addTo(map);
                    map.fitBounds(replayLine.getBounds(), { padding: [30, 30] });
                    replayHint.textContent = replayPoints.length + ' position(s) enregistrée(s).';
                })
                .catch(function () { replayHint.textContent = "Échec du chargement de l'historique."; });
        }

        replayPlayBtn.addEventListener('click', function () {
            if (replayTimer) { clearInterval(replayTimer); replayTimer = null; replayPlayBtn.innerHTML = '<i class="ph-fill ph-play"></i> Lecture'; return; }
            if (replayPoints.length === 0) return;
            replayIndex = 0;
            if (!replayMarker) replayMarker = L.marker(replayPoints[0], { icon: busIcon }).addTo(map);
            replayPlayBtn.innerHTML = '<i class="ph-fill ph-pause"></i> Pause';
            replayTimer = setInterval(function () {
                if (replayIndex >= replayPoints.length) { clearInterval(replayTimer); replayTimer = null; replayPlayBtn.innerHTML = '<i class="ph-fill ph-play"></i> Lecture'; return; }
                replayMarker.setLatLng(replayPoints[replayIndex]);
                replayIndex++;
            }, 400);
        });

        var replayParams = new URLSearchParams(location.search);
        if (replayParams.get('bus') && replayParams.get('date')) {
            applyBusSelection(replayParams.get('bus'));
            loadReplay(replayParams.get('bus'), replayParams.get('date'));
        }
    });
</script>
@endpush
