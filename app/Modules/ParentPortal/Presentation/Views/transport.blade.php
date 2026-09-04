@extends('ParentPortal::layout')

@section('title', 'Transport Scolaire - ' . $child->first_name)

@section('content')

<!-- HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Transport & Bus &bull; {{ $child->first_name }}</h1>
        <p class="text-sm font-medium text-slate-500 mt-0.5">Arrêts de ramassage, horaires et véhicule assigné.</p>
    </div>
</div>

@php
    $otherPeriodLabel = ['morning' => 'Soir', 'evening' => 'Matin'];
    $periods = [
        'morning' => [
            'label' => 'Matin', 'desc' => 'Maison → École', 'stop' => $morningStop, 'status' => $morningStatus,
            'lockedZone' => $lockedZoneByPeriod['evening'] ?? null,
        ],
        'evening' => [
            'label' => 'Soir', 'desc' => 'École → Maison', 'stop' => $eveningStop, 'status' => $eveningStatus,
            'lockedZone' => $lockedZoneByPeriod['morning'] ?? null,
        ],
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
@foreach($periods as $period => $p)
<div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] p-6 flex flex-col justify-between">
    <div>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                    <span class="material-symbols-outlined text-[19px]">directions_bus</span>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Trajet du {{ $p['label'] }}</h3>
                    <p class="text-[11px] font-medium text-slate-400">{{ $p['desc'] }}</p>
                </div>
            </div>
            @if($p['stop'])
                <span class="text-[10.5px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60">Inscrit</span>
            @endif
        </div>

        @if($p['stop'])
            <div class="space-y-3 bg-slate-50/70 p-4 rounded-2xl border border-slate-100/80">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500">Arrêt assigné</span>
                    <span class="text-xs font-black text-slate-900">{{ $p['stop']->name }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500">Heure de passage</span>
                    <span class="text-xs font-black text-[#061536]">{{ $p['stop']->arrival_time }}</span>
                </div>
            </div>
        @else
            <div x-data="{ editing: {{ $p['status']['status'] === 'pending' ? 'false' : 'true' }} }">
                @if($p['status']['status'] === 'pending')
                    <div class="p-3 mb-3 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-800 text-xs font-bold flex items-center justify-between gap-2">
                        <span class="flex items-center gap-2 min-w-0">
                            <span class="material-symbols-outlined text-[17px] shrink-0">hourglass_empty</span>
                            <span>Demande envoyée, en attente de validation par l'école.</span>
                        </span>
                        <button type="button" x-show="!editing" x-on:click="editing = true"
                                class="shrink-0 text-[11px] font-extrabold text-amber-900 bg-amber-100 hover:bg-amber-200 px-2.5 py-1 rounded-lg transition">
                            Modifier
                        </button>
                    </div>
                @elseif($p['status']['status'] === 'withdrawn')
                    <p class="text-slate-500 text-xs mb-3">Cet élève a été retiré de ce service. Vous pouvez faire une nouvelle demande.</p>
                @elseif($p['status']['status'] === 'rejected' && $p['status']['rejectionReason'])
                    <div class="p-3 mb-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                        <strong>Refus :</strong> {{ $p['status']['rejectionReason'] }}
                    </div>
                @else
                    <p class="text-slate-500 text-xs mb-3">Cet élève n'est pas inscrit pour ce trajet.</p>
                @endif

                <template x-if="editing">
                    <div>
                        @if($zonesByPeriod[$period]->isEmpty())
                            <p class="text-slate-400 text-xs italic">Aucun arrêt disponible pour ce trajet pour le moment.</p>
                        @else
                            <div x-data="stopPicker('{{ $period }}', {{ \Illuminate\Support\Js::from($zonesByPeriod[$period]) }}, {{ \Illuminate\Support\Js::from($p['status']['pendingStopId']) }})" class="space-y-3">
                                <div class="relative">
                                    <label class="text-[11.5px] font-bold text-slate-700 mb-1 block">Votre adresse</label>
                                    <input type="text" x-model="query" x-on:input.debounce.400ms="searchAddress()"
                                           placeholder="Tapez votre adresse pour voir les zones les plus proches..."
                                           autocomplete="off"
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 outline-none focus:border-blue-500">
                                    <div x-show="suggestions.length > 0" x-cloak x-on:click.outside="suggestions = []"
                                         class="absolute z-[1100] mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                        <template x-for="s in suggestions" :key="s.lat + ',' + s.lng">
                                            <button type="button" x-on:click="pickSuggestion(s)"
                                                    class="w-full text-left px-3 py-2 text-[11.5px] font-medium text-slate-700 hover:bg-blue-50 transition border-b border-slate-50 last:border-0"
                                                    x-text="s.label"></button>
                                        </template>
                                    </div>
                                </div>

                                <div x-ref="map" class="w-full h-40 rounded-2xl border border-slate-200 overflow-hidden"></div>

                                <form method="POST" action="{{ route('parent.transport.request', $child->id) }}" class="space-y-2">
                                    @csrf
                                    <input type="hidden" name="period" value="{{ $period }}">
                                    <input type="hidden" name="route_stop_id" x-model="selectedStopId">

                                    @if($p['lockedZone'])
                                        <p class="text-[10.5px] text-blue-700 bg-blue-50 border border-blue-100 rounded-lg px-2.5 py-1.5 flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[14px] shrink-0">lock</span>
                                            <span>Zone imposée par le trajet du {{ $otherPeriodLabel[$period] }} : <strong>{{ $p['lockedZone'] }}</strong></span>
                                        </p>
                                    @endif
                                    <label class="text-[11.5px] font-bold text-slate-700 block">1. Choisir une zone</label>
                                    <div class="max-h-32 overflow-y-auto space-y-1.5 pr-1">
                                        <template x-for="z in sortedZones" :key="z.zone">
                                            <button type="button" x-on:click="selectZone(z.zone)"
                                                    :class="selectedZone === z.zone ? 'border-[#061536] bg-blue-50/60' : 'border-slate-200 bg-slate-50 hover:border-slate-300'"
                                                    class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-xl border text-left transition">
                                                <span class="flex items-center gap-2 min-w-0">
                                                    <span class="material-symbols-outlined text-[16px] text-slate-400 shrink-0">map</span>
                                                    <span class="text-[11.5px] font-bold text-slate-800 truncate" x-text="z.zone"></span>
                                                </span>
                                            </button>
                                        </template>
                                    </div>

                                    <template x-if="selectedZone">
                                        <div class="space-y-1.5">
                                            <label class="text-[11.5px] font-bold text-slate-700 block">2. Choisir un arrêt — <span x-text="selectedZone"></span></label>
                                            <div class="max-h-40 overflow-y-auto space-y-1.5 pr-1">
                                                <template x-for="s in stopsInSelectedZone" :key="s.id">
                                                    <button type="button" x-on:click="selectStop(s.id, selectedZone)"
                                                            :class="selectedStopId === s.id ? 'border-[#061536] bg-blue-50/60' : 'border-slate-200 bg-slate-50 hover:border-slate-300'"
                                                            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-xl border text-left transition">
                                                        <span class="flex items-center gap-2 min-w-0">
                                                            <span class="material-symbols-outlined text-[16px] text-slate-400 shrink-0">location_on</span>
                                                            <span class="text-[11.5px] font-bold text-slate-800 truncate" x-text="s.name"></span>
                                                            <template x-if="s.arrival_time">
                                                                <span class="text-[10px] text-slate-400 shrink-0" x-text="'(' + s.arrival_time + ')'"></span>
                                                            </template>
                                                        </span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <button type="submit" :disabled="!selectedStopId"
                                            :class="selectedStopId ? 'bg-[#061536] hover:bg-[#061536]/90' : 'bg-slate-300 cursor-not-allowed'"
                                            class="w-full text-white font-bold text-xs py-2.5 rounded-xl transition"
                                            x-text="{{ \Illuminate\Support\Js::from($p['status']['pendingStopId']) }} ? 'Modifier ma demande' : 'Demander l\'inscription'">
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </template>
            </div>
        @endif
    </div>
</div>
@endforeach
</div>

@if($route || $bus)
<div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] p-6">
    <h3 class="text-sm font-extrabold text-slate-900 mb-4">Informations Véhicule & Chauffeur</h3>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div class="bg-slate-50 p-4 rounded-2xl">
            <span class="text-[11px] font-bold text-slate-400 block mb-0.5">Ligne / Itinéraire</span>
            <span class="font-extrabold text-slate-900">{{ $route->name ?? 'Ligne Principale' }}</span>
        </div>
        <div class="bg-slate-50 p-4 rounded-2xl">
            <span class="text-[11px] font-bold text-slate-400 block mb-0.5">Bus Assigné</span>
            <span class="font-extrabold text-slate-900">{{ $bus->bus_number ?? 'Bus #1' }} @if($bus?->plate_number) ({{ $bus->plate_number }}) @endif</span>
        </div>
        @if($bus?->driver)
        <div class="bg-slate-50 p-4 rounded-2xl">
            <span class="text-[11px] font-bold text-slate-400 block mb-0.5">Chauffeur</span>
            <span class="font-extrabold text-slate-900">{{ $bus->driver->first_name }} {{ $bus->driver->last_name }}</span>
        </div>
        @endif
    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('stopPicker', (period, zones, initialStopId) => ({
        period,
        zones, // [{zone, stops:[{id,name,arrival_time,lat,lng}]}]
        query: '',
        suggestions: [],
        addressLat: null,
        addressLng: null,
        selectedZone: null,
        selectedStopId: null,
        map: null,
        stopMarkers: {}, // stopId -> {marker, zone}
        addressMarker: null,

        init() {
            this.$nextTick(() => {
                this.initMap();
                // A still-pending request already has a zone/stop — preselect it so
                // "modifier ma demande" starts from what was actually asked for,
                // instead of an empty picker the parent has to redo from scratch.
                const preselected = initialStopId
                    ? this.zones.flatMap(z => z.stops.map(s => ({ ...s, zone: z.zone }))).find(s => s.id === initialStopId)
                    : null;
                if (preselected) {
                    this.selectStop(preselected.id, preselected.zone);
                } else if (this.zones.length === 1) {
                    // Only one zone on offer usually means it's locked by the other
                    // period's already-chosen zone — nothing left to actually choose.
                    this.selectZone(this.zones[0].zone);
                }
            });
        },

        get allStops() {
            return this.zones.flatMap(z => z.stops);
        },

        initMap() {
            const el = this.$refs.map;
            const first = this.allStops[0];
            if (!el || typeof L === 'undefined' || !first) return;

            this.map = L.map(el, { zoomControl: false }).setView([first.lat, first.lng], 12);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(this.map);
            L.control.zoom({ position: 'bottomright' }).addTo(this.map);

            const bounds = [];
            this.zones.forEach(z => {
                z.stops.forEach(s => {
                    const marker = L.marker([s.lat, s.lng], { icon: this.stopIcon(s.id, z.zone) })
                        .addTo(this.map)
                        .bindPopup(s.name)
                        .on('click', () => this.selectStop(s.id, z.zone));
                    this.stopMarkers[s.id] = { marker, zone: z.zone };
                    bounds.push([s.lat, s.lng]);
                });
            });
            if (bounds.length > 1) this.map.fitBounds(bounds, { padding: [24, 24] });
        },

        /** Selected stop = green, any other stop in the selected zone = navy, everything else = muted grey. */
        stopIcon(stopId, zone) {
            let color = 'bg-slate-400 ring-slate-200/60';
            if (this.selectedStopId === stopId) color = 'bg-emerald-600 ring-emerald-300/50';
            else if (this.selectedZone === zone) color = 'bg-[#061536] ring-blue-300/40';

            return L.divIcon({
                className: '',
                html: '<div class="w-7 h-7 rounded-full ' + color + ' text-white flex items-center justify-center shadow-lg ring-4"><i class="ph-bold ph-map-pin text-xs"></i></div>',
                iconSize: [28, 28],
                iconAnchor: [14, 14],
            });
        },

        refreshIcons() {
            Object.entries(this.stopMarkers).forEach(([sid, { marker, zone }]) => {
                marker.setIcon(this.stopIcon(Number(sid), zone));
            });
        },

        searchAddress() {
            const q = this.query.trim();
            this.addressLat = null;
            this.addressLng = null;
            if (q.length < 3) { this.suggestions = []; return; }
            fetch('{{ route('parent.settings.address.search') }}?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => { this.suggestions = data; })
                .catch(() => { this.suggestions = []; });
        },

        pickSuggestion(s) {
            this.query = s.label;
            this.addressLat = s.lat;
            this.addressLng = s.lng;
            this.suggestions = [];
            if (!this.map) return;

            if (this.addressMarker) this.map.removeLayer(this.addressMarker);
            const homeIcon = L.divIcon({
                className: '',
                html: '<div class="w-7 h-7 rounded-full bg-amber-500 text-white flex items-center justify-center shadow-lg ring-4 ring-amber-300/40"><i class="ph-bold ph-house-line text-xs"></i></div>',
                iconSize: [28, 28],
                iconAnchor: [14, 14],
            });
            this.addressMarker = L.marker([s.lat, s.lng], { icon: homeIcon }).addTo(this.map).bindPopup('Votre adresse');
            this.map.setView([s.lat, s.lng], 13);
        },

        distanceTo(stop) {
            if (this.addressLat === null) return null;
            const R = 6371000;
            const toRad = d => d * Math.PI / 180;
            const dLat = toRad(stop.lat - this.addressLat);
            const dLng = toRad(stop.lng - this.addressLng);
            const a = Math.sin(dLat / 2) ** 2
                + Math.cos(toRad(this.addressLat)) * Math.cos(toRad(stop.lat)) * Math.sin(dLng / 2) ** 2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        },

        /** A zone's distance = its single closest stop — the most useful number for "which zone is near me". */
        zoneDistance(zone) {
            if (this.addressLat === null) return null;
            return Math.min(...zone.stops.map(s => this.distanceTo(s)));
        },

        get sortedZones() {
            if (this.addressLat === null) return this.zones;
            return [...this.zones].sort((a, b) => this.zoneDistance(a) - this.zoneDistance(b));
        },

        get stopsInSelectedZone() {
            const zone = this.zones.find(z => z.zone === this.selectedZone);
            if (!zone) return [];
            if (this.addressLat === null) return zone.stops;
            return [...zone.stops].sort((a, b) => this.distanceTo(a) - this.distanceTo(b));
        },

        selectZone(zone) {
            this.selectedZone = zone;
            this.selectedStopId = null;
            this.refreshIcons();

            const zoneStops = this.zones.find(z => z.zone === zone)?.stops ?? [];
            const bounds = zoneStops.map(s => [s.lat, s.lng]);
            if (this.map && bounds.length > 0) {
                bounds.length > 1 ? this.map.fitBounds(bounds, { padding: [24, 24] }) : this.map.setView(bounds[0], 15);
            }
        },

        /** A marker click can land on a stop outside the currently selected zone (or none selected yet) — keep both in sync either way. */
        selectStop(id, zone) {
            this.selectedZone = zone;
            this.selectedStopId = id;
            this.refreshIcons();

            const entry = this.stopMarkers[id];
            if (entry && this.map) {
                this.map.panTo(entry.marker.getLatLng());
                entry.marker.openPopup();
            }
        },
    }));
});
</script>
@endpush

@endsection
