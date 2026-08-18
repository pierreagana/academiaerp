@extends('SchoolDashboard::layouts.app')

@section('title', 'Profil School Track')

@section('content')
@php
    $facilityLabels = [
        'wifi' => 'Wifi',
        'energie_solaire' => 'Énergie solaire',
        'laboratoire' => 'Laboratoire',
        'informatique' => 'Salle informatique',
        'piscine' => 'Piscine',
        'internat' => 'Internat',
        'sport' => 'Terrain de sport',
        'cantine' => 'Cantine',
    ];
    $currentFacilities = $school->facilities ?? [];
    $currentRadar = $school->academic_radar ?? [];
    $currentLevels = $school->levels ?? [];
    $isComplete = $school->isSchoolTrackProfileComplete();
@endphp
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Profil School Track</h2>
            <p class="text-sm text-slate-500 mt-1">Ce profil est ce que les parents voient dans l'outil de recherche/comparaison d'écoles de l'application mobile.</p>
        </div>
        @if($isComplete)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-700 text-xs font-bold rounded-full border border-green-200">
                <i class="ph ph-check-circle text-sm"></i> Profil complet — visible sur School Track
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-700 text-xs font-bold rounded-full border border-amber-200">
                <i class="ph ph-warning-circle text-sm"></i> Profil incomplet — non visible
            </span>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-50 text-green-800 p-4 rounded-xl border border-green-200">{{ session('success') }}</div>
    @endif

    @if(!$isComplete)
        <div class="bg-amber-50 text-amber-800 p-4 rounded-xl border border-amber-200 text-sm">
            Tant que la description, les niveaux, au moins un équipement, au moins une photo, le taux de réussite et les 4 scores du radar académique ne sont pas renseignés, votre établissement n'apparaît pas dans les résultats School Track des parents.
        </div>
    @endif

    <form action="{{ route('school.school-track.update') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-8">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Description</label>
            <textarea name="description" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">{{ old('description', $school->description) }}</textarea>
            @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Niveaux enseignés</label>
            <div class="flex flex-wrap gap-3">
                @foreach(\App\Modules\SuperAdmin\Domain\Models\School::SCHOOL_TRACK_LEVELS as $level)
                    <label class="inline-flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer has-[:checked]:bg-blue-50 has-[:checked]:border-blue-400">
                        <input type="checkbox" name="levels[]" value="{{ $level }}" {{ in_array($level, $currentLevels) ? 'checked' : '' }} class="rounded text-blue-600">
                        <span class="text-sm font-medium text-slate-700">{{ $level }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Tags (séparés par une virgule)</label>
            <input type="text" name="tags" value="{{ old('tags', implode(', ', $school->tags ?? [])) }}" placeholder="Bilingue, Sport-Études, Confessionnel..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Équipements</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($facilityLabels as $key => $label)
                    <label class="inline-flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer has-[:checked]:bg-blue-50 has-[:checked]:border-blue-400">
                        <input type="checkbox" name="facilities[]" value="{{ $key }}" {{ !empty($currentFacilities[$key]) ? 'checked' : '' }} class="rounded text-blue-600">
                        <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Taux de réussite (%)</label>
                <input type="number" name="success_rate" min="0" max="100" value="{{ old('success_rate', $school->success_rate) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                @error('success_rate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Radar académique (auto-évaluation, /100 par domaine)</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach(\App\Modules\SuperAdmin\Domain\Models\School::ACADEMIC_RADAR_KEYS as $key)
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">{{ $key }}</label>
                        <input type="number" name="academic_radar[{{ $key }}]" min="0" max="100" value="{{ old('academic_radar.' . $key, $currentRadar[$key] ?? '') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 outline-none focus:border-blue-600 focus:bg-white transition">
                    </div>
                @endforeach
            </div>
        </div>

        <div x-data="{
            places: {{ Illuminate\Support\Js::from(count($school->nearby_places ?? []) ? $school->nearby_places : [['emoji' => '📍', 'label' => '', 'distance' => '']]) }}
        }">
            <label class="block text-sm font-bold text-slate-700 mb-2">Lieux à proximité</label>
            <div class="space-y-2">
                <template x-for="(place, index) in places" :key="index">
                    <div class="flex items-center gap-2">
                        <input type="text" :name="'nearby_places[' + index + '][emoji]'" x-model="place.emoji" placeholder="🏥" class="w-16 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 outline-none focus:border-blue-600 focus:bg-white transition text-center">
                        <input type="text" :name="'nearby_places[' + index + '][label]'" x-model="place.label" placeholder="Hôpital régional" class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 outline-none focus:border-blue-600 focus:bg-white transition">
                        <input type="text" :name="'nearby_places[' + index + '][distance]'" x-model="place.distance" placeholder="500m" class="w-28 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 outline-none focus:border-blue-600 focus:bg-white transition">
                        <button type="button" @click="places.splice(index, 1)" class="text-red-500 hover:text-red-700 px-2">
                            <i class="ph ph-trash"></i>
                        </button>
                    </div>
                </template>
                <button type="button" @click="places.push({emoji: '📍', label: '', distance: ''})" class="text-sm font-semibold text-blue-600 hover:text-blue-800 mt-1">
                    + Ajouter un lieu
                </button>
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Galerie photo</label>
            @if(!empty($school->gallery_paths))
                <div class="grid grid-cols-3 md:grid-cols-5 gap-3 mb-3">
                    @foreach($school->gallery_paths as $path)
                        <div class="relative group">
                            <img src="{{ Storage::url($path) }}" class="w-full h-24 object-cover rounded-xl border border-slate-200">
                            <label class="absolute top-1 right-1 bg-white/90 rounded-full p-1 cursor-pointer">
                                <input type="checkbox" name="remove_gallery[]" value="{{ $path }}" class="align-middle">
                            </label>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-slate-500 mb-2">Cochez une photo pour la supprimer à l'enregistrement.</p>
            @endif
            <input type="file" name="gallery[]" accept="image/*" multiple class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-[9px] outline-none focus:border-blue-600 focus:bg-white transition file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:bg-slate-200 file:text-slate-700">
            @error('gallery.*') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        <div class="pt-6 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-dynamic text-white font-bold hover:opacity-95 transition shadow-sm flex items-center gap-2">
                <i class="ph ph-check-circle text-lg"></i> Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
@endsection
