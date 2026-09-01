@extends('SchoolDashboard::layouts.app')

@section('title', 'Modifier le profil School Track')

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
        <h2 class="text-2xl font-bold text-slate-800">Modifier le profil School Track</h2>
        <a href="{{ route('school.school-track') }}" class="px-4 py-2 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition">
            Annuler
        </a>
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

        <div class="flex items-start gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl">
            <i class="ph ph-info text-blue-600 text-lg mt-0.5"></i>
            <p class="text-xs text-blue-800 leading-relaxed">
                Le taux de réussite (BAC/BEPC/CEPE/BTS) et la progression annuelle ne se saisissent plus ici : ils sont
                calculés automatiquement depuis vos résultats d'examens validés et les promotions de classe.
                Rendez-vous sur <a href="{{ route('school.exam-results.index') }}" class="font-bold underline">Résultats aux examens</a> pour les renseigner,
                ou consultez-les sur le <a href="{{ route('school.school-track') }}" class="font-bold underline">profil</a>.
            </p>
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
