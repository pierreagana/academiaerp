@extends('SuperAdmin::layouts.app')

@push('scripts')
@endpush

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[28px] font-extrabold text-[#111827]">Gestion Multi-Campus</h2>
            <p class="text-[15px] text-slate-500 mt-1">Superviser et orchestrer les réseaux scolaires régionaux.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 shrink-0 mt-2 md:mt-0">
            <button type="button" onclick="openMapModal()" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-50 transition shadow-sm">
                <i class="ph ph-arrows-out-simple text-base font-bold text-indigo-600"></i> Agrandir la carte
            </button>
            <button type="button" onclick="openAddNetworkModal()" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-900 transition shadow-sm">
                <i class="ph ph-plus text-lg font-bold"></i> Ajouter un Réseau
            </button>
        </div>
    </div>

    {{-- Map & KPIs Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- Interactive Map Card --}}
        <div id="mapCardContainer" class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col relative transition-all duration-300 min-w-0 w-full max-w-full">
            <div class="absolute top-3 right-3 z-[400] flex items-center gap-2">
                <button type="button" onclick="recenterMap()" title="Centrer la carte sur tous les établissements" class="bg-white/90 backdrop-blur border border-slate-300 text-slate-700 px-3 py-1.5 rounded-xl text-xs font-extrabold hover:bg-white hover:text-indigo-600 transition shadow-md flex items-center gap-1.5">
                    <i class="ph ph-crosshair text-sm font-bold text-indigo-600"></i>
                    <span>Centrer</span>
                </button>
                <button type="button" onclick="openMapModal()" class="bg-white/90 backdrop-blur border border-slate-300 text-slate-700 px-3.5 py-1.5 rounded-xl text-xs font-extrabold hover:bg-white hover:text-indigo-600 transition shadow-md flex items-center gap-1.5">
                    <i class="ph ph-arrows-out-simple text-sm font-bold"></i>
                    <span>Plein écran</span>
                </button>
            </div>
            <div id="campusMap" class="w-full max-w-full h-[380px] z-0 transition-all duration-300"></div>

            <div id="mapBottomInfo" class="p-6 bg-white border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-1">COUVERTURE RÉGIONALE</p>
                    <p class="text-[16px] font-bold text-slate-900">
                        {{ $networks->count() }} région(s) — Afrique Francophone
                    </p>
                </div>
                <div class="sm:text-right">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-1">STATUT DU RÉSEAU</p>
                    <p class="flex items-center sm:justify-end gap-1.5 text-[15px] font-bold text-[#059669]">
                        <i class="ph ph-check-circle text-lg"></i> Opérationnel
                    </p>
                </div>
            </div>
        </div>

        {{-- KPI Cards Column --}}
        <div class="flex flex-col gap-6">

            {{-- Total Schools --}}
            <div class="bg-white rounded-[16px] border border-slate-200 shadow-sm p-6 relative overflow-hidden flex-1">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-[13px] font-bold text-slate-500">Total Établissements</h3>
                    <i class="ph ph-buildings text-4xl text-slate-100 absolute right-4 top-4"></i>
                </div>
                <h2 class="text-[36px] font-extrabold text-slate-900 mb-3 leading-none">{{ $stats['total_campus'] }}</h2>
                <span class="inline-flex items-center gap-1 bg-[#ECFDF5] text-[#059669] text-[11px] font-bold px-2.5 py-1 rounded-full">
                    <i class="ph ph-buildings"></i> {{ $stats['enterprise_count'] }} Enterprise, {{ $stats['premium_count'] }} Premium
                </span>
            </div>

            {{-- Total Enrolled --}}
            <div class="bg-white rounded-[16px] border border-slate-200 shadow-sm p-6 relative overflow-hidden flex-1">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-[13px] font-bold text-slate-500">Total Élèves Inscrits</h3>
                    <i class="ph ph-users-three text-4xl text-slate-100 absolute right-4 top-4"></i>
                </div>
                <h2 class="text-[36px] font-extrabold text-slate-900 mb-3 leading-none">
                    {{ number_format($stats['total_students'], 0, ',', ' ') }}
                </h2>
                <span class="inline-flex items-center gap-1 bg-[#ECFDF5] text-[#059669] text-[11px] font-bold px-2.5 py-1 rounded-full">
                    <i class="ph ph-trend-up"></i> Toutes écoles confondues
                </span>
            </div>

            {{-- Storage --}}
            <div class="bg-[#F8F5FF] rounded-[16px] border border-purple-100 shadow-sm p-6 flex-1">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-1.5">
                        <i class="ph ph-sparkle text-purple-600 text-lg"></i>
                        <h3 class="text-[13px] font-bold text-[#7C3AED]">Stockage Utilisé</h3>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-[#7C3AED] flex items-center justify-center shadow-md shadow-purple-500/20">
                        <i class="ph ph-hard-drive text-white text-lg"></i>
                    </div>
                </div>
                <h2 class="text-[32px] font-extrabold text-slate-900 mb-3 flex items-baseline gap-1">
                    {{ $stats['total_storage_gb'] }}<span class="text-[16px] text-slate-400 font-medium"> GB</span>
                </h2>
                <p class="text-[13px] font-medium text-slate-600 leading-relaxed">
                    Réparti sur <span class="text-[#7C3AED] font-bold">{{ $stats['total_campus'] }} campus</span> actifs dans le réseau.
                </p>
            </div>
        </div>
    </div>

    {{-- Campus Table from DB --}}
    <div class="bg-white border border-slate-200 rounded-[16px] overflow-hidden mb-8 shadow-sm">

        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 bg-[#FCFDFE]">
            <h3 class="text-[20px] font-extrabold text-[#111827]">Campus du Réseau</h3>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="relative w-full sm:w-[320px]">
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                    <input type="text" placeholder="Rechercher des campus..." class="w-full bg-white border border-slate-200 text-slate-700 text-sm rounded-lg pl-10 pr-4 py-2 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition shadow-sm">
                </div>
                <button class="bg-white border border-slate-200 text-slate-600 p-2 rounded-lg hover:bg-slate-50 transition shadow-sm shrink-0">
                    <i class="ph ph-funnel-simple text-lg font-bold"></i>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-widest bg-[#F8FAFC] border-b border-slate-200">
                        <th class="py-4 px-6">DÉTAILS DU CAMPUS</th>
                        <th class="py-4 px-4">EMPLACEMENT</th>
                        <th class="py-4 px-4">FORFAIT</th>
                        <th class="py-4 px-4">ÉLÈVES</th>
                        <th class="py-4 px-4 text-center">STATUT</th>
                        <th class="py-4 px-6 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[14px]">
                    @forelse($allSchools as $school)
                        @php
                            $initials = collect(explode(' ', $school->name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');
                            $statusMap = [
                                'actif'       => ['label' => 'Actif',         'class' => 'bg-[#D1FAE5] text-[#065F46] border border-[#A7F3D0]'],
                                'inactif'     => ['label' => 'Inactif',        'class' => 'bg-slate-100 text-slate-500'],
                                'suspendu'    => ['label' => 'Suspendu',       'class' => 'bg-red-50 text-red-600'],
                                'en attente'  => ['label' => 'En attente',     'class' => 'bg-[#DBEAFE] text-[#1D4ED8] border border-[#BFDBFE]'],
                            ];
                            $badge = $statusMap[$school->status] ?? ['label' => $school->status, 'class' => 'bg-slate-100 text-slate-600'];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-[#031C5B] font-extrabold text-[13px] shrink-0">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#031C5B] text-[14px] mb-0.5">{{ $school->name }}</p>
                                        <p class="text-[12px] font-medium text-slate-400">{{ $school->type ?? 'Établissement' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-1.5 text-slate-600 font-medium">
                                    <i class="ph ph-map-pin"></i> {{ $school->location ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-[13px] font-bold text-[#031C5B]">{{ $school->plan_name }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex flex-col gap-1 min-w-[140px]">
                                    <div class="flex justify-between text-[12px] font-medium text-slate-600">
                                        <span class="font-bold">{{ number_format($school->students_count, 0, ',', ' ') }}</span>
                                        <span class="text-slate-400">élèves</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        @php
                                            $maxStudents = $allSchools->max('students_count') ?: 1;
                                            $pct = round(($school->students_count / $maxStudents) * 100);
                                            $barColor = $pct >= 90 ? '#DC2626' : '#031C5B';
                                        @endphp
                                        <div class="h-full rounded-full" style="width: {{ $pct }}%; background: {{ $barColor }};"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center text-[12px] font-bold px-3 py-1 rounded-full {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button class="text-[#031C5B] hover:text-blue-800 transition p-1.5 rounded-md hover:bg-blue-50">
                                    <i class="ph ph-magnifying-glass-plus text-[18px] font-bold"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-sm">Aucun campus enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 flex items-center justify-between bg-white border-t border-slate-200">
            <p class="text-[14px] text-slate-600 font-medium">
                {{ $allSchools->count() }} campus enregistrés au total
            </p>
        </div>
    </div>

    {{-- Campus Clusters by City --}}
    @if($networks->count())
    <div class="mb-8">
        <h3 class="text-[18px] font-extrabold text-slate-900 mb-5">Réseaux par Région</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($networks as $network)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                            <i class="ph ph-map-trifold text-[#031C5B] text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 text-[15px]">{{ $network['city'] }}</p>
                            <p class="text-[12px] text-slate-400">{{ $network['school_count'] }} campus</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-indigo-50 text-[#031C5B]">
                        {{ number_format($network['students'], 0, ',', ' ') }} élèves
                    </span>
                </div>
                <div class="space-y-2">
                    @foreach($network['schools']->take(3) as $s)
                        <div class="flex items-center gap-2 text-[13px] text-slate-600">
                            <i class="ph ph-building text-slate-400"></i>
                            <span class="truncate">{{ $s->name }}</span>
                            <span class="ml-auto shrink-0 font-bold text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $s->plan_name }}</span>
                        </div>
                    @endforeach
                    @if($network['school_count'] > 3)
                        <p class="text-[12px] text-slate-400 pl-5">+{{ $network['school_count'] - 3 }} autre(s)…</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Modal : Ajouter un Réseau -->
    <div id="addNetworkModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-tree-structure text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Ajouter un Réseau Régional</h3>
                        <p class="text-xs text-blue-200 font-medium">Regroupez plusieurs campus sous une même entité</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddNetworkModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('superadmin.multi-campus.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nom du Réseau *</label>
                    <input type="text" name="name" required placeholder="Ex: Réseau Écoles Excellence Sénégal" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Région Principale</label>
                    <input type="text" name="region" placeholder="Ex: Dakar & Thiès" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeAddNetworkModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                        Annuler
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#031C5B] text-white text-xs font-bold hover:bg-blue-900 transition shadow-sm flex items-center gap-2">
                        <i class="ph ph-check text-sm"></i> Créer le réseau
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Fullscreen Map Modal Overlay -->
    <div id="mapModal" class="hidden fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[99999] flex items-center justify-center p-3 sm:p-6 md:p-8">
        <div class="bg-white w-full max-w-7xl h-[88vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-slate-200 relative">
            
            <!-- Modal Header -->
            <div class="p-4 px-6 bg-[#031C5B] text-white flex items-center justify-between shrink-0 shadow-md">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-map-trifold text-xl text-blue-300"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white">Carte Interactive Multi-Campus</h3>
                        <p class="text-xs text-blue-200 font-medium">Afrique Francophone — {{ $networks->count() }} Régions | {{ $stats['total_campus'] }} Établissements</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <button type="button" onclick="recenterModalMap()" class="bg-white/10 hover:bg-white/20 text-white px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-white/20">
                        <i class="ph ph-crosshair text-sm"></i> Centrer
                    </button>
                    <button type="button" onclick="closeMapModal()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <i class="ph ph-x text-sm font-bold"></i> Fermer
                    </button>
                </div>
            </div>

            <!-- Modal Map Body -->
            <div id="modalCampusMap" class="w-full flex-1 bg-slate-100 z-0"></div>

            <!-- Modal Footer -->
            <div class="p-3 px-6 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs text-slate-600 font-medium shrink-0">
                <div class="flex items-center gap-4">
                    <span>⚡ Statut Réseau: <strong class="text-emerald-600">Opérationnel</strong></span>
                    <span>🎓 Total Élèves: <strong>{{ number_format($stats['total_students'], 0, ',', ' ') }}</strong></span>
                </div>
                <span class="text-slate-400">Appuyez sur <kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded text-[10px] font-mono shadow-xs">Échap</kbd> pour fermer</span>
            </div>
        </div>
    </div>

    <script>
        function openAddNetworkModal() {
            const modal = document.getElementById('addNetworkModal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeAddNetworkModal() {
            const modal = document.getElementById('addNetworkModal');
            if (modal) modal.classList.add('hidden');
        }

        function openMapModal() {
            const modal = document.getElementById('mapModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            
            setTimeout(() => {
                if (window.modalLeafletMap) {
                    window.modalLeafletMap.invalidateSize();
                    if (window.mapBounds && window.mapBounds.length > 0) {
                        window.modalLeafletMap.fitBounds(window.mapBounds, { padding: [50, 50], animate: true });
                    }
                }
            }, 150);
        }

        function closeMapModal() {
            const modal = document.getElementById('mapModal');
            if (modal) modal.classList.add('hidden');
        }

        function recenterMap() {
            if (window.leafletMap && window.mapBounds && window.mapBounds.length > 0) {
                window.leafletMap.invalidateSize();
                window.leafletMap.fitBounds(window.mapBounds, { padding: [50, 50], animate: true });
            }
        }

        function recenterModalMap() {
            if (window.modalLeafletMap && window.mapBounds && window.mapBounds.length > 0) {
                window.modalLeafletMap.invalidateSize();
                window.modalLeafletMap.fitBounds(window.mapBounds, { padding: [50, 50], animate: true });
            }
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeMapModal();
                closeAddNetworkModal();
            }
        });
    </script>
@endsection

@push('scripts')
<script>
    // Round pin marker (ball + stem, glossy highlight) — shared by the city-cluster
    // and individual-school markers on both the main and fullscreen-modal maps.
    // `badgeCount`, when set, draws a small count bubble on the ball (city clusters);
    // schools instead get a status dot baked into the ball color itself.
    // `showLabel` is off for schools — the name still shows in the click popup,
    // so a persistent label under every pin would just be visual clutter.
    function buildPinIconHtml(color, label, badgeCount, showLabel = true) {
        const ball = 26;
        const stem = 14;
        const totalH = ball + stem;
        const badgeHtml = badgeCount
            ? `<span style="position:absolute; top:-4px; right:-4px; background:#2563EB; color:white; border:2px solid white; border-radius:999px; min-width:16px; height:16px; padding:0 3px; font-size:9px; font-weight:800; display:flex; align-items:center; justify-content:center; line-height:1;">${badgeCount}</span>`
            : '';
        return `
            <div style="display:flex; flex-direction:column; align-items:center; cursor:pointer;">
                <div style="position:relative; filter: drop-shadow(0 2px 3px rgba(0,0,0,0.3));">
                    <svg width="${ball}" height="${totalH}" viewBox="0 0 ${ball} ${totalH}">
                        <rect x="${ball / 2 - 2}" y="${ball * 0.65}" width="4" height="${stem}" rx="2" fill="#1E293B"></rect>
                        <circle cx="${ball / 2}" cy="${ball / 2}" r="${ball / 2}" fill="${color}"></circle>
                        <circle cx="${ball * 0.62}" cy="${ball * 0.36}" r="${ball * 0.16}" fill="white" opacity="0.4"></circle>
                    </svg>
                    ${badgeHtml}
                </div>
                ${showLabel ? `<div style="margin-top:2px; background:white; border:1px solid #E2E8F0; padding:2px 8px; border-radius:8px; font-weight:700; font-size:11px; color:#1E293B; box-shadow:0 2px 6px rgba(0,0,0,0.15); white-space:nowrap; font-family:system-ui,sans-serif;">${label}</div>` : ''}
            </div>
        `;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const mapEl = document.getElementById('campusMap');
        if (mapEl && typeof L !== 'undefined') {
            const map = L.map('campusMap', {
                scrollWheelZoom: true,
                zoomControl: true
            }).setView([8.0, 0.0], 5);

            window.leafletMap = map;

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            const networks   = @json($networks);
            const allSchools = @json($allSchools);

            const cityLayer   = L.layerGroup().addTo(map);
            const schoolLayer = L.layerGroup();

            const bounds = [];

            // 1. Regional City Clusters (Macro View: Zoom < 8)
            networks.forEach(function (net) {
                if (net.lat && net.lng) {
                    bounds.push([net.lat, net.lng]);

                    const cityBadgeIcon = L.divIcon({
                        className: 'custom-city-badge',
                        html: buildPinIconHtml('#031C5B', net.city, net.school_count),
                        iconSize: [140, 64],
                        iconAnchor: [70, 40]
                    });

                    const marker = L.marker([net.lat, net.lng], { icon: cityBadgeIcon }).addTo(cityLayer);

                    marker.on('click', function () {
                        map.flyTo([net.lat, net.lng], 10, { duration: 1.2 });
                    });
                }
            });

            // 2. Individual School Locations (Micro View: Zoom >= 8)
            allSchools.forEach(function (sch) {
                if (sch.lat && sch.lng) {
                    bounds.push([sch.lat, sch.lng]);

                    const statusColor = sch.status === 'actif' ? '#059669' : '#DC2626';

                    const schoolIcon = L.divIcon({
                        className: 'custom-school-pin',
                        html: buildPinIconHtml(statusColor, sch.name, null, false),
                        iconSize: [140, 64],
                        iconAnchor: [70, 40]
                    });

                    const schoolMarker = L.marker([sch.lat, sch.lng], { icon: schoolIcon }).addTo(schoolLayer);

                    schoolMarker.bindPopup(`
                        <div style="font-family: system-ui, sans-serif; padding: 6px; min-width: 180px;">
                            <h4 style="margin: 0 0 4px 0; color: #031C5B; font-weight: 800; font-size: 14px;">🏫 ${sch.name}</h4>
                            <p style="margin: 0 0 4px 0; color: #64748B; font-size: 12px; font-weight: 600;">📍 ${sch.location || 'N/A'}</p>
                            <p style="margin: 0 0 4px 0; color: #059669; font-size: 12px; font-weight: 700;">🎓 ${sch.students_count || 0} Élèves Inscrits</p>
                            <span style="display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 800; text-transform: uppercase; background: #EEF2FF; color: #4F46E5;">
                                Forfait: ${sch.plan_name || 'Standard'}
                            </span>
                        </div>
                    `);
                }
            });

            window.mapBounds = bounds;

            // Adjust view zoom levels dynamically
            function updateMapZoomVisibility() {
                const currentZoom = map.getZoom();
                if (currentZoom >= 8) {
                    if (map.hasLayer(cityLayer)) map.removeLayer(cityLayer);
                    if (!map.hasLayer(schoolLayer)) map.addLayer(schoolLayer);
                } else {
                    if (!map.hasLayer(cityLayer)) map.addLayer(cityLayer);
                    if (map.hasLayer(schoolLayer)) map.removeLayer(schoolLayer);
                }
            }

            map.on('zoomend', updateMapZoomVisibility);

            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }

        // Initialize Modal Map
        const modalMapEl = document.getElementById('modalCampusMap');
        if (modalMapEl && typeof L !== 'undefined') {
            const modalMap = L.map('modalCampusMap', {
                scrollWheelZoom: true,
                zoomControl: true
            }).setView([8.0, 0.0], 5);

            window.modalLeafletMap = modalMap;

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap'
            }).addTo(modalMap);

            const networks   = @json($networks);
            const allSchools = @json($allSchools);

            const modalCityLayer   = L.layerGroup().addTo(modalMap);
            const modalSchoolLayer = L.layerGroup();

            networks.forEach(function (net) {
                if (net.lat && net.lng) {
                    const cityBadgeIcon = L.divIcon({
                        className: 'custom-city-badge-modal',
                        html: buildPinIconHtml('#031C5B', net.city, net.school_count),
                        iconSize: [140, 64],
                        iconAnchor: [70, 40]
                    });
                    const marker = L.marker([net.lat, net.lng], { icon: cityBadgeIcon }).addTo(modalCityLayer);
                    marker.on('click', function () {
                        modalMap.flyTo([net.lat, net.lng], 10, { duration: 1.2 });
                    });
                }
            });

            allSchools.forEach(function (sch) {
                if (sch.lat && sch.lng) {
                    const statusColor = sch.status === 'actif' ? '#059669' : '#DC2626';
                    const schoolIcon = L.divIcon({
                        className: 'custom-school-pin-modal',
                        html: buildPinIconHtml(statusColor, sch.name, null, false),
                        iconSize: [140, 64],
                        iconAnchor: [70, 40]
                    });
                    const schoolMarker = L.marker([sch.lat, sch.lng], { icon: schoolIcon }).addTo(modalSchoolLayer);
                    schoolMarker.bindPopup(`
                        <div style="font-family: system-ui, sans-serif; padding: 6px; min-width: 180px;">
                            <h4 style="margin: 0 0 4px 0; color: #031C5B; font-weight: 800; font-size: 14px;">🏫 ${sch.name}</h4>
                            <p style="margin: 0 0 4px 0; color: #64748B; font-size: 12px; font-weight: 600;">📍 ${sch.location || 'N/A'}</p>
                            <p style="margin: 0 0 4px 0; color: #059669; font-size: 12px; font-weight: 700;">🎓 ${sch.students_count || 0} Élèves Inscrits</p>
                            <span style="display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 800; text-transform: uppercase; background: #EEF2FF; color: #4F46E5;">
                                Forfait: ${sch.plan_name || 'Standard'}
                            </span>
                        </div>
                    `);
                }
            });

            modalMap.on('zoomend', function () {
                if (modalMap.getZoom() >= 8) {
                    if (modalMap.hasLayer(modalCityLayer)) modalMap.removeLayer(modalCityLayer);
                    if (!modalMap.hasLayer(modalSchoolLayer)) modalMap.addLayer(modalSchoolLayer);
                } else {
                    if (!modalMap.hasLayer(modalCityLayer)) modalMap.addLayer(modalCityLayer);
                    if (modalMap.hasLayer(modalSchoolLayer)) modalMap.removeLayer(modalSchoolLayer);
                }
            });
        }
    });
</script>
@endpush
