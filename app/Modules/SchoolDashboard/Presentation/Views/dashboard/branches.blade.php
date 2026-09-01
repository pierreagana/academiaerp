@extends('SchoolDashboard::layouts.app')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Succursales</h1>
        <p class="text-[13.5px] text-slate-500 mt-1">Gérez les différentes succursales de votre établissement (ex : Lycée, Collège, Primaire, Préscolaire, ou un campus dans une autre ville). Chaque succursale a ses propres classes, élèves, enseignants et personnel.</p>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <div class="flex items-center gap-2 mb-2">
            <i class="ph-fill ph-warning-circle text-lg"></i>
            <span class="font-bold">Il y a des erreurs :</span>
        </div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Add/Edit form -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill {{ isset($editBranch) ? 'ph-pencil-simple' : 'ph-plus-circle' }} text-primary-dynamic"></i>
                {{ isset($editBranch) ? 'Modifier la succursale' : 'Ajouter une succursale' }}
            </h2>
            @if(isset($editBranch))
            <a href="{{ route('school.branches') }}" class="text-[12px] font-medium text-slate-500 hover:text-slate-800 transition">Annuler l'édition</a>
            @endif
        </div>
        <div class="p-5">
            <form action="{{ isset($editBranch) ? route('school.branches.update', $editBranch->id) : route('school.branches.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($editBranch))
                    @method('PUT')
                @endif

                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">Identité</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Nom de l'Établissement <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" required value="{{ old('name', $editBranch->name ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="Ex : Lycée, Collège, Campus Abidjan...">
                    </div>
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Code Établissement</label>
                        <div class="relative flex items-center opacity-75 bg-slate-50 rounded-lg border border-slate-200 p-0.5">
                            <input type="text" value="{{ $editBranch->code ?? 'Généré automatiquement à la validation' }}" disabled class="w-full bg-transparent text-slate-600 text-[13.5px] font-bold px-3.5 py-2.5 outline-none cursor-not-allowed">
                            <div class="absolute right-3 text-[10.5px] font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded uppercase tracking-wider">Automatique</div>
                        </div>
                    </div>
                    <div>
                        <label for="slogan" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Slogan</label>
                        <input type="text" id="slogan" name="slogan" value="{{ old('slogan', $editBranch->slogan ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="Ex : Exceller ensemble">
                    </div>
                    <div>
                        <label for="logo" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ isset($editBranch) && $editBranch->logo_path ? 'Nouveau Logo' : 'Logo' }}</label>
                        <input type="file" id="logo" name="logo" accept="image/*"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[13px] rounded-lg px-3 py-2 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm">
                        @if(isset($editBranch) && $editBranch->logo_path)
                            <img src="{{ asset('storage/' . $editBranch->logo_path) }}" class="w-10 h-10 rounded-lg object-cover mt-2 border border-slate-200">
                        @endif
                    </div>
                </div>

                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">Classification</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="type" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Type</label>
                        <select id="type" name="type" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm cursor-pointer">
                            <option value="">Sélectionner...</option>
                            @foreach(['Secondaire (Lycée)', 'Collège', 'Primaire', 'Complexe Scolaire'] as $typeOpt)
                                <option value="{{ $typeOpt }}" {{ old('type', $editBranch->type ?? '') === $typeOpt ? 'selected' : '' }}>{{ $typeOpt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="sector" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Secteur</label>
                        <select id="sector" name="sector" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm cursor-pointer">
                            <option value="">Sélectionner...</option>
                            @foreach(\App\Modules\SuperAdmin\Domain\Models\School::getAvailableSectors() as $sectorOpt)
                                <option value="{{ $sectorOpt }}" {{ old('sector', $editBranch->sector ?? '') === $sectorOpt ? 'selected' : '' }}>{{ $sectorOpt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Statut <span class="text-red-500">*</span></label>
                        <select id="status" name="status" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm cursor-pointer">
                            @foreach(\App\Modules\Academic\Domain\Models\Branch::STATUSES as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $editBranch->status ?? 'active') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="language_regime" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Régime</label>
                        <select id="language_regime" name="language_regime" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm cursor-pointer">
                            <option value="">Sélectionner...</option>
                            @foreach(\App\Modules\SuperAdmin\Domain\Models\School::getAvailableLanguageRegimes() as $regimeOpt)
                                <option value="{{ $regimeOpt }}" {{ old('language_regime', $editBranch->language_regime ?? '') === $regimeOpt ? 'selected' : '' }}>{{ $regimeOpt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Niveaux Dispensés</label>
                        @php $selectedLevels = old('levels', $editBranch->levels ?? []); @endphp
                        <div class="flex flex-wrap gap-3">
                            @foreach(\App\Modules\Academic\Domain\Models\Branch::LEVELS as $level)
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="levels[]" value="{{ $level }}" {{ in_array($level, $selectedLevels) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-[#2F5F76] focus:ring-[#2F5F76]/20">
                                <span class="text-[13px] font-semibold text-slate-700">{{ $level }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">Coordonnées</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="contact_email" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Email Officiel</label>
                        <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $editBranch->contact_email ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="contact@etablissement.com">
                    </div>
                    <div>
                        <label for="phone_number" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Téléphone</label>
                        @php
                            [$savedCode, $savedNumber] = isset($editBranch) && $editBranch->contact_phone
                                ? (function ($phone) {
                                    $parts = explode(' ', $phone, 2);
                                    return count($parts) === 2 ? $parts : ['+225', $phone];
                                })($editBranch->contact_phone)
                                : ['+225', ''];
                        @endphp
                        <div class="flex gap-2">
                            <select name="phone_country_code" class="w-[110px] bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-2 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm cursor-pointer">
                                @foreach($countries as $c)
                                    <option value="{{ $c->dial_code }}" {{ old('phone_country_code', $savedCode) === $c->dial_code ? 'selected' : '' }}>{{ $c->flag_emoji }} {{ $c->dial_code }}</option>
                                @endforeach
                            </select>
                            <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $savedNumber) }}"
                                class="flex-1 bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                placeholder="Ex : 0102030405">
                        </div>
                    </div>
                    <div>
                        <label for="city" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Ville</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $editBranch->city ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="Ex : Dakar, Abidjan...">
                    </div>
                    <div>
                        <label for="country" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Pays</label>
                        <select id="country" name="country" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm cursor-pointer">
                            @foreach($countries as $c)
                                <option value="{{ $c->name }}" {{ old('country', $editBranch->country ?? "Côte d'Ivoire") === $c->name ? 'selected' : '' }}>{{ $c->flag_emoji }} {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2 space-y-3">
                        <div>
                            <label for="address_search" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Adresse / Position géographique</label>
                            <div class="relative">
                                <i class="ph-fill ph-map-pin absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <input type="text" id="address_search" name="address" value="{{ old('address', $editBranch->address ?? '') }}"
                                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                    placeholder="Rechercher une ville, une rue..." autocomplete="off">
                                <ul id="autocomplete-results" class="absolute z-[1100] w-full bg-white border border-slate-200 rounded-lg shadow-lg mt-1 hidden max-h-48 overflow-y-auto"></ul>
                            </div>
                        </div>

                        <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $editBranch->latitude ?? '5.359951') }}">
                        <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $editBranch->longitude ?? '-4.008256') }}">

                        <div id="map" class="w-full h-64 rounded-lg shadow-sm border border-slate-200"></div>
                    </div>
                </div>

                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">Gestion</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                    <div>
                        <label for="director_id" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Responsable</label>
                        @php $currentDirector = $editBranch?->director(); @endphp
                        <select id="director_id" name="director_id" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm cursor-pointer">
                            <option value="">Aucun</option>
                            @foreach($eligibleDirectors as $u)
                                <option value="{{ $u->id }}" {{ old('director_id', $currentDirector->id ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        @if($eligibleDirectors->isEmpty())
                            <p class="text-[11px] text-slate-400 mt-1">Aucun utilisateur avec le rôle "Directeur de succursale" pour le moment.</p>
                        @endif
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Équipements &amp; Services</label>
                        @php $selectedFacilityIds = old('facilities', $branchFacilityIds); @endphp
                        <div class="flex flex-wrap gap-2.5 p-3 bg-slate-50 border border-slate-200 rounded-lg">
                            @forelse($facilities as $facility)
                            <label class="flex items-center gap-2 px-3 py-2 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-[#2F5F76] transition text-[12.5px] font-bold text-slate-700 select-none">
                                <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" {{ in_array($facility->id, $selectedFacilityIds) ? 'checked' : '' }} class="rounded border-slate-300 text-[#2F5F76] focus:ring-[#2F5F76]">
                                <i class="ph {{ $facility->icon }} text-[#2F5F76]"></i>
                                <span>{{ $facility->name }}</span>
                            </label>
                            @empty
                            <p class="text-[12px] text-slate-400">Aucun équipement configuré sur la plateforme.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] px-6 py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                        <i class="ph-bold ph-floppy-disk"></i>
                        {{ isset($editBranch) ? 'Mettre à jour' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill ph-buildings text-primary-dynamic"></i>
                Liste des succursales
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Nom</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Code</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Type</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Localisation</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Principale</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($branches as $branch)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-[14px] font-bold text-slate-700 flex items-center gap-2">
                            @if($branch->logo_path)<img src="{{ asset('storage/' . $branch->logo_path) }}" class="w-6 h-6 rounded object-cover">@endif
                            {{ $branch->name }}
                        </td>
                        <td class="px-5 py-4 text-[12.5px] text-slate-500 font-mono">{{ $branch->code ?: '-' }}</td>
                        <td class="px-5 py-4 text-[13px] text-slate-600">{{ $branch->type ?: '-' }}</td>
                        <td class="px-5 py-4 text-[13px] text-slate-600">{{ collect([$branch->city, $branch->country])->filter()->implode(', ') ?: '-' }}</td>
                        <td class="px-5 py-4">
                            @if($branch->is_main)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100">Principale</span>
                            @else
                                <form action="{{ route('school.branches.set-main', $branch->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-bold text-slate-400 hover:text-blue-600 transition">Définir comme principale</button>
                                </form>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right flex justify-end gap-2">
                            <a href="?edit={{ $branch->id }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition inline-flex items-center justify-center" title="Éditer">
                                <i class="ph ph-pencil-simple text-[16px]"></i>
                            </a>
                            @unless($branch->is_main)
                            <form action="{{ route('school.branches.destroy', $branch->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette succursale ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition inline-flex items-center justify-center" title="Supprimer">
                                    <i class="ph ph-trash text-[16px]"></i>
                                </button>
                            </form>
                            @endunless
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-500 text-[13px]">Aucune succursale trouvée.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100">
            <span class="text-[13px] font-medium text-slate-500">{{ $branches->count() }} succursale(s) au total</span>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let branchMap = null;
    let branchMarker = null;

    function fetchBranchAddressFromCoords(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.display_name) {
                    document.getElementById('address_search').value = data.display_name;
                }
            })
            .catch(e => console.error("Reverse geocoding error:", e));
    }

    function initBranchMap() {
        const lat = parseFloat(document.getElementById('latitude').value) || 5.359951;
        const lng = parseFloat(document.getElementById('longitude').value) || -4.008256;

        branchMap = L.map('map').setView([lat, lng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(branchMap);

        branchMarker = L.marker([lat, lng], { draggable: true }).addTo(branchMap);

        branchMarker.on('dragend', function () {
            const position = branchMarker.getLatLng();
            document.getElementById('latitude').value = position.lat;
            document.getElementById('longitude').value = position.lng;
            fetchBranchAddressFromCoords(position.lat, position.lng);
        });

        branchMap.on('click', function (e) {
            branchMarker.setLatLng(e.latlng);
            document.getElementById('latitude').value = e.latlng.lat;
            document.getElementById('longitude').value = e.latlng.lng;
            fetchBranchAddressFromCoords(e.latlng.lat, e.latlng.lng);
        });

        setTimeout(() => { branchMap.invalidateSize(); }, 200);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initBranchMap();

        const addressInput = document.getElementById('address_search');
        const resultsList = document.getElementById('autocomplete-results');
        let searchTimeout = null;

        addressInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const query = this.value;

            if (query.length < 3) {
                resultsList.classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        resultsList.innerHTML = '';
                        if (data.length > 0) {
                            resultsList.classList.remove('hidden');
                            data.slice(0, 5).forEach(item => {
                                const li = document.createElement('li');
                                li.className = 'px-4 py-2 hover:bg-slate-50 cursor-pointer text-[13px] text-slate-700 border-b border-slate-100 last:border-0';
                                li.textContent = item.display_name;
                                li.onclick = () => {
                                    addressInput.value = item.display_name;
                                    resultsList.classList.add('hidden');

                                    const newLat = parseFloat(item.lat);
                                    const newLng = parseFloat(item.lon);

                                    if (branchMap) {
                                        branchMap.setView([newLat, newLng], 15);
                                        branchMarker.setLatLng([newLat, newLng]);
                                    }

                                    document.getElementById('latitude').value = newLat;
                                    document.getElementById('longitude').value = newLng;
                                };
                                resultsList.appendChild(li);
                            });
                        } else {
                            resultsList.classList.add('hidden');
                        }
                    });
            }, 500);
        });

        document.addEventListener('click', function (e) {
            if (e.target !== addressInput && e.target !== resultsList) {
                resultsList.classList.add('hidden');
            }
        });
    });
</script>
@endpush
