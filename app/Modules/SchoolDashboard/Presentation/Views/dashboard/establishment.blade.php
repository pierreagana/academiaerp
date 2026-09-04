@extends('SchoolDashboard::layouts.app')

@section('title', 'Informations de l\'Établissement')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-slate-800">Profil de l'Établissement</h2>
        <div class="flex gap-3">
            <a href="{{ route('school.legal-documents.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition flex items-center gap-2">
                <i class="ph ph-file-text"></i> Documents Légaux
            </a>
            <a href="{{ route('school.establishment.edit') }}" class="px-4 py-2 bg-primary-dynamic text-white font-bold rounded-xl hover:opacity-90 transition flex items-center gap-2">
                <i class="ph ph-pencil-simple"></i> Modifier
            </a>
            <a href="{{ route('school.dashboard') }}" class="px-4 py-2 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition">
                Retour
            </a>
        </div>
    </div>

    @if($school)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Logo et infos de base -->
        <div class="md:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col items-center text-center relative">
            @if($school->logo_url)
                <img src="{{ $school->logo_url }}" alt="Logo" class="w-32 h-32 rounded-xl object-cover mb-4 border border-slate-100 shadow-sm">
            @else
                <div class="w-32 h-32 rounded-xl bg-slate-100 flex items-center justify-center text-4xl font-bold text-slate-300 mb-4">
                    {{ strtoupper(substr($school->name, 0, 1)) }}
                </div>
            @endif
            <h3 class="text-xl font-bold text-slate-800 mb-1">{{ $school->name }}</h3>
            <p class="text-sm text-slate-500 mb-4">{{ $school->slogan ?? 'Aucun slogan' }}</p>

            <div class="w-full pt-4 border-t border-slate-100 flex justify-between items-center text-sm">
                <span class="text-slate-500">Forfait actuel</span>
                <span class="font-bold text-primary-dynamic bg-blue-50 px-3 py-1 rounded-full">{{ $school->plan_name }}</span>
            </div>
            <div class="w-full mt-3 flex justify-between items-center text-sm">
                <span class="text-slate-500">Secteur / Statut</span>
                <span class="font-bold text-slate-800 bg-slate-100 px-2.5 py-0.5 rounded-md text-xs">{{ $school->sector ?? 'Privé' }}</span>
            </div>
            <div class="w-full mt-3 flex justify-between items-center text-sm">
                <span class="text-slate-500">Régime</span>
                <span class="font-bold {{ ($school->is_bilingual ?? false) || str_contains(strtolower($school->language_regime ?? ''), 'bilingue') ? 'text-purple-700 bg-purple-50' : 'text-slate-700 bg-slate-100' }} px-2.5 py-0.5 rounded-md text-xs">
                    {{ $school->language_regime ?? (($school->is_bilingual ?? false) ? 'Bilingue' : 'Monolingue') }}
                </span>
            </div>
            @if($school->code)
            <div class="w-full mt-3 flex justify-between items-center text-sm">
                <span class="text-slate-500">Code Établissement</span>
                <span class="font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded-md font-mono text-xs">{{ $school->code }}</span>
            </div>
            @endif
            <div class="w-full mt-3 flex justify-between items-center text-sm">
                <span class="text-slate-500">Statut</span>
                <span class="font-bold {{ $school->status === 'actif' ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50' }} px-3 py-1 rounded-full">
                    {{ ucfirst($school->status) }}
                </span>
            </div>

            @php
                $levels = is_array($school->levels) ? $school->levels : (is_string($school->levels) ? json_decode($school->levels, true) : []);
            @endphp
            @if(!empty($levels))
                <div class="w-full mt-4 pt-3 border-t border-slate-100 text-left">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Niveaux Dispensés</span>
                    <div class="flex flex-wrap gap-1">
                        @foreach($levels as $lvl)
                            <span class="px-2 py-0.5 bg-blue-50 text-primary-dynamic rounded text-[11px] font-bold">
                                {{ $lvl }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Coordonnées et Contact -->
        <div class="md:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h4 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-100 pb-2">Coordonnées</h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Responsable</label>
                    <p class="font-semibold text-slate-700">{{ auth()->user()->name }}</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Téléphone</label>
                    <p class="font-semibold text-slate-700">{{ $school->contact_phone ?? 'Non renseigné' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Email Officiel</label>
                    <p class="font-semibold text-slate-700">{{ $school->contact_email ?? 'Non renseigné' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Adresse</label>
                    <p class="font-semibold text-slate-700">{{ $school->location ?? 'Non renseignée' }}</p>
                </div>
            </div>

            <!-- Équipements & Services -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-2">
                    <h4 class="text-lg font-bold text-slate-800">Équipements & Services</h4>
                    <span class="text-xs text-slate-500 font-semibold">{{ $school->facilitiesList->count() }} équipement(s) déclaré(s)</span>
                </div>

                @if($school->facilitiesList->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($school->facilitiesList as $fac)
                            <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-blue-50 text-primary-dynamic flex items-center justify-center text-lg shrink-0">
                                    <i class="ph {{ $fac->icon }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate">{{ $fac->name }}</p>
                                    <p class="text-[10.5px] text-slate-400 truncate">{{ $fac->category }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 bg-slate-50 border border-dashed border-slate-200 rounded-xl text-center">
                        <p class="text-xs text-slate-500 font-medium">Aucun équipement déclaré pour le moment.</p>
                        <a href="{{ route('school.establishment.edit') }}" class="text-xs font-bold text-primary-dynamic mt-1 inline-block hover:underline">
                            + Déclarer vos équipements
                        </a>
                    </div>
                @endif
            </div>

            <h4 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Position Géographique</h4>
            <div id="schoolMap" class="w-full h-64 rounded-xl border border-slate-200 z-10 relative bg-slate-100"></div>
        </div>
    </div>

    <!-- Catalogue photos -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-2">
            <h4 class="text-lg font-bold text-slate-800">Catalogue photos</h4>
            <span class="text-xs text-slate-500 font-semibold">{{ count($school->catalog_paths ?? []) }}/{{ \App\Modules\SuperAdmin\Domain\Models\School::CATALOG_MAX_PHOTOS }}</span>
        </div>
        @if(!empty($school->catalog_paths))
            <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                @foreach($school->catalog_paths as $path)
                    <img src="{{ Storage::url($path) }}" class="w-full h-24 object-cover rounded-xl border border-slate-200">
                @endforeach
            </div>
        @else
            <div class="p-4 bg-slate-50 border border-dashed border-slate-200 rounded-xl text-center">
                <p class="text-xs text-slate-500 font-medium">Aucune photo dans le catalogue pour le moment.</p>
                <a href="{{ route('school.establishment.edit') }}" class="text-xs font-bold text-primary-dynamic mt-1 inline-block hover:underline">
                    + Ajouter des photos
                </a>
            </div>
        @endif
    </div>
    @else
    <div class="bg-yellow-50 text-yellow-800 p-4 rounded-xl">
        Aucun établissement n'est rattaché à votre compte.
    </div>
    @endif
</div>
@endsection

@push('scripts')
@if($school && $school->latitude && $school->longitude)
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let lat = {{ $school->latitude }};
        let lng = {{ $school->longitude }};

        const map = L.map('schoolMap').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map)
            .bindPopup("<b>{{ $school->name }}</b><br>{{ $school->location }}")
            .openPopup();
    });
</script>
@endif
@endpush
