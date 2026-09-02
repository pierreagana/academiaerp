@extends('ParentPortal::layout')

@section('title', 'School Track – Comparateur d\'Écoles')

@section('content')

<!-- HEADER MATCHING SCREENSHOT 2 -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('parent.school-track.index') }}" class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1">
                <span class="material-symbols-outlined text-[15px]">arrow_back</span>
                <span>Retour aux écoles</span>
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-xs font-bold text-slate-400">Comparateur</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Comparateur d'Écoles</h1>
        <p class="text-sm font-medium text-slate-500 mt-1">
            Évaluez vos établissements sélectionnés côte à côte selon vos critères prioritaires.
        </p>
    </div>

    <!-- ADD SCHOOL BUTTON / MODAL TRIGGER -->
    <div class="flex items-center gap-2" x-data="{ open: false }">
        <button type="button" @click="open = true" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-[#061536] hover:bg-slate-50 font-bold text-xs shadow-xs transition">
            <span class="material-symbols-outlined text-[17px] text-blue-600">add</span>
            <span>Ajouter une école</span>
        </button>

        <a href="{{ route('parent.school-track.map') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#061536] text-white hover:bg-[#061536]/90 font-bold text-xs shadow-sm transition">
            <span class="material-symbols-outlined text-[17px] text-blue-300">map</span>
            <span>Vue Carte</span>
        </a>

        <!-- Add School Modal -->
        <div x-show="open" style="display:none;" 
             class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-4 text-slate-800" 
             x-on:keydown.escape.window="open = false">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl" x-on:click.outside="open = false">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-extrabold text-slate-900">Ajouter à la comparaison</h3>
                    <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mb-4">Sélectionnez un établissement pour l'ajouter à votre tableau comparatif.</p>

                <div class="space-y-2 max-h-72 overflow-y-auto">
                    @foreach($allSchools as $s)
                    <form action="{{ route('parent.school-track.compare.toggle') }}" method="POST">
                        @csrf
                        <input type="hidden" name="school_id" value="{{ $s->id }}">
                        <button type="submit" 
                                class="w-full flex items-center justify-between p-3 rounded-2xl border border-slate-100 hover:border-blue-500 hover:bg-blue-50/40 text-left transition">
                            <span class="text-xs font-bold text-slate-800">{{ $s->name }}</span>
                            <span class="material-symbols-outlined text-[18px] text-blue-600">add_circle</span>
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@php
    // Real comparison winner: highest actual performanceScore among the
    // compared schools (blended promotion/exam-admission rate) — not a
    // fixed card position. No school is marked "best" when none has any
    // validated performance data yet.
    $bestScore = $schools->max('performanceScore');
    $isBest = fn ($s) => $bestScore !== null && $s['performanceScore'] === $bestScore;
@endphp

@if($schools->isEmpty())
<div class="bg-white rounded-3xl border border-slate-200 p-12 text-center">
    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">compare_arrows</span>
    <h3 class="text-base font-extrabold text-slate-800 mb-1">Aucune école sélectionnée</h3>
    <p class="text-xs text-slate-500 max-w-sm mx-auto mb-5">Ajoutez au moins deux écoles depuis le catalogue pour les comparer côte à côte.</p>
    <a href="{{ route('parent.school-track.index') }}" 
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#061536] text-white text-xs font-bold transition">
        Découvrir les écoles
    </a>
</div>
@else

<!-- SIDE-BY-SIDE COMPARISON TABLE MATCHING SCREENSHOT 2 -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-x-auto">
    <table class="w-full text-left border-collapse min-w-[700px]">
        
        <!-- HEADER ROW: SCHOOL CARDS -->
        <thead>
            <tr class="border-b border-slate-100">
                <th class="p-6 w-1/4 align-bottom bg-slate-50/50">
                    <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-widest">Critères d'analyse</p>
                    <p class="text-xs font-bold text-slate-600 mt-1">{{ $schools->count() }} établissements comparés</p>
                </th>
                @foreach($schools as $index => $s)
                @php
                    $isBestMatch = $isBest($s);
                @endphp
                <th class="p-6 w-1/4 align-top text-center relative {{ $isBestMatch ? 'bg-blue-50/30 border-x-2 border-blue-500/40' : '' }}">
                    @if($isBestMatch)
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-[#061536] text-white shadow-md">
                            ★ Recommandation IA
                        </span>
                    </div>
                    @endif

                    <!-- School Avatar / Logo -->
                    <div class="w-20 h-20 rounded-2xl overflow-hidden mx-auto mb-3 shadow-md border border-slate-100 bg-slate-100">
                        <img src="{{ $s['photo'] }}" alt="{{ $s['name'] }}" class="w-full h-full object-cover">
                    </div>

                    <h3 class="text-sm font-black text-slate-900 leading-tight mb-1 truncate">
                        {{ $s['name'] }}
                    </h3>
                    <p class="text-[11.5px] font-semibold text-slate-400 truncate">
                        {{ $s['location'] ?? 'Localisation non renseignée' }}
                    </p>

                    <!-- Remove from comparison -->
                    <form action="{{ route('parent.school-track.compare.toggle') }}" method="POST" class="mt-2">
                        @csrf
                        <input type="hidden" name="school_id" value="{{ $s['model_id'] }}">
                        <button type="submit" class="text-[10.5px] font-bold text-slate-400 hover:text-rose-600 transition flex items-center justify-center gap-1 mx-auto">
                            <span class="material-symbols-outlined text-[13px]">close</span>
                            <span>Retirer</span>
                        </button>
                    </form>
                </th>
                @endforeach
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-100 text-xs">
            
            <!-- ROW 1: ANNUAL TUITION -->
            <tr>
                <td class="p-5 font-black text-slate-800 bg-slate-50/40">
                    <span class="text-slate-900 block text-xs">Frais de Scolarité Annuels</span>
                    <span class="text-[11px] text-slate-400 font-medium">Moyenne annuelle estimée</span>
                </td>
                @foreach($schools as $index => $s)
                @php $isBestMatch = $isBest($s); @endphp
                <td class="p-5 text-center font-black text-base text-slate-900 {{ $isBestMatch ? 'bg-blue-50/20 border-x-2 border-blue-500/40 text-blue-700' : '' }}">
                    {{ $s['frais_formatted'] }}
                </td>
                @endforeach
            </tr>

            <!-- ROW 2: ACADEMIC PERFORMANCE — real, blended promotion/exam-admission score -->
            <tr>
                <td class="p-5 font-black text-slate-800 bg-slate-50/40">
                    <span class="text-slate-900 block text-xs">Performance Académique</span>
                    <span class="text-[11px] text-slate-400 font-medium">Score réel (promotions & examens validés)</span>
                </td>
                @foreach($schools as $index => $s)
                @php $isBestMatch = $isBest($s); @endphp
                <td class="p-5 text-center {{ $isBestMatch ? 'bg-blue-50/20 border-x-2 border-blue-500/40' : '' }}">
                    @if($s['performanceScore'] !== null)
                        <div class="text-xl font-black text-slate-900 mb-1">
                            {{ round($s['performanceScore']) }}%
                        </div>
                        @if($s['progressionAnnuelle'] !== null)
                        <p class="text-[11px] font-bold {{ $s['progressionAnnuelle'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $s['progressionAnnuelle'] >= 0 ? '+' : '' }}{{ $s['progressionAnnuelle'] }} pts / an
                        </p>
                        @endif
                    @else
                        <p class="text-[11px] text-slate-400 font-medium">Pas encore de données validées</p>
                    @endif
                </td>
                @endforeach
            </tr>

            <!-- ROW 3: INFRASTRUCTURE (Checklist matching Screenshot 2) -->
            <tr>
                <td class="p-5 font-black text-slate-800 bg-slate-50/40">
                    <span class="text-slate-900 block text-xs">Infrastructures & Équipements</span>
                    <span class="text-[11px] text-slate-400 font-medium">Installations vérifiées sur site</span>
                </td>
                @foreach($schools as $index => $s)
                @php
                    $isBestMatch = $isBest($s);
                    $fac = $s['facilities'] ?? [];
                    $hasPool = !empty($fac['piscine']);
                    $hasSmart = !empty($fac['informatique']);
                    $hasLab = !empty($fac['laboratoire']);
                    $hasSports = !empty($fac['sport']);
                @endphp
                <td class="p-5 {{ $isBestMatch ? 'bg-blue-50/20 border-x-2 border-blue-500/40' : '' }}">
                    <div class="space-y-2 text-[11.5px] font-semibold max-w-[190px] mx-auto">

                        <!-- Piscine -->
                        <div class="flex items-center gap-2 {{ $hasPool ? 'text-blue-700' : 'text-slate-400' }}">
                            <span class="material-symbols-outlined text-[16px] {{ $hasPool ? 'text-blue-600' : 'text-slate-300' }}">
                                {{ $hasPool ? 'check_circle' : 'cancel' }}
                            </span>
                            <span>Piscine</span>
                        </div>

                        <!-- Salle Informatique -->
                        <div class="flex items-center gap-2 {{ $hasSmart ? 'text-blue-700' : 'text-slate-400' }}">
                            <span class="material-symbols-outlined text-[16px] {{ $hasSmart ? 'text-blue-600' : 'text-slate-300' }}">
                                {{ $hasSmart ? 'check_circle' : 'cancel' }}
                            </span>
                            <span>Salle Informatique</span>
                        </div>

                        <!-- Laboratoire -->
                        <div class="flex items-center gap-2 {{ $hasLab ? 'text-blue-700' : 'text-slate-400' }}">
                            <span class="material-symbols-outlined text-[16px] {{ $hasLab ? 'text-blue-600' : 'text-slate-300' }}">
                                {{ $hasLab ? 'check_circle' : 'cancel' }}
                            </span>
                            <span>Laboratoire de Sciences</span>
                        </div>

                        <!-- Installations sportives -->
                        <div class="flex items-center gap-2 {{ $hasSports ? 'text-blue-700' : 'text-slate-400' }}">
                            <span class="material-symbols-outlined text-[16px] {{ $hasSports ? 'text-blue-600' : 'text-slate-300' }}">
                                {{ $hasSports ? 'check_circle' : 'cancel' }}
                            </span>
                            <span>Installations Sportives</span>
                        </div>

                    </div>
                </td>
                @endforeach
            </tr>

            <!-- ROW 4: DISTANCE (from your child's current school, when known) -->
            <tr>
                <td class="p-5 font-black text-slate-800 bg-slate-50/40">
                    <span class="text-slate-900 block text-xs">Distance</span>
                    <span class="text-[11px] text-slate-400 font-medium">Depuis {{ $distanceLabel ?? 'un point de référence' }}</span>
                </td>
                @foreach($schools as $index => $s)
                @php $isBestMatch = $isBest($s); @endphp
                <td class="p-5 text-center {{ $isBestMatch ? 'bg-blue-50/20 border-x-2 border-blue-500/40' : '' }}">
                    <p class="text-base font-black text-slate-900">{{ $s['distance_formatted'] ?? 'Non renseignée' }}</p>
                </td>
                @endforeach
            </tr>

            <!-- ROW 5: ACTION BUTTONS -->
            <tr>
                <td class="p-5 bg-slate-50/40"></td>
                @foreach($schools as $index => $s)
                @php $isBestMatch = $isBest($s); @endphp
                <td class="p-5 text-center {{ $isBestMatch ? 'bg-blue-50/20 border-x-2 border-blue-500/40' : '' }}">
                    <a href="{{ route('parent.school-track.show', $s['model_id']) }}" 
                       class="inline-flex items-center justify-center gap-1.5 w-full py-2.5 px-4 rounded-xl font-bold text-xs shadow-sm transition
                              {{ $isBestMatch 
                                 ? 'bg-[#061536] hover:bg-[#061536]/90 text-white shadow-md' 
                                 : 'bg-slate-100 hover:bg-slate-200 text-slate-800' }}">
                        <span>Fiche complète</span>
                        <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                    </a>
                </td>
                @endforeach
            </tr>

        </tbody>
    </table>
</div>

@endif

@endsection
