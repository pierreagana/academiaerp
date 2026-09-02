@extends('ParentPortal::layout')

@section('title', 'Services de Vie Scolaire')

@push('styles')
<!-- Leaflet CSS for Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .leaflet-control-attribution { display: none !important; }
</style>
@endpush

@section('content')

<!-- HEADER -->
<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Services de Vie Scolaire</h1>
    <p class="text-sm font-medium text-slate-500 mt-1">Gérez la cantine, le transport et le suivi santé de vos enfants.</p>
</div>

<!-- TOP GRID: MENU & CANTINE (Col 8) + SANTÉ & INFIRMERIE (Col 4) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
    
    <!-- MENU & CANTINE (Col 8) -->
    <div class="lg:col-span-8 bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                    <span class="material-symbols-outlined text-[19px]">restaurant</span>
                </div>
                <h2 class="text-sm font-extrabold text-slate-900">Menu & Cantine</h2>
            </div>

            @if($selectedChild)
            <a href="{{ route('parent.canteen', $selectedChild->id) }}" class="text-xs font-bold text-blue-700 hover:underline flex items-center gap-1">
                <span>Gérer les allergies</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
            @endif
        </div>

        <!-- 3 DAYS MENU CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
            @forelse($canteenDays as $day)
            <div class="p-4 rounded-2xl border transition flex flex-col justify-between {{ $day['is_today'] ? 'border-[#061536] bg-white ring-2 ring-[#061536]/10 shadow-sm' : 'border-slate-100 bg-white hover:border-slate-200' }}">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-extrabold text-slate-900">{{ $day['day_label'] }}</span>
                        @if($day['title'])
                            <span class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                <span class="material-symbols-outlined text-[14px]">check_circle</span>
                            </span>
                        @endif
                    </div>

                    @if($day['title'])
                        <h3 class="text-xs font-black text-slate-900 leading-snug mb-1">{{ $day['title'] }}</h3>
                    @else
                        <div class="py-6 text-center">
                            <p class="text-xs italic text-slate-400">Menu non publié</p>
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400 sm:col-span-3 text-center py-6">Aucun menu publié cette semaine.</p>
            @endforelse
        </div>
    </div>

    <!-- SANTÉ & INFIRMERIE (Col 4) -->
    <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                    <span class="material-symbols-outlined text-[19px]">medical_services</span>
                </div>
                <h2 class="text-sm font-extrabold text-slate-900">Santé & Infirmerie</h2>
            </div>

            <!-- Recent Treatment Entry -->
            @if($healthRecords['recentIntervention'] ?? null)
            <div class="p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100 mb-3 flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-white text-slate-600 flex items-center justify-center shrink-0 shadow-2xs">
                    <span class="material-symbols-outlined text-[17px]">medication</span>
                </div>
                <div class="min-w-0">
                    <h3 class="text-xs font-black text-slate-900 leading-tight">{{ $healthRecords['recentIntervention']['treatment'] }}</h3>
                    <p class="text-[11px] font-medium text-slate-500 mt-0.5">{{ $healthRecords['recentIntervention']['reason'] }}</p>
                    <p class="text-[10.5px] font-bold text-slate-400 mt-1">{{ $healthRecords['recentIntervention']['staff'] }}</p>
                </div>
            </div>
            @else
            <p class="text-xs text-slate-400 mb-3">Aucune visite à l'infirmerie enregistrée.</p>
            @endif

            <!-- Vaccine Alert Banner -->
            @if($healthRecords['vaccineAlert'] ?? null)
            <div class="p-3.5 rounded-2xl bg-rose-50/60 border border-rose-100/80 flex items-start gap-2.5">
                <span class="material-symbols-outlined text-[18px] text-rose-600 shrink-0 mt-0.5">notifications_active</span>
                <div>
                    <h4 class="text-xs font-black text-rose-900 leading-tight">{{ $healthRecords['vaccineAlert']['title'] }}</h4>
                    <p class="text-[11px] font-medium text-rose-700/90 mt-0.5 leading-snug">{{ $healthRecords['vaccineAlert']['message'] }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="mt-5 pt-3 border-t border-slate-100">
            <a href="{{ route('parent.infirmary') }}" 
               class="w-full inline-flex items-center justify-center gap-2 bg-blue-100/70 hover:bg-blue-200/80 text-[#061536] font-bold text-xs py-2.5 rounded-xl transition">
                <span class="material-symbols-outlined text-[16px]">health_and_safety</span>
                <span>Carnet de Santé & Infirmerie</span>
            </a>
        </div>
    </div>

</div>

<!-- BOTTOM CARD: TRANSPORT GPS -->
<div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden">
@if($transportGPS)
    <div class="grid grid-cols-1 lg:grid-cols-12">

        <!-- LEFT: GPS DETAILS & TIMELINE (Col 5) -->
        <div class="lg:col-span-5 p-6 flex flex-col justify-between border-b lg:border-b-0 lg:border-r border-slate-100">
            <div>
                <div class="flex items-center gap-2.5 mb-5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[19px]">directions_bus</span>
                    </div>
                    <h2 class="text-sm font-extrabold text-slate-900">Transport GPS &bull; {{ $transportGPS['busNumber'] }}</h2>
                </div>

                <!-- Live Status Tile -->
                <div class="bg-[#F8FAFC] rounded-2xl p-4 border border-slate-100 mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-black text-slate-900">{{ $transportGPS['line'] }}</span>
                        <span class="inline-flex items-center gap-1 text-[10.5px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full {{ $transportGPS['isLive'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $transportGPS['isLive'] ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                            <span>{{ $transportGPS['status'] }}</span>
                        </span>
                    </div>

                    @if($transportGPS['nextStopName'])
                    <div class="text-xl font-black text-slate-900 tracking-tight">
                        Prochain arrêt : <span class="text-[#061536]">{{ $transportGPS['nextStopName'] }}</span>
                    </div>
                    @endif
                    @if($transportGPS['positionUpdatedAt'])
                    <p class="text-xs font-semibold text-slate-500 mt-1">Position mise à jour à {{ $transportGPS['positionUpdatedAt']->format('H:i') }}</p>
                    @endif
                </div>

                <!-- HISTORIQUE RÉCENT TIMELINE -->
                <div>
                    <h3 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-3">Historique Récent</h3>
                    <div class="space-y-4">
                        @forelse($transportGPS['history'] as $h)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[17px]">{{ $h['icon'] }}</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 leading-tight">{{ $h['event'] }}</h4>
                                <p class="text-[11px] font-medium text-slate-400 mt-0.5">{{ $h['time'] }}</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-slate-400">Aucun arrêt confirmé aujourd'hui.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            @if($selectedChild)
            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('parent.transport', $selectedChild->id) }}" class="text-xs font-bold text-blue-700 hover:underline flex items-center gap-1">
                    <span>Voir tous les arrêts et horaires</span>
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>
            @endif
        </div>

        <!-- RIGHT: LIVE INTERACTIVE MAP (Col 7) -->
        <div class="lg:col-span-7 h-80 lg:h-auto min-h-[340px] relative bg-slate-100">
            @if($transportGPS['isLive'])
            <div id="live-transport-map" class="w-full h-full" data-lat="{{ $transportGPS['latitude'] }}" data-lng="{{ $transportGPS['longitude'] }}" data-bus="{{ $transportGPS['busNumber'] }}"></div>

            <!-- Map Overlay Bus Pin Indicator -->
            <div class="absolute top-4 left-4 z-1000 bg-white/95 backdrop-blur-xs px-3 py-1.5 rounded-xl shadow-md border border-slate-200/80 flex items-center gap-2 text-xs font-bold text-slate-800 pointer-events-none">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span>Position en direct</span>
            </div>
            @else
            <div class="w-full h-full flex items-center justify-center">
                <p class="text-sm text-slate-400 font-semibold">Le bus n'a pas encore transmis de position aujourd'hui.</p>
            </div>
            @endif
        </div>

    </div>
@else
    <div class="p-12 text-center text-sm text-slate-400">Aucun bus assigné à cet élève pour l'instant.</div>
@endif
</div>

@endsection

@push('scripts')
<!-- Leaflet JS for Map -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapEl = document.getElementById('live-transport-map');
    if (!mapEl) return;

    const busLat = parseFloat(mapEl.dataset.lat);
    const busLng = parseFloat(mapEl.dataset.lng);
    if (!isFinite(busLat) || !isFinite(busLng)) return;

    const map = L.map('live-transport-map', {
        zoomControl: false
    }).setView([busLat, busLng], 14);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        maxZoom: 19
    }).addTo(map);

    // Custom Bus Icon
    const busIcon = L.divIcon({
        className: 'custom-bus-marker',
        html: `<div class="w-9 h-9 rounded-full bg-[#061536] text-white flex items-center justify-center shadow-lg ring-4 ring-blue-400/30">
                 <i class="ph-bold ph-bus text-lg"></i>
               </div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });

    const popupEl = document.createElement('b');
    popupEl.textContent = mapEl.dataset.bus || 'Bus';
    L.marker([busLat, busLng], { icon: busIcon }).addTo(map)
        .bindPopup(popupEl)
        .openPopup();

    // Zoom control on bottom right
    L.control.zoom({ position: 'bottomright' }).addTo(map);
});
</script>
@endpush
