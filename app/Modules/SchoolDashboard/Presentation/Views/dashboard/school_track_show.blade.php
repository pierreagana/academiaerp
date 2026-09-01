@extends('SchoolDashboard::layouts.app')

@section('title', 'Profil School Track')

@section('content')
@php
    $currentRadar = is_array($school->academic_radar) ? $school->academic_radar : [];
    $currentLevels = is_array($school->levels) ? $school->levels : [];
    $nearbyPlaces = is_array($school->nearby_places) ? $school->nearby_places : [];
@endphp
<div class="space-y-6 max-w-5xl mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Profil School Track</h2>
            <p class="text-sm text-slate-500 mt-1">Ce que les parents voient dans l'outil de recherche/comparaison d'écoles de l'application mobile.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('school.dashboard') }}" class="px-4 py-2 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition">
                Retour
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 text-green-800 p-4 rounded-xl border border-green-200">{{ session('success') }}</div>
    @endif

    <div>
        @if($isComplete)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-700 text-xs font-bold rounded-full border border-green-200">
                <i class="ph ph-check-circle text-sm"></i> Profil complet — visible sur School Track
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-700 text-xs font-bold rounded-full border border-amber-200">
                <i class="ph ph-warning-circle text-sm"></i> Profil incomplet — non visible pour l'instant
            </span>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Photo & identité -->
        <div class="md:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 text-center">
            @if($school->logo_url)
                <img src="{{ $school->logo_url }}" alt="Logo" class="w-32 h-32 rounded-xl object-cover mb-4 border border-slate-100 shadow-sm mx-auto">
            @else
                <div class="w-32 h-32 rounded-xl bg-slate-100 flex items-center justify-center text-4xl font-bold text-slate-300 mb-4 mx-auto">
                    {{ strtoupper(substr($school->name, 0, 1)) }}
                </div>
            @endif
            <h3 class="text-lg font-bold text-slate-800">{{ $school->name }}</h3>
            @if(!empty($currentLevels))
                <div class="flex flex-wrap gap-1 justify-center mt-3">
                    @foreach($currentLevels as $lvl)
                        <span class="px-2 py-0.5 bg-blue-50 text-primary-dynamic rounded text-[11px] font-bold">{{ $lvl }}</span>
                    @endforeach
                </div>
            @endif
            @if(!empty($school->tags))
                <div class="flex flex-wrap gap-1 justify-center mt-2">
                    @foreach($school->tags as $tag)
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[11px]">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Description -->
        <div class="md:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Description</label>
            <p class="text-sm text-slate-700 leading-relaxed">{{ $school->description ?: 'Non renseignée.' }}</p>
        </div>
    </div>

    <!-- Rendement & données -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h4 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Rendement & Données</h4>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            @forelse($availableExamTypes as $type)
                <div class="bg-slate-50 rounded-xl p-3 text-center">
                    <p class="text-[11px] font-bold text-slate-500 uppercase">{{ strtoupper($type) }}</p>
                    <p class="text-xl font-bold {{ $examSuccessRates[$type] !== null ? 'text-slate-800' : 'text-slate-300' }} mt-1">
                        {{ $examSuccessRates[$type] !== null ? $examSuccessRates[$type] . '%' : '—' }}
                    </p>
                </div>
            @empty
                <div class="col-span-2 md:col-span-3">
                    <p class="text-xs text-slate-400 italic">Aucune classe d'examen (Terminale/3ème/CM2/BTS) enregistrée.</p>
                </div>
            @endforelse

            <div class="bg-slate-50 rounded-xl p-3 text-center">
                <p class="text-[11px] font-bold text-slate-500 uppercase">Progression</p>
                <p class="text-xl font-bold {{ $progressionAnnuelle !== null ? ($progressionAnnuelle >= 0 ? 'text-emerald-600' : 'text-red-600') : 'text-slate-300' }} mt-1">
                    @if($progressionAnnuelle === null)
                        —
                    @else
                        {{ $progressionAnnuelle > 0 ? '+' : '' }}{{ $progressionAnnuelle }}%
                    @endif
                </p>
            </div>

            <div class="bg-slate-50 rounded-xl p-3 text-center">
                <p class="text-[11px] font-bold text-slate-500 uppercase">Ratio Prof/Élèves</p>
                <p class="text-xl font-bold text-slate-800 mt-1">{{ $school->teacherStudentRatioLabel() ?? '—' }}</p>
            </div>
        </div>
        <p class="text-[11px] text-slate-400 mt-3">
            Taux de réussite et progression calculés depuis <a href="{{ route('school.exam-results.index') }}" class="underline font-semibold">Résultats aux examens</a> et les promotions de classe — jamais saisis à la main.
        </p>
    </div>

    <!-- Équipements (liste réelle de l'établissement — même donnée que l'app mobile) -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-2">
            <h4 class="text-lg font-bold text-slate-800">Équipements</h4>
            <span class="text-xs text-slate-500 font-semibold">{{ collect($facilities)->where('is_available', true)->count() }} équipement(s) déclaré(s)</span>
        </div>
        @if(collect($facilities)->where('is_available', true)->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach($facilities as $facility)
                    @if($facility['is_available'])
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i class="ph {{ $facility['icon'] ?? 'ph-check-circle' }}"></i> {{ $facility['name'] }}
                        </span>
                    @endif
                @endforeach
            </div>
        @else
            <div class="p-4 bg-slate-50 border border-dashed border-slate-200 rounded-xl text-center">
                <p class="text-xs text-slate-500 font-medium">Aucun équipement déclaré pour le moment.</p>
                <a href="{{ route('school.school-track.edit') }}" class="text-xs font-bold text-primary-dynamic mt-1 inline-block hover:underline">
                    + Déclarer vos équipements
                </a>
            </div>
        @endif
    </div>

    <!-- Radar académique -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h4 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Radar académique</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach(\App\Modules\SuperAdmin\Domain\Models\School::ACADEMIC_RADAR_KEYS as $key)
                <div class="text-center">
                    <p class="text-xs font-semibold text-slate-500 mb-1">{{ $key }}</p>
                    <p class="text-xl font-bold text-slate-800">{{ isset($currentRadar[$key]) ? $currentRadar[$key] . '/100' : '—' }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Lieux à proximité -->
    @if(!empty($nearbyPlaces))
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h4 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Lieux à proximité</h4>
            <div class="flex flex-wrap gap-2">
                @foreach($nearbyPlaces as $place)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-full text-xs font-semibold text-slate-700">
                        {{ $place['emoji'] ?? '📍' }} {{ $place['label'] ?? '' }}
                        @if(!empty($place['distance'])) <span class="text-slate-400">· {{ $place['distance'] }}</span> @endif
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Galerie -->
    @if(!empty($school->gallery_paths))
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h4 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Galerie photo</h4>
            <div class="grid grid-cols-3 md:grid-cols-5 gap-3">
                @foreach($school->gallery_paths as $path)
                    <img src="{{ Storage::url($path) }}" class="w-full h-24 object-cover rounded-xl border border-slate-200">
                @endforeach
            </div>
        </div>
    @endif

    <!-- Catalogue de l'établissement (défini dans Mon Établissement) -->
    @if(!empty($school->catalog_paths))
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-2">
                <h4 class="text-lg font-bold text-slate-800">Catalogue de l'établissement</h4>
                <span class="text-xs text-slate-500 font-semibold">{{ count($school->catalog_paths) }}/{{ \App\Modules\SuperAdmin\Domain\Models\School::CATALOG_MAX_PHOTOS }}</span>
            </div>
            <div class="grid grid-cols-3 md:grid-cols-5 gap-3">
                @foreach($school->catalog_paths as $path)
                    <img src="{{ Storage::url($path) }}" class="w-full h-24 object-cover rounded-xl border border-slate-200">
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
