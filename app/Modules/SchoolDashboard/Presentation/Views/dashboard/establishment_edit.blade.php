@extends('SchoolDashboard::layouts.app')

@section('title', 'Modifier l\'Établissement')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-slate-800">Modifier l'Établissement</h2>
        <a href="{{ route('school.establishment') }}" class="px-4 py-2 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition">
            Annuler
        </a>
    </div>

    @if($school)
    <form action="{{ route('school.establishment.update') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Code (Lecture Seule) -->
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-1">Code Établissement (Généré automatiquement)</label>
                <input type="text" value="{{ $school->code }}" readonly class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-3 outline-none text-slate-500 font-mono cursor-not-allowed">
            </div>

            <!-- Nom -->
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-1">Nom de l'Établissement *</label>
                <input type="text" name="name" value="{{ old('name', $school->name) }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Slogan -->
            <div class="md:col-span-1">
                <label class="block text-sm font-bold text-slate-700 mb-1">Slogan</label>
                <input type="text" name="slogan" value="{{ old('slogan', $school->slogan) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                @error('slogan') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Logo -->
            <div class="md:col-span-1">
                <label class="block text-sm font-bold text-slate-700 mb-1">Nouveau Logo</label>
                <input type="file" name="logo" accept="image/*" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-[9px] outline-none focus:border-blue-600 focus:bg-white transition file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:bg-slate-200 file:text-slate-700">
                @error('logo') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Secteur / Statut Juridique -->
            <div class="md:col-span-1">
                <label class="block text-sm font-bold text-slate-700 mb-1">Secteur / Statut Juridique</label>
                <select name="sector" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition text-sm font-semibold text-slate-800">
                    @foreach($availableSectors ?? ['Privé', 'Public', 'Semi-privé'] as $sec)
                        <option value="{{ $sec }}" {{ old('sector', $school->sector) === $sec ? 'selected' : '' }}>{{ $sec }}</option>
                    @endforeach
                </select>
                @error('sector') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Régime Linguistique (Bilingue ou non) -->
            <div class="md:col-span-1">
                <label class="block text-sm font-bold text-slate-700 mb-1">Régime Linguistique</label>
                <select name="language_regime" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition text-sm font-semibold text-slate-800">
                    @foreach($availableLanguageRegimes ?? ['Monolingue (Français)', 'Bilingue (Français / Anglais)', 'International / Trilingue'] as $lang)
                        <option value="{{ $lang }}" {{ old('language_regime', $school->language_regime) === $lang ? 'selected' : '' }}>{{ $lang }}</option>
                    @endforeach
                </select>
                @error('language_regime') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Niveaux & Ordres d'Enseignement (Multisélection) -->
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                    <span>Niveaux d'Enseignement Dispensés</span>
                    <span class="text-xs text-slate-400 font-normal">Cochez tous les ordres d'enseignement assurés</span>
                </label>
                @php
                    $currentLevels = is_array($school->levels) ? $school->levels : (is_string($school->levels) ? json_decode($school->levels, true) : []);
                    $currentLevels = is_array($currentLevels) ? $currentLevels : [];
                @endphp
                <div class="flex flex-wrap gap-2.5 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    @foreach($availableLevels ?? ['Préscolaire', 'Primaire', 'Collège', 'Lycée'] as $lvl)
                        <label class="flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-primary-dynamic transition text-xs font-bold text-slate-700 select-none">
                            <input type="checkbox" name="levels[]" value="{{ $lvl }}"
                                   {{ in_array($lvl, old('levels', $currentLevels)) ? 'checked' : '' }}
                                   class="rounded text-primary-dynamic focus:ring-primary-dynamic">
                            <i class="ph ph-graduation-cap text-primary-dynamic"></i>
                            <span>{{ $lvl }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Heures de cours -->
            <div class="md:col-span-1">
                <label class="block text-sm font-bold text-slate-700 mb-1">Heure de début des cours</label>
                <input type="time" name="day_start_time" value="{{ old('day_start_time', $school->day_start_time ? \Carbon\Carbon::parse($school->day_start_time)->format('H:i') : '') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                <p class="text-[11px] text-slate-400 mt-1">Aucun cours ne pourra être programmé avant cette heure.</p>
                @error('day_start_time') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-1">
                <label class="block text-sm font-bold text-slate-700 mb-1">Heure de fin des cours</label>
                <input type="time" name="day_end_time" value="{{ old('day_end_time', $school->day_end_time ? \Carbon\Carbon::parse($school->day_end_time)->format('H:i') : '') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                <p class="text-[11px] text-slate-400 mt-1">Aucun cours ne pourra dépasser cette heure.</p>
                @error('day_end_time') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Téléphone -->
            <div class="md:col-span-1">
                <label class="block text-sm font-bold text-slate-700 mb-1">Téléphone *</label>
                @php [$schoolPhoneCode, $schoolPhoneNumber] = \App\Modules\SuperAdmin\Domain\Models\Country::splitPhone($school->contact_phone); @endphp
                @include('SchoolDashboard::components.phone-input', [
                    'selectedCode' => $schoolPhoneCode,
                    'selectedNumber' => $schoolPhoneNumber,
                    'required' => true,
                    'selectClass' => 'w-[110px] bg-slate-50 border border-slate-200 rounded-xl px-2 py-3 outline-none focus:border-blue-600 focus:bg-white transition cursor-pointer',
                    'inputClass' => 'flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition',
                ])
                @error('phone_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Email -->
            <div class="md:col-span-1">
                <label class="block text-sm font-bold text-slate-700 mb-1">Email Officiel *</label>
                <input type="email" name="contact_email" value="{{ old('contact_email', $school->contact_email) }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                @error('contact_email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Adresse & Carte -->
            <div class="md:col-span-2 relative">
                <label class="block text-sm font-bold text-slate-700 mb-1">Adresse / Position géographique</label>
                <input type="text" id="address_search" name="location" value="{{ old('location', $school->location) }}" placeholder="Rechercher une ville, une rue..." autocomplete="off" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                <ul id="autocomplete-results" class="absolute z-[1100] w-full bg-white border border-slate-200 rounded-lg shadow-xl mt-1 hidden max-h-48 overflow-y-auto"></ul>
                
                <div id="map" class="w-full h-64 mt-3 rounded-xl border border-slate-200 z-10 relative"></div>
                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $school->latitude ?? 5.359951) }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $school->longitude ?? -4.008256) }}">
            </div>

            <!-- Équipements & Services Scolaires (Multisélection) -->
            <div class="md:col-span-2 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <label class="block text-sm font-bold text-slate-800">Équipements & Services de l'Établissement</label>
                        <p class="text-xs text-slate-500 mt-0.5">Cochez les commodités et installations disponibles dans votre école.</p>
                    </div>
                    <span class="text-xs font-semibold text-primary-dynamic bg-blue-50 px-2.5 py-1 rounded-lg">Catalogue SuperAdmin</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 p-4 bg-slate-50 border border-slate-200/80 rounded-2xl">
                    @forelse($facilities ?? [] as $facility)
                        <label class="flex items-start gap-3 p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-primary-dynamic transition select-none group">
                            <input type="checkbox" name="facilities[]" value="{{ $facility->id }}"
                                   {{ in_array($facility->id, $selectedFacilityIds ?? []) ? 'checked' : '' }}
                                   class="mt-0.5 rounded text-primary-dynamic focus:ring-primary-dynamic">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-blue-50 group-hover:text-primary-dynamic text-slate-700 flex items-center justify-center text-base shrink-0 transition">
                                <i class="ph {{ $facility->icon }}"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 group-hover:text-primary-dynamic transition">{{ $facility->name }}</p>
                                <p class="text-[10.5px] text-slate-400 truncate">{{ $facility->category }}</p>
                            </div>
                        </label>
                    @empty
                        <div class="col-span-3 text-center py-4 text-xs text-slate-400">
                            Aucun équipement disponible dans le catalogue pour le moment.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Catalogue photos (max 6) -->
            <div class="md:col-span-2 pt-4 border-t border-slate-100" x-data="{ previews: [] }">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <label class="block text-sm font-bold text-slate-800">Catalogue photos de l'établissement</label>
                        <p class="text-xs text-slate-500 mt-0.5">Ajoutez jusqu'à {{ \App\Modules\SuperAdmin\Domain\Models\School::CATALOG_MAX_PHOTOS }} photos (bâtiments, salles, cour...).</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-500">{{ count($school->catalog_paths ?? []) }}/{{ \App\Modules\SuperAdmin\Domain\Models\School::CATALOG_MAX_PHOTOS }}</span>
                </div>

                <template x-if="previews.length > 0">
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-blue-700 mb-2">Nouvelles photos sélectionnées (pas encore enregistrées) :</p>
                        <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                            <template x-for="(url, i) in previews" :key="i">
                                <img :src="url" class="w-full h-24 object-cover rounded-xl border-2 border-blue-300">
                            </template>
                        </div>
                    </div>
                </template>

                @if(!empty($school->catalog_paths))
                    <div class="grid grid-cols-3 md:grid-cols-6 gap-3 mb-3">
                        @foreach($school->catalog_paths as $path)
                            <div class="relative group">
                                <img src="{{ Storage::url($path) }}" class="w-full h-24 object-cover rounded-xl border border-slate-200">
                                <label class="absolute top-1 right-1 bg-white/90 rounded-full p-1 cursor-pointer">
                                    <input type="checkbox" name="remove_catalog[]" value="{{ $path }}" class="align-middle">
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-500 mb-2">Cochez une photo pour la supprimer à l'enregistrement.</p>
                @endif
                <input type="file" name="catalog[]" accept="image/*" multiple
                    @change="previews = Array.from($event.target.files).map(f => URL.createObjectURL(f))"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-[9px] outline-none focus:border-blue-600 focus:bg-white transition file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:bg-slate-200 file:text-slate-700">
                @error('catalog') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                @error('catalog.*') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="pt-6 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-dynamic text-white font-bold hover:opacity-95 transition shadow-sm flex items-center gap-2">
                <i class="ph ph-check-circle text-lg"></i> Enregistrer les modifications
            </button>
        </div>
    </form>
    @else
    <div class="bg-yellow-50 text-yellow-800 p-4 rounded-xl">
        Aucun établissement n'est rattaché à votre compte.
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let lat = parseFloat(document.getElementById('latitude').value);
        let lng = parseFloat(document.getElementById('longitude').value);
        
        const map = L.map('map').setView([lat, lng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        let marker = L.marker([lat, lng], {draggable: true}).addTo(map);

        function fetchAddressFromCoords(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
                headers: { 'Accept-Language': 'fr' }
            })
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        document.getElementById('address_search').value = data.display_name;
                    }
                })
                .catch(e => console.error("Reverse geocoding error:", e));
        }

        marker.on('dragend', function(e) {
            const position = marker.getLatLng();
            document.getElementById('latitude').value = position.lat;
            document.getElementById('longitude').value = position.lng;
            fetchAddressFromCoords(position.lat, position.lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            document.getElementById('latitude').value = e.latlng.lat;
            document.getElementById('longitude').value = e.latlng.lng;
            fetchAddressFromCoords(e.latlng.lat, e.latlng.lng);
        });

        const addressInput = document.getElementById('address_search');
        const resultsList = document.getElementById('autocomplete-results');
        let timeout = null;

        addressInput.addEventListener('input', function() {
            clearTimeout(timeout);
            const query = this.value;
            if (query.length < 3) {
                resultsList.classList.add('hidden');
                return;
            }
            timeout = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`, {
                    headers: { 'Accept-Language': 'fr' }
                })
                    .then(response => response.json())
                    .then(data => {
                        resultsList.innerHTML = '';
                        if (data.length > 0) {
                            resultsList.classList.remove('hidden');
                            data.slice(0, 5).forEach(item => {
                                const li = document.createElement('li');
                                li.className = 'px-4 py-2 hover:bg-slate-50 cursor-pointer text-sm text-slate-700 border-b border-slate-100 last:border-0';
                                li.textContent = item.display_name;
                                li.onclick = () => {
                                    addressInput.value = item.display_name;
                                    resultsList.classList.add('hidden');
                                    const newLat = parseFloat(item.lat);
                                    const newLng = parseFloat(item.lon);
                                    map.setView([newLat, newLng], 15);
                                    marker.setLatLng([newLat, newLng]);
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

        document.addEventListener('click', function(e) {
            if (e.target !== addressInput && e.target !== resultsList) {
                resultsList.classList.add('hidden');
            }
        });
    });
</script>
@endpush
