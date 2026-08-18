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

            <!-- Téléphone -->
            <div class="md:col-span-1">
                <label class="block text-sm font-bold text-slate-700 mb-1">Téléphone *</label>
                <input type="text" name="contact_phone" value="{{ old('contact_phone', $school->contact_phone) }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                @error('contact_phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
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
                <ul id="autocomplete-results" class="absolute z-[100] w-full bg-white border border-slate-200 rounded-lg shadow-xl mt-1 hidden max-h-48 overflow-y-auto"></ul>
                
                <div id="map" class="w-full h-64 mt-3 rounded-xl border border-slate-200 z-10 relative"></div>
                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $school->latitude ?? 5.359951) }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $school->longitude ?? -4.008256) }}">
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
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
