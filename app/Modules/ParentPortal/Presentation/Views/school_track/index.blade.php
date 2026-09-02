@extends('ParentPortal::layout')

@section('title', 'School Track – Découvrez les écoles')

@push('styles')
<style>
    .school-card {
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .school-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px -8px rgba(6, 21, 54, 0.12);
    }
</style>
@endpush

@section('content')

<!-- HEADER & SUBTITLE MATCHING SCREENSHOT 1 -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-extrabold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200/60">
                School Track IA
            </span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Découvrez les écoles</h1>
        <p class="text-sm font-medium text-slate-500 mt-1">
            Explorez les établissements adaptés aux besoins de vos élèves.
        </p>
    </div>

    <!-- ACTION BUTTONS -->
    <div class="flex items-center gap-2.5 flex-wrap">
        <a href="{{ route('parent.school-track.map') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition">
            <span class="material-symbols-outlined text-[17px] text-blue-600">map</span>
            <span>Vue Carte</span>
        </a>

        <a href="{{ route('parent.school-track.compare') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs shadow-md shadow-blue-950/20 transition">
            <span class="material-symbols-outlined text-[17px] text-blue-300">compare_arrows</span>
            <span>Comparateur</span>
            @if(count($comparisonIds) > 0)
                <span class="bg-blue-500 text-white text-[10px] font-black px-1.5 py-0.2 rounded-full">
                    {{ count($comparisonIds) }}
                </span>
            @endif
        </a>
    </div>
</div>

<!-- SEARCH & FILTERS TOOLBAR -->
<div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm mb-7">
    <form method="GET" action="{{ route('parent.school-track.index') }}" class="space-y-3">
        
        <!-- Search input + Submit -->
        <div class="flex flex-col sm:flex-row gap-2.5">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-slate-400">
                    <span class="material-symbols-outlined text-[20px]">search</span>
                </span>
                <input type="text" name="q" value="{{ $query }}" 
                       placeholder="Rechercher par nom, quartier (Plateau, Almadies, Mermoz...), ou spécialité..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 outline-none transition">
            </div>

            <button type="submit" 
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#061536] hover:bg-[#061536]/90 text-white text-xs font-bold transition shrink-0">
                <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                <span>Filtrer</span>
            </button>

            @if(!empty($query) || !empty($level) || !empty($maxPrice) || !empty($minRating) || !empty($facility))
            <a href="{{ route('parent.school-track.index') }}" 
               class="inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-500 text-xs font-bold transition shrink-0">
                <span>Réinitialiser</span>
            </a>
            @endif
        </div>

        <!-- Filter Dropdowns Row -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-1 text-xs font-medium">
            <!-- Cycle / Niveau -->
            <div>
                <select name="level" onchange="this.form.submit()" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500">
                    <option value="all" {{ empty($level) || $level === 'all' ? 'selected' : '' }}>Tous les cycles</option>
                    <option value="Maternelle" {{ $level === 'Maternelle' ? 'selected' : '' }}>Maternelle</option>
                    <option value="Primaire" {{ $level === 'Primaire' ? 'selected' : '' }}>Primaire</option>
                    <option value="Collège" {{ $level === 'Collège' ? 'selected' : '' }}>Collège</option>
                    <option value="Lycée" {{ $level === 'Lycée' ? 'selected' : '' }}>Lycée</option>
                </select>
            </div>

            <!-- Score de performance minimal (réel, calculé à partir des taux de réussite/promotion) -->
            <div>
                <select name="min_rating" onchange="this.form.submit()"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500">
                    <option value="" {{ empty($minRating) ? 'selected' : '' }}>Tous les scores</option>
                    <option value="80" {{ $minRating == '80' ? 'selected' : '' }}>Score 80% et +</option>
                    <option value="60" {{ $minRating == '60' ? 'selected' : '' }}>Score 60% et +</option>
                    <option value="40" {{ $minRating == '40' ? 'selected' : '' }}>Score 40% et +</option>
                </select>
            </div>

            <!-- Équipement clé -->
            <div>
                <select name="facility" onchange="this.form.submit()" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500">
                    <option value="" {{ empty($facility) ? 'selected' : '' }}>Tous équipements</option>
                    <option value="laboratoire" {{ $facility === 'laboratoire' ? 'selected' : '' }}>Laboratoires STEM</option>
                    <option value="informatique" {{ $facility === 'informatique' ? 'selected' : '' }}>Salles Informatique</option>
                    <option value="piscine" {{ $facility === 'piscine' ? 'selected' : '' }}>Piscine Olympique</option>
                    <option value="cantine" {{ $facility === 'cantine' ? 'selected' : '' }}>Cantine scolaire</option>
                    <option value="transport" {{ $facility === 'transport' ? 'selected' : '' }}>Transport / Bus</option>
                </select>
            </div>

            <!-- Tri -->
            <div>
                <select name="sort_by" onchange="this.form.submit()" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500">
                    <option value="rating" {{ empty($sortBy) || $sortBy === 'rating' ? 'selected' : '' }}>Mieux notés</option>
                    <option value="proximite" {{ $sortBy === 'proximite' ? 'selected' : '' }}>Plus proches</option>
                    <option value="frais_asc" {{ $sortBy === 'frais_asc' ? 'selected' : '' }}>Frais : Moins chers</option>
                    <option value="frais_desc" {{ $sortBy === 'frais_desc' ? 'selected' : '' }}>Frais : Élevés</option>
                </select>
            </div>
        </div>
    </form>
</div>

<!-- SCHOOLS CARDS GRID MATCHING SCREENSHOT 1 -->
@if($schools->isEmpty())
<div class="bg-white rounded-3xl border border-slate-200 p-12 text-center">
    <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
        <span class="material-symbols-outlined text-3xl">school</span>
    </div>
    <h3 class="text-base font-extrabold text-slate-900 mb-1">Aucun établissement correspondant</h3>
    <p class="text-xs text-slate-500 max-w-sm mx-auto mb-5">Modifiez vos critères de recherche ou réinitialisez les filtres pour découvrir d'autres écoles.</p>
    <a href="{{ route('parent.school-track.index') }}" 
       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#061536] text-white text-xs font-bold transition">
        Réinitialiser les filtres
    </a>
</div>
@else

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($schools as $sch)
    @php
        $isCompared = in_array($sch['model_id'], $comparisonIds);
    @endphp
    <div class="school-card bg-white rounded-3xl border border-slate-200/80 overflow-hidden flex flex-col justify-between shadow-sm">
        
        <!-- CARD TOP: IMAGE + BADGES -->
        <div>
            <div class="relative h-48 w-full bg-slate-100 overflow-hidden">
                <img src="{{ $sch['photo'] }}" alt="{{ $sch['name'] }}" class="w-full h-full object-cover">
                
                <!-- Gradient overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/40 via-transparent to-black/30"></div>

                <!-- Top Left Badge: real school level/type, e.g. "Collège · Lycée" -->
                @if($sch['level'])
                <div class="absolute top-3 left-3">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-[#061536]/80 text-white backdrop-blur-md border border-white/20 shadow-sm">
                        <span class="material-symbols-outlined text-[13px] text-blue-300">verified</span>
                        <span>{{ $sch['level'] }}</span>
                    </span>
                </div>
                @endif

                <!-- Bottom Image Bar: Tuition chip -->
                <div class="absolute bottom-3 left-3">
                    <span class="px-2.5 py-1 rounded-xl text-[11px] font-black bg-white/95 text-slate-900 shadow-sm backdrop-blur-xs">
                        {{ $sch['frais_formatted'] }}
                    </span>
                </div>
            </div>

            <!-- CARD BODY -->
            <div class="p-5">
                
                <!-- Title + Rating pill -->
                <div class="flex items-start justify-between gap-2 mb-2">
                    <h2 class="text-base font-extrabold text-slate-900 leading-snug tracking-tight line-clamp-1 hover:text-blue-600 transition">
                        <a href="{{ route('parent.school-track.show', $sch['model_id']) }}">
                            {{ $sch['name'] }}
                        </a>
                    </h2>
                    
                    <!-- Performance score: real, from validated exam/promotion data -->
                    @if($sch['performanceScore'] !== null)
                    <div class="flex items-center gap-1 bg-amber-50 border border-amber-200/80 px-2 py-0.5 rounded-lg shrink-0">
                        <span class="material-symbols-outlined text-[14px] text-amber-500">trending_up</span>
                        <span class="text-[12px] font-black text-amber-900">{{ round($sch['performanceScore']) }}%</span>
                    </div>
                    @endif
                </div>

                <!-- Location line: e.g. "📍 Plateau, Dakar" -->
                <p class="text-[12px] font-semibold text-slate-500 flex items-center gap-1.5 mb-1.5 truncate">
                    <span class="material-symbols-outlined text-[16px] text-slate-400 shrink-0">location_on</span>
                    <span>{{ $sch['location'] ?? 'Localisation non renseignée' }}</span>
                </p>

                <!-- Distance line: only shown when a real reference point + geocode are both available -->
                @if($sch['distance_formatted'])
                <p class="text-[11.5px] font-medium text-slate-400 flex items-center gap-1.5 mb-3">
                    <span class="material-symbols-outlined text-[15px] text-blue-500 shrink-0">near_me</span>
                    <span>{{ $sch['distance_formatted'] }} de {{ $distanceLabel }}</span>
                </p>
                @endif

                <!-- Tags list -->
                @if(!empty($sch['tags']))
                <div class="flex flex-wrap gap-1.5 mb-2">
                    @foreach(array_slice($sch['tags'], 0, 3) as $tag)
                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600 font-semibold text-[10.5px]">
                        {{ $tag }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- CARD BOTTOM ACTIONS MATCHING SCREENSHOT 1: "Comparer" + "Détails" -->
        <div class="px-5 pb-5 pt-2 border-t border-slate-100 flex items-center gap-2.5">
            
            <!-- Compare toggle button -->
            <form action="{{ route('parent.school-track.compare.toggle') }}" method="POST" class="flex-1">
                @csrf
                <input type="hidden" name="school_id" value="{{ $sch['model_id'] }}">
                <button type="submit" 
                        class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl border text-xs font-bold transition
                               {{ $isCompared 
                                  ? 'bg-blue-50 border-blue-300 text-blue-700' 
                                  : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-700' }}">
                    <span class="material-symbols-outlined text-[16px]">
                        {{ $isCompared ? 'check_circle' : 'compare_arrows' }}
                    </span>
                    <span>{{ $isCompared ? 'Sélectionné' : 'Comparer' }}</span>
                </button>
            </form>

            <!-- Details button (Dark navy) -->
            <a href="{{ route('parent.school-track.show', $sch['model_id']) }}" 
               class="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-[#061536] hover:bg-[#061536]/90 text-white text-xs font-extrabold shadow-sm transition">
                <span>Détails</span>
                <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
            </a>
        </div>

    </div>
    @endforeach
</div>

@endif

<!-- FLOATING COMPARISON BAR (Appears when schools are selected) -->
@if(count($comparisonIds) > 0)
<div class="fixed bottom-6 right-6 z-40 bg-[#061536] text-white rounded-2xl shadow-2xl p-4 flex items-center gap-4 border border-white/10 animate-bounce-short">
    <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-blue-500 text-white flex items-center justify-center font-black text-sm">
            {{ count($comparisonIds) }}
        </div>
        <div>
            <p class="text-xs font-extrabold text-white">Écoles sélectionnées</p>
            <p class="text-[11px] text-blue-200">Prêtes pour la comparaison côte à côte</p>
        </div>
    </div>

    <a href="{{ route('parent.school-track.compare') }}" 
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white hover:bg-blue-50 text-[#061536] font-extrabold text-xs shadow transition">
        <span>Ouvrir le comparateur</span>
        <span class="material-symbols-outlined text-[16px]">launch</span>
    </a>
</div>
@endif

@endsection
