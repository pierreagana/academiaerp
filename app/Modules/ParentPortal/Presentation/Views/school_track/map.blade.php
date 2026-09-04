@extends('ParentPortal::layout')

@section('title', 'School Track – Carte Interactive')

@push('styles')
<!-- Leaflet CSS -->
<style>
    #school-map {
        height: 620px;
        width: 100%;
        border-radius: 1.25rem;
        z-index: 10;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 1rem;
        padding: 4px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .leaflet-popup-content {
        margin: 8px 12px;
        line-height: 1.4;
    }
    .custom-pin {
        background: #061536;
        color: white;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 11px;
        transition: transform 0.2s ease;
    }
    .custom-pin:hover {
        transform: scale(1.15);
    }
</style>
@endpush

@section('content')

<!-- TOP HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-extrabold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200/60">
                School Track Map
            </span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Carte Interactive des Écoles</h1>
        <p class="text-sm font-medium text-slate-500 mt-1">
            Visualisez la position géographique des établissements.
        </p>
    </div>

    <div class="flex items-center gap-2.5">
        <a href="{{ route('parent.school-track.index') }}" 
           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
            <span class="material-symbols-outlined text-[17px] text-blue-600">view_module</span>
            <span>Vue Grille</span>
        </a>
        <a href="{{ route('parent.school-track.compare') }}" 
           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs shadow-sm transition">
            <span class="material-symbols-outlined text-[17px] text-blue-300">compare_arrows</span>
            <span>Comparateur ({{ count($comparisonIds) }})</span>
        </a>
    </div>
</div>

<!-- FILTERS TOOLBAR MATCHING SCREENSHOT 3 -->
<div class="bg-white rounded-2xl border border-slate-200 p-3.5 shadow-sm mb-5">
    <form method="GET" action="{{ route('parent.school-track.map') }}" class="flex flex-wrap items-center gap-2.5 text-xs font-bold text-slate-700">
        
        <div class="flex items-center gap-1 text-slate-400 mr-1">
            <span class="material-symbols-outlined text-[18px]">tune</span>
            <span class="text-[11px] uppercase tracking-wider">Filtres :</span>
        </div>

        <!-- Education Level -->
        <select name="level" onchange="this.form.submit()" 
                class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold outline-none focus:border-blue-500">
            <option value="all" {{ empty($level) || $level === 'all' ? 'selected' : '' }}>Tous les cycles</option>
            <option value="Maternelle" {{ $level === 'Maternelle' ? 'selected' : '' }}>Maternelle</option>
            <option value="Primaire" {{ $level === 'Primaire' ? 'selected' : '' }}>Primaire</option>
            <option value="Collège" {{ $level === 'Collège' ? 'selected' : '' }}>Collège</option>
            <option value="Lycée" {{ $level === 'Lycée' ? 'selected' : '' }}>Lycée</option>
        </select>

        <!-- Min performance score (real) -->
        <select name="min_rating" onchange="this.form.submit()"
                class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold outline-none focus:border-blue-500">
            <option value="" {{ empty($minRating) ? 'selected' : '' }}>Tous les scores</option>
            <option value="80" {{ $minRating == '80' ? 'selected' : '' }}>Score 80% et +</option>
            <option value="60" {{ $minRating == '60' ? 'selected' : '' }}>Score 60% et +</option>
            <option value="40" {{ $minRating == '40' ? 'selected' : '' }}>Score 40% et +</option>
        </select>

        <!-- Clear all -->
        @if(!empty($level) && $level !== 'all' || !empty($minRating) || !empty($query))
        <a href="{{ route('parent.school-track.map') }}" class="text-xs font-bold text-blue-600 hover:underline ml-auto">
            Effacer les filtres
        </a>
        @endif
    </form>
</div>

<!-- SPLIT VIEW MATCHING SCREENSHOT 3: LIST ON LEFT, MAP ON RIGHT -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
    
    <!-- LEFT COLUMN: SCHOOLS SCROLLABLE LIST (5 COLUMNS) -->
    <div class="lg:col-span-5 flex flex-col space-y-3">
        <div class="flex items-center justify-between px-1">
            <p class="text-xs font-extrabold text-slate-800">
                Affichage de {{ $schools->count() }} établissements
            </p>
        </div>

        <div class="space-y-3 overflow-y-auto max-h-[620px] pr-1">
            @forelse($schools as $sch)
            @php
                $isCompared = in_array($sch['model_id'], $comparisonIds);
                $hasPosition = $sch['lat'] !== null && $sch['lng'] !== null;
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200/90 p-3 shadow-sm transition flex items-center gap-3.5 group {{ $hasPosition ? 'hover:border-blue-400 hover:shadow-md cursor-pointer' : '' }}"
                 @if($hasPosition) onclick="focusSchool({{ $sch['lat'] }}, {{ $sch['lng'] }}, '{{ addslashes($sch['name']) }}')" @endif>
                
                <!-- Fixed size Thumbnail: guaranteed 80px x 80px -->
                <div class="w-20 h-20 min-w-[80px] max-w-[80px] h-[80px] rounded-xl overflow-hidden shrink-0 bg-slate-100 shadow-xs border border-slate-100">
                    <img src="{{ $sch['photo'] }}" alt="{{ $sch['name'] }}" 
                         class="w-full h-full object-cover rounded-xl"
                         style="width: 80px; height: 80px; object-fit: cover; display: block;">
                </div>

                <!-- Info Column -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-1 mb-0.5">
                        <h3 class="text-[13px] font-black text-slate-900 leading-tight truncate group-hover:text-blue-600 transition">
                            {{ $sch['name'] }}
                        </h3>
                    </div>

                    <!-- Distance (only when a real reference + geocode both exist) -->
                    @if($sch['distance_formatted'])
                    <p class="text-[11px] font-semibold text-slate-400 flex items-center gap-1 mb-2 truncate">
                        <span class="material-symbols-outlined text-[13px] text-slate-400 shrink-0">location_on</span>
                        <span>{{ $sch['distance_formatted'] }} de {{ $distanceLabel }}</span>
                    </p>
                    @elseif(!$hasPosition)
                    <p class="text-[11px] font-semibold text-slate-400 flex items-center gap-1 mb-2 truncate">
                        <span class="material-symbols-outlined text-[13px] text-slate-400 shrink-0">location_off</span>
                        <span>Position non renseignée</span>
                    </p>
                    @endif

                    <!-- Bottom row: Performance score + Compare checkbox -->
                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100">
                        @if($sch['performanceScore'] !== null)
                        <div class="flex items-center gap-1 bg-amber-50 border border-amber-200/60 px-2 py-0.5 rounded-md">
                            <span class="material-symbols-outlined text-[12px] text-amber-500 font-fill">trending_up</span>
                            <span class="text-[11px] font-black text-amber-900">{{ round($sch['performanceScore']) }}%</span>
                        </div>
                        @else
                        <span></span>
                        @endif

                        <!-- Compare Checkbox Form -->
                        <form action="{{ route('parent.school-track.compare.toggle') }}" method="POST" onclick="event.stopPropagation()">
                            @csrf
                            <input type="hidden" name="school_id" value="{{ $sch['model_id'] }}">
                            <label class="flex items-center gap-1.5 cursor-pointer text-[11px] font-bold text-slate-600 hover:text-[#061536]">
                                <input type="checkbox" onchange="this.form.submit()" {{ $isCompared ? 'checked' : '' }} 
                                       class="rounded text-[#061536] focus:ring-0 cursor-pointer">
                                <span>Comparer</span>
                            </label>
                        </form>
                    </div>
                </div>

            </div>
            @empty
            <div class="bg-white rounded-2xl p-8 text-center text-slate-400 text-xs">
                Aucun établissement ne correspond à ces critères.
            </div>
            @endforelse
        </div>
    </div>

    <!-- RIGHT COLUMN: INTERACTIVE MAP EXPLORER (7 COLUMNS) -->
    <div class="lg:col-span-7">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-3 overflow-hidden relative">
            
            <!-- Map Top Bar Overlay matching Screenshot 3 -->
            <div class="absolute top-6 left-6 z-20 bg-white/95 backdrop-blur-md rounded-2xl px-4 py-2 border border-slate-200/80 shadow-md pointer-events-auto">
                <p class="text-xs font-black text-slate-900">Map Explorer</p>
                <p class="text-[10px] font-bold text-slate-500">Interactive Zone View</p>
            </div>

            <!-- Leaflet Container with strict height -->
            <div id="school-map"></div>

        </div>
    </div>

</div>

@endsection

@push('scripts')
<!-- Leaflet JS -->
<script>
    let map;
    let markers = [];

    document.addEventListener('DOMContentLoaded', function () {
        map = L.map('school-map', { zoomControl: false });

        L.control.zoom({ position: 'topright' }).addTo(map);

        // Standard OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        // Add school markers from PHP data — only schools with a real geocode
        const schoolsData = @json($schools).filter(s => s.lat !== null && s.lng !== null);

        schoolsData.forEach(s => {
            const iconHtml = `<div class="custom-pin" style="width:34px;height:34px;">
                <span class="material-symbols-outlined" style="font-size:18px;">school</span>
            </div>`;

            const customIcon = L.divIcon({
                html: iconHtml,
                className: 'custom-leaflet-marker',
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });

            const marker = L.marker([s.lat, s.lng], { icon: customIcon }).addTo(map);

            const popupContent = document.createElement('div');
            popupContent.style.cssText = 'width:210px;font-family:sans-serif;';
            popupContent.innerHTML = `
                <img style="width:100%;height:95px;object-fit:cover;border-radius:10px;margin-bottom:8px;display:block;">
                <p style="font-weight:900;font-size:13px;margin:0 0 2px 0;color:#061536;"></p>
                <p style="font-size:11px;color:#64748b;margin:0 0 8px 0;"></p>
                <div style="display:flex;align-items:center;justify-content:space-between;gap:4px;">
                    <span style="font-weight:800;font-size:11px;background:#fef3c7;color:#92400e;padding:2px 7px;border-radius:6px;"></span>
                    <a style="font-size:11px;font-weight:800;background:#061536;color:white;padding:4px 10px;border-radius:8px;text-decoration:none;">Fiche &rarr;</a>
                </div>
            `;
            if (s.photo) popupContent.querySelector('img').src = s.photo;
            popupContent.querySelectorAll('p')[0].textContent = s.name;
            popupContent.querySelectorAll('p')[1].textContent = s.location || 'Localisation non renseignée';
            popupContent.querySelector('span').textContent = s.performanceScore !== null ? Math.round(s.performanceScore) + '%' : 'N/A';
            popupContent.querySelector('a').href = `/parent/school-track/${s.model_id}`;

            marker.bindPopup(popupContent);
            markers.push({ id: s.model_id, marker: marker, lat: s.lat, lng: s.lng });
        });

        // Fit the view to the real markers — no fixed/fabricated center point.
        if (markers.length > 0) {
            map.fitBounds(L.featureGroup(markers.map(m => m.marker)).getBounds().pad(0.15));
        } else {
            map.setView([0, 0], 2);
        }

        // Resize fix to ensure smooth rendering inside flex/grid layouts
        setTimeout(() => {
            map.invalidateSize();
        }, 200);
    });

    function focusSchool(lat, lng, name) {
        if (map) {
            map.flyTo([lat, lng], 15, { duration: 1.2 });
            const m = markers.find(item => item.lat === lat && item.lng === lng);
            if (m) {
                m.marker.openPopup();
            }
        }
    }
</script>
@endpush
