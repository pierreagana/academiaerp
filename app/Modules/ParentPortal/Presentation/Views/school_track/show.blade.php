@extends('ParentPortal::layout')

@section('title', $data['name'] . ' – School Track')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.css" />
<style>
    #mini-map {
        height: 300px;
        border-radius: 1rem;
    }
    #mini-map:fullscreen {
        border-radius: 0;
    }
</style>
@endpush

@section('content')

<!-- BREADCRUMB -->
<div class="flex items-center gap-2 text-xs font-bold text-slate-400 mb-4">
    <a href="{{ route('parent.school-track.index') }}" class="text-blue-600 hover:underline flex items-center gap-1">
        <span class="material-symbols-outlined text-[15px]">arrow_back</span>
        <span>Explorer les écoles</span>
    </a>
    <span>/</span>
    <span class="text-slate-600 truncate">{{ $data['name'] }}</span>
</div>

<!-- TOP HERO BANNER & SCHOOL IDENTITY MATCHING SCREENSHOT 4 -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">

    <!-- Hero Banner Image -->
    <div class="h-64 sm:h-80 w-full relative bg-slate-900">
        <img src="{{ $data['photo'] }}" alt="{{ $data['name'] }}" class="w-full h-full object-cover opacity-90">
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

        <!-- Top Badges -->
        <div class="absolute top-4 left-4 flex items-center gap-2">
            @if($data['level'])
            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-[#061536]/80 text-white backdrop-blur-md border border-white/20">
                {{ $data['level'] }}
            </span>
            @endif
            @if($data['performanceScore'] !== null)
            <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-400 text-slate-950 flex items-center gap-1 shadow-sm">
                <span class="material-symbols-outlined text-[15px] font-fill">trending_up</span>
                <span>{{ round($data['performanceScore']) }}%</span>
            </span>
            @endif
        </div>

        <!-- Top Right Actions: Compare -->
        <div class="absolute top-4 right-4 flex items-center gap-2">
            <form action="{{ route('parent.school-track.compare.toggle') }}" method="POST">
                @csrf
                <input type="hidden" name="school_id" value="{{ $school->id }}">
                <button type="submit"
                        class="px-3.5 py-2 rounded-xl bg-[#061536]/90 hover:bg-[#061536] text-white border border-white/20 backdrop-blur-md transition flex items-center gap-1.5 text-xs font-bold shadow-md">
                    <span class="material-symbols-outlined text-[16px]">compare_arrows</span>
                    <span>Comparer</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Identity Bar Below Banner -->
    <div class="p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">

        <div class="flex items-start gap-4">
            <!-- School Logo Card -->
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white border-2 border-slate-100 shadow-md flex items-center justify-center p-2 shrink-0 -mt-12 sm:-mt-16 z-10">
                <span class="material-symbols-outlined text-3xl sm:text-4xl text-[#061536]">school</span>
            </div>

            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-tight">
                    {{ $data['name'] }}
                </h1>
                <p class="text-xs sm:text-sm font-bold text-slate-500 flex items-center gap-1.5 mt-1">
                    <span class="material-symbols-outlined text-[18px] text-slate-400">location_on</span>
                    <span>{{ $data['location'] ?? 'Localisation non renseignée' }}</span>
                </p>
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @foreach($data['levels'] ?? [] as $lvl)
                    <span class="px-2.5 py-0.5 rounded-lg bg-blue-50 text-blue-800 text-[11px] font-bold">
                        {{ $lvl }}
                    </span>
                    @endforeach
                    @if($data['frais_numeric'] !== null)
                    <span class="px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-800 text-[11px] font-bold">
                        {{ $data['frais_formatted'] }} / an
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Student Body & Est. Boxes Matching Screenshot 4 -->
        <div class="flex items-center gap-3 shrink-0">
            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl px-4 py-3 text-center min-w-[100px]">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Effectif Élèves</span>
                <span class="text-xl font-black text-slate-900">{{ $school->students_count ? number_format($school->students_count, 0, ',', ' ') : 'Non renseigné' }}</span>
            </div>

            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl px-4 py-3 text-center min-w-[100px]">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Fondation</span>
                <span class="text-xl font-black text-slate-900">{{ $school->founded_date ? $school->founded_date->format('Y') : 'Non renseigné' }}</span>
            </div>
        </div>

    </div>

</div>

<!-- MAIN GRID: 2 COLUMNS MATCHING SCREENSHOT 4 & 5 -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- LEFT COLUMN: AI INSIGHT + ACADEMIC PERFORMANCE + INFRASTRUCTURE (8 COLS) -->
    <div class="lg:col-span-6 space-y-6">

        <!-- 1. AI PERFORMANCE INSIGHT BOX MATCHING SCREENSHOT 4 -->
        <div class="bg-blue-50/50 rounded-3xl border border-blue-200/70 p-6 shadow-xs">
            <div class="flex items-start gap-3.5 mb-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/30 shrink-0">
                    <span class="material-symbols-outlined text-[22px]">auto_awesome</span>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">Aperçu de Performance</h2>
                    <p class="text-[11.5px] font-semibold text-blue-600">Basé sur les examens et promotions réellement validés par l'établissement</p>
                </div>
            </div>

            @php
                $insightParts = [];
                if ($data['successRate'] !== null) {
                    $insightParts[] = "Taux de réussite aux examens validés : <strong>{$data['successRate']}%</strong>.";
                }
                if ($data['progressionAnnuelle'] !== null) {
                    $sign = $data['progressionAnnuelle'] >= 0 ? '+' : '';
                    $insightParts[] = "Évolution sur un an : <strong>{$sign}{$data['progressionAnnuelle']} points</strong>.";
                }
                if ($data['ratioProf']) {
                    $insightParts[] = "Ratio élèves / enseignant : <strong>{$data['ratioProf']}</strong>.";
                }
            @endphp

            <p class="text-xs sm:text-[13px] text-slate-700 font-medium leading-relaxed mb-4">
                @if(!empty($insightParts))
                    {!! implode(' ', $insightParts) !!}
                @elseif($data['aiInsight'])
                    {{ $data['aiInsight'] }}
                @else
                    Pas encore assez de données validées (examens, promotions) pour établir un aperçu de performance pour {{ $data['name'] }}.
                @endif
            </p>

            @if(!empty($data['tags']))
            <div class="flex flex-wrap items-center gap-2">
                @foreach($data['tags'] as $tag)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white border border-blue-200 text-blue-700 font-extrabold text-[11.5px] shadow-xs">
                    <span class="material-symbols-outlined text-[15px]">label</span>
                    <span>{{ $tag }}</span>
                </span>
                @endforeach
            </div>
            @endif
        </div>

        <!-- 2. ACADEMIC PERFORMANCE TABLE MATCHING SCREENSHOT 4 -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[22px] text-[#061536]">school</span>
                    <h2 class="text-base font-black text-slate-900">Performance Académique</h2>
                </div>
            </div>

            @if($academicMetrics->isEmpty())
            <p class="text-xs text-slate-400 text-center py-6">Aucune session d'examen validée par cet établissement pour l'instant.</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="border-b border-slate-100 text-[11px] font-black text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-2">Indicateur Clé</th>
                            <th class="py-3 px-3 text-center">{{ $previousYear }}</th>
                            <th class="py-3 px-3 text-center">{{ $currentYear }}</th>
                            <th class="py-3 px-3 text-right">Tendance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                        @foreach($academicMetrics as $m)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-2 font-bold text-slate-900">{{ $m['metric'] }}</td>
                            <td class="py-3.5 px-3 text-center text-slate-500">{{ $m['yPrevious'] }}</td>
                            <td class="py-3.5 px-3 text-center font-black text-slate-900 text-[13px]">{{ $m['yCurrent'] }}</td>
                            <td class="py-3.5 px-3 text-right">
                                @if($m['trend'])
                                <span class="inline-flex items-center gap-1 font-black {{ $m['trend_up'] ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50' }} px-2.5 py-0.5 rounded-lg text-[11.5px]">
                                    <span class="material-symbols-outlined text-[14px]">{{ $m['trend_up'] ? 'arrow_upward' : 'arrow_downward' }}</span>
                                    <span>{{ $m['trend'] }}</span>
                                </span>
                                @else
                                <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- 3. INFRASTRUCTURE & FACILITIES MATCHING SCREENSHOT 4 & 5 -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[22px] text-[#061536]">domain</span>
                    <h2 class="text-base font-black text-slate-900">Infrastructures & Équipements</h2>
                </div>
                <span class="text-xs font-bold text-slate-400">Équipements déclarés par l'établissement</span>
            </div>

            @if($facilityCards->isEmpty())
            <p class="text-xs text-slate-400 text-center py-6">Aucun équipement renseigné pour cet établissement.</p>
            @else
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($facilityCards as $fc)
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 text-center flex flex-col items-center justify-center hover:bg-blue-50/40 hover:border-blue-300 transition">
                    <div class="w-11 h-11 rounded-2xl bg-white text-blue-600 shadow-sm flex items-center justify-center mb-2.5">
                        <i class="ph-bold {{ $fc['ph_icon'] }} text-[22px]"></i>
                    </div>
                    <p class="text-xs font-black text-slate-900 leading-tight mb-1">{{ $fc['title'] }}</p>
                    @if($fc['desc'])
                    <p class="text-[10.5px] font-semibold text-slate-400 leading-tight">{{ $fc['desc'] }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>

    <!-- RIGHT COLUMN: CAMPUS LOCATION + SECURITY + REVIEWS (4 COLS) -->
    <div class="lg:col-span-6 space-y-6">

        <!-- 1. CAMPUS LOCATION CARD MATCHING SCREENSHOT 4 -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px] text-blue-600">location_on</span>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">Localisation du Campus</h3>
                </div>
                @if(!empty($data['nearbyPlaces']))
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-lg">
                    <span class="material-symbols-outlined text-[13px]">place</span>
                    <span>{{ count($data['nearbyPlaces']) }} lieux à proximité</span>
                </span>
                @endif
            </div>

            @if($data['lat'] !== null && $data['lng'] !== null)
            <div id="mini-map" class="mb-3"></div>
            @else
            <div class="mb-3 h-[300px] rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-xs text-slate-400 font-semibold">
                Position non renseignée
            </div>
            @endif

            <p class="text-xs font-bold text-slate-700 mb-1">{{ $data['location'] ?? 'Localisation non renseignée' }}</p>
            @if($data['distance_formatted'])
            <p class="text-[11px] text-slate-400 flex items-center gap-1 mb-3">
                <span class="material-symbols-outlined text-[14px]">near_me</span>
                <span>{{ $data['distance_formatted'] }} de {{ $distanceLabel }}</span>
            </p>
            @endif

            <!-- Real nearby amenities (OpenStreetMap/Overpass), also pinned on the mini-map above -->
            @if(!empty($data['nearbyPlaces']))
            <div class="mb-3 pt-3 border-t border-slate-100 space-y-2 max-h-52 overflow-y-auto">
                @foreach($data['nearbyPlaces'] as $place)
                <div class="flex items-center justify-between gap-2 text-[11px]">
                    <span class="flex items-center gap-2 min-w-0">
                        <span class="text-sm leading-none shrink-0">{{ $place['emoji'] }}</span>
                        <span class="font-semibold text-slate-700 truncate">{{ $place['label'] }}</span>
                    </span>
                    <span class="font-bold text-slate-400 shrink-0">{{ $place['distance'] }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <a href="{{ route('parent.school-track.map') }}"
               class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition">
                <span>Ouvrir dans la carte interactive</span>
                <span class="material-symbols-outlined text-[15px]">open_in_new</span>
            </a>
        </div>


    </div>

</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mapEl = document.getElementById('mini-map');
        if (!mapEl) return; // no real geocode for this school — placeholder shown instead

        const lat = {{ $data['lat'] ?? 'null' }};
        const lng = {{ $data['lng'] ?? 'null' }};

        const miniMap = L.map('mini-map', {
            center: [lat, lng],
            zoom: 15,
            zoomControl: true,
            dragging: true,
            scrollWheelZoom: true
        });

        L.control.fullscreen({ position: 'topright' }).addTo(miniMap);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(miniMap);

        const customIcon = L.divIcon({
            html: `<div style="background:#061536;color:white;width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid white;box-shadow:0 4px 6px rgba(0,0,0,0.3);">
                <span class="material-symbols-outlined" style="font-size:16px;">school</span>
            </div>`,
            className: '',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });

        const schoolPopup = document.createElement('b');
        schoolPopup.textContent = @json($data['name']);
        L.marker([lat, lng], { icon: customIcon }).addTo(miniMap).bindPopup(schoolPopup);

        // Real nearby amenities (OpenStreetMap/Overpass) — same list shown below the map.
        const nearbyPlaces = @json($data['nearbyPlaces'] ?? []);
        nearbyPlaces.forEach(function (p) {
            const color = '#' + (p.colorHex & 0xFFFFFF).toString(16).padStart(6, '0');
            const placeIcon = L.divIcon({
                html: `<div style="background:${color};width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid white;box-shadow:0 2px 4px rgba(0,0,0,0.3);font-size:11px;line-height:1;">${p.emoji}</div>`,
                className: '',
                iconSize: [22, 22],
                iconAnchor: [11, 11]
            });
            const popup = document.createElement('div');
            const strong = document.createElement('b');
            strong.textContent = p.label;
            popup.appendChild(strong);
            popup.appendChild(document.createElement('br'));
            popup.appendChild(document.createTextNode(p.distance));

            L.marker([p.latitude, p.longitude], { icon: placeIcon }).addTo(miniMap).bindPopup(popup);
        });
    });
</script>
@endpush
