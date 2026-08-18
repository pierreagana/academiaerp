<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Academia ERP</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; }
        #map { height: 250px; width: 100%; border-radius: 0.5rem; z-index: 10; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="flex min-h-screen flex-col lg:flex-row">
        
        <!-- Left Panel: Login Form -->
        <div class="w-full lg:w-3/5 flex flex-col justify-center px-8 md:px-12 py-12 h-screen overflow-y-auto">
            
            <div class="max-w-2xl w-full mx-auto">
                <h1 class="text-[32px] font-bold text-slate-900 tracking-tight mb-2">Inscrivez votre établissement</h1>
                <p class="text-[14px] text-slate-500 mb-8">Remplissez les informations ci-dessous pour créer votre espace Academia ERP.</p>

                <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    @if ($errors->any())
                        <div class="bg-red-50 text-red-600 text-[13px] font-medium p-3 rounded-lg border border-red-200">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>- {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- School Name -->
                        <div class="md:col-span-2">
                            <label for="school_name" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Nom de l'Établissement <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="ph-fill ph-buildings absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <input type="text" id="school_name" name="school_name" required autofocus
                                    class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                    placeholder="Lycée d'Excellence" value="{{ old('school_name') }}">
                            </div>
                        </div>

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Nom et prénoms du Responsable <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="ph-fill ph-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <input type="text" id="name" name="name" required
                                    class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                    placeholder="Jean Dupont" value="{{ old('name') }}">
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Email Officiel <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="ph-fill ph-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <input type="email" id="email" name="email" required
                                    class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                    placeholder="contact@ecole.com" value="{{ old('email') }}">
                            </div>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Téléphone <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="ph-fill ph-phone absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <input type="text" id="phone" name="phone" required
                                    class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                    placeholder="+33 6 12 34 56 78" value="{{ old('phone') }}">
                            </div>
                        </div>

                        <!-- Plan -->
                        <div>
                            <label for="plan_name" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Forfait Souhaité</label>
                            <div class="relative">
                                <i class="ph-fill ph-star absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <select id="plan_name" name="plan_name"
                                    class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                                    <option value="Basic">Basic</option>
                                    <option value="Premium">Premium</option>
                                    <option value="Enterprise">Enterprise</option>
                                </select>
                                <i class="ph-bold ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            </div>
                        </div>

                        <!-- Slogan -->
                        <div>
                            <label for="slogan" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Slogan</label>
                            <div class="relative">
                                <i class="ph-fill ph-chat-circle-text absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <input type="text" id="slogan" name="slogan"
                                    class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                    placeholder="L'excellence avant tout" value="{{ old('slogan') }}">
                            </div>
                        </div>

                        <!-- Logo -->
                        <div>
                            <label for="logo" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Logo</label>
                            <div class="relative">
                                <i class="ph-fill ph-image absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <input type="file" id="logo" name="logo" accept="image/*"
                                    class="w-full bg-white border border-slate-200 text-slate-600 text-[14px] font-medium rounded-lg pl-10 pr-4 py-[7px] outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            </div>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Mot de passe <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="ph-fill ph-lock-key absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <input type="password" id="password" name="password" required
                                    class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-10 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                    placeholder="••••••••">
                            </div>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Confirmer le mot de passe <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="ph-fill ph-lock-key absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <input type="password" id="password_confirmation" name="password_confirmation" required
                                    class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-10 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                    placeholder="••••••••">
                            </div>
                        </div>

                        <!-- Address with Autocomplete & Map -->
                        <div class="md:col-span-2 space-y-3">
                            <div>
                                <label for="address_search" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Adresse / Position géographique</label>
                                <div class="relative">
                                    <i class="ph-fill ph-map-pin absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                    <input type="text" id="address_search" name="address"
                                        class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                        placeholder="Rechercher une ville, une rue..." autocomplete="off">
                                    <ul id="autocomplete-results" class="absolute z-20 w-full bg-white border border-slate-200 rounded-lg shadow-lg mt-1 hidden max-h-48 overflow-y-auto"></ul>
                                </div>
                            </div>
                            
                            <!-- Hidden fields for coords -->
                            <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', '5.359951') }}">
                            <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', '-4.008256') }}">

                            <div id="map" class="shadow-sm border border-slate-200"></div>
                        </div>

                    </div>

                    <button type="submit" class="w-full bg-[#2B5A73] hover:bg-[#1E4357] text-white font-bold text-[14px] py-3 rounded-lg shadow-md transition flex items-center justify-center gap-2 mt-4">
                        Créer mon établissement <i class="ph-bold ph-arrow-right"></i>
                    </button>
                </form>

                <div class="text-center mt-6">
                    <p class="text-[12px] font-semibold text-slate-600">
                        Déjà un compte ? <a href="{{ route('login') }}" class="text-[#2B5A73] hover:underline font-bold">Connectez-vous à votre portail !</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Panel: Visual/Brand -->
        <div class="hidden lg:flex lg:w-2/5 relative bg-[#0D2F2A] overflow-hidden flex-col justify-between p-12">
            <div class="absolute inset-0 z-0 opacity-40 mix-blend-overlay">
                <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=2000&auto=format&fit=crop" class="w-full h-full object-cover" alt="Students">
            </div>
            <div class="absolute inset-0 z-0 bg-gradient-to-t from-[#0D2F2A] via-[#0D2F2A]/80 to-transparent"></div>
            <div class="absolute inset-0 z-0 bg-gradient-to-r from-[#0D2F2A] via-[#0D2F2A]/60 to-transparent"></div>

            <div class="relative z-10 flex items-center gap-3">
                <div class="w-8 h-8 rounded bg-[#27A792] flex items-center justify-center text-white font-bold text-lg">A</div>
                <span class="text-white font-bold text-xl tracking-tight">Academia ERP</span>
            </div>

            <div class="relative z-10 max-w-lg mb-12">
                <span class="inline-block border border-[#27A792] text-[#27A792] text-[10px] font-bold tracking-widest uppercase px-3 py-1.5 rounded-full mb-6">
                    INSCRIPTION RAPIDE
                </span>
                
                <h2 class="text-4xl font-extrabold text-white leading-[1.1] tracking-tight mb-6">
                    Commencez à numériser votre école.
                </h2>
                
                <p class="text-[15px] text-slate-300 font-medium leading-relaxed max-w-md">
                    Rejoignez des centaines d'établissements qui utilisent Academia ERP pour simplifier leur gestion académique, administrative et financière.
                </p>
            </div>
        </div>

    </div>

    <!-- Leaflet JS & Autocomplete script -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Default center (Paris or user's previous selection)
            let lat = parseFloat(document.getElementById('latitude').value) || 5.359951;
            let lng = parseFloat(document.getElementById('longitude').value) || -4.008256;
            
            // Initialize map
            const map = L.map('map').setView([lat, lng], 13);
            
            // OSM tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            // Add marker
            let marker = L.marker([lat, lng], {draggable: true}).addTo(map);

            function fetchAddressFromCoords(lat, lng) {
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.display_name) {
                            document.getElementById('address_search').value = data.display_name;
                        }
                    })
                    .catch(e => console.error("Reverse geocoding error:", e));
            }

            // Update hidden inputs when marker is dragged
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                document.getElementById('latitude').value = position.lat;
                document.getElementById('longitude').value = position.lng;
                fetchAddressFromCoords(position.lat, position.lng);
            });

            // Map click to move marker
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                document.getElementById('latitude').value = e.latlng.lat;
                document.getElementById('longitude').value = e.latlng.lng;
                fetchAddressFromCoords(e.latlng.lat, e.latlng.lng);
            });

            // Autocomplete with Nominatim (OSM)
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
                }, 500); // debounce 500ms
            });

            // Close autocomplete when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target !== addressInput && e.target !== resultsList) {
                    resultsList.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
