<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Academia ERP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Poppins', sans-serif; }
        #map { height: 250px; width: 100%; border-radius: 0.5rem; z-index: 10; }
    </style>
    @include('SchoolDashboard::components.searchable-select')
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

                    <!-- Step Indicator -->
                    <div class="flex items-center gap-3 pb-1">
                        <div class="flex items-center gap-2" id="regStepDot1">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold bg-[#2F5F76] text-white">1</span>
                            <span class="text-slate-900 text-[13px] font-semibold">Vos Informations</span>
                        </div>
                        <div class="flex-1 h-px bg-slate-200"></div>
                        <div class="flex items-center gap-2" id="regStepDot2">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold bg-slate-200 text-slate-500" id="regStepDot2Circle">2</span>
                            <span class="text-slate-400 text-[13px] font-semibold" id="regStepDot2Label">Détails de l'Établissement</span>
                        </div>
                    </div>

                    <!-- Étape 1 : Vos Informations -->
                    <div id="registerStep1">
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
                            <label for="phone_number" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Téléphone <span class="text-red-500">*</span></label>
                            @include('SchoolDashboard::components.phone-input', [
                                'required' => true,
                                'selectClass' => 'w-[100px] bg-white border border-slate-200 text-slate-900 text-[13px] font-medium rounded-lg px-2 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm cursor-pointer',
                                'inputClass' => 'flex-1 bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm',
                            ])
                        </div>

                        <!-- Info: credentials emailed -->
                        <div class="md:col-span-2 p-3 rounded-lg bg-blue-50 border border-blue-100 text-[#1E4B7A] text-[12.5px] font-semibold flex items-start gap-2">
                            <i class="ph-fill ph-shield-check mt-0.5"></i>
                            Vos identifiants de connexion (mot de passe généré automatiquement) vous seront envoyés à cette adresse email.
                        </div>
                    </div>
                    </div>

                    <div class="flex justify-end pt-2" id="regFooterStep1">
                        <button type="button" onclick="goToRegisterStep(2)" class="bg-[#2B5A73] hover:bg-[#1E4357] text-white font-bold text-[14px] px-6 py-3 rounded-lg shadow-md transition flex items-center justify-center gap-2">
                            Suivant <i class="ph-bold ph-arrow-right"></i>
                        </button>
                    </div>

                    <!-- Étape 2 : Détails de l'Établissement -->
                    <div id="registerStep2" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Plan -->
                        <div>
                            <label for="plan_name" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Forfait Souhaité</label>
                            <div class="relative">
                                <i class="ph-fill ph-star absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <select id="plan_name" name="plan_name"
                                    class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                                    @foreach($saasPackages as $pkg)
                                        <option value="{{ $pkg->name }}" {{ old('plan_name', 'Starter') === $pkg->name ? 'selected' : '' }}>
                                            {{ $pkg->name }}{{ $pkg->is_popular ? ' (Recommandé)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="ph-bold ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            </div>
                        </div>

                        <!-- Type -->
                        <div>
                            <label for="type" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Type d'Établissement</label>
                            <div class="relative">
                                <i class="ph-fill ph-graduation-cap absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <select id="type" name="type"
                                    class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                                    @foreach(['Secondaire (Lycée)', 'Collège', 'Primaire', 'Complexe Scolaire'] as $typeOpt)
                                        <option value="{{ $typeOpt }}" {{ old('type') === $typeOpt ? 'selected' : '' }}>{{ $typeOpt }}</option>
                                    @endforeach
                                </select>
                                <i class="ph-bold ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            </div>
                        </div>

                        <!-- Sector -->
                        <div>
                            <label for="sector" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Secteur / Statut</label>
                            <div class="relative">
                                <i class="ph-fill ph-bank absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <select id="sector" name="sector"
                                    class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                                    @foreach($availableSectors ?? ['Privé', 'Public', 'Semi-privé'] as $sectorOpt)
                                        <option value="{{ $sectorOpt }}" {{ old('sector') === $sectorOpt ? 'selected' : '' }}>{{ $sectorOpt }}</option>
                                    @endforeach
                                </select>
                                <i class="ph-bold ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            </div>
                        </div>

                        <!-- Language Regime -->
                        <div>
                            <label for="language_regime" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Régime Linguistique</label>
                            <div class="relative">
                                <i class="ph-fill ph-translate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <select id="language_regime" name="language_regime"
                                    class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                                    @foreach($availableLanguageRegimes ?? ['Monolingue (Français)', 'Bilingue (Français / Anglais)', 'International / Trilingue'] as $langOpt)
                                        <option value="{{ $langOpt }}" {{ old('language_regime') === $langOpt ? 'selected' : '' }}>{{ $langOpt }}</option>
                                    @endforeach
                                </select>
                                <i class="ph-bold ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            </div>
                        </div>

                        <!-- Students Count -->
                        <div>
                            <label for="students_count" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Nombre d'Élèves Estimé</label>
                            <div class="relative">
                                <i class="ph-fill ph-users-three absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                <input type="number" id="students_count" name="students_count" min="0"
                                    class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                    placeholder="ex: 850" value="{{ old('students_count') }}">
                            </div>
                        </div>

                        <!-- Levels -->
                        <div class="md:col-span-2">
                            <label class="block text-[12px] font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Niveaux &amp; Cycles d'Enseignement</span>
                                <span class="text-[11px] font-normal text-slate-400">Sélectionnez les ordres dispensés</span>
                            </label>
                            <div class="flex flex-wrap gap-2 p-2.5 bg-white border border-slate-200 rounded-lg shadow-sm">
                                @foreach($availableLevels ?? ['Préscolaire', 'Primaire', 'Collège', 'Lycée'] as $lvl)
                                    <label class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-200/80 rounded-lg cursor-pointer hover:border-[#2F5F76] text-xs font-bold text-slate-700 transition">
                                        <input type="checkbox" name="levels[]" value="{{ $lvl }}" {{ in_array($lvl, old('levels', [])) ? 'checked' : '' }} class="rounded text-[#2F5F76] focus:ring-[#2F5F76]">
                                        <span>{{ $lvl }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Facilities -->
                        <div class="md:col-span-2">
                            <label class="block text-[12px] font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Équipements &amp; Services Scolaires</span>
                                <span class="text-[11px] font-normal text-slate-400">Sélectionnez les commodités</span>
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-40 overflow-y-auto p-3 bg-white border border-slate-200 rounded-lg shadow-sm">
                                @foreach($facilities ?? [] as $facility)
                                    <label class="flex items-center gap-2 p-2 bg-slate-50 border border-slate-200/70 rounded-lg cursor-pointer hover:border-[#2F5F76] transition text-xs font-semibold text-slate-800">
                                        <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" {{ in_array($facility->id, old('facilities', [])) ? 'checked' : '' }} class="rounded text-[#2F5F76] focus:ring-[#2F5F76]">
                                        <i class="ph {{ $facility->icon }} text-base text-[#2F5F76] shrink-0"></i>
                                        <span class="truncate">{{ $facility->name }}</span>
                                    </label>
                                @endforeach
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

                        <!-- Address with Autocomplete & Map -->
                        <div>
                            <label for="city" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Ville</label>
                            <input type="text" id="city" name="city" value="{{ old('city') }}"
                                class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                placeholder="Ex : Abidjan">
                        </div>
                        <div>
                            <label for="country" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Pays</label>
                            @include('SchoolDashboard::components.country-select', [
                                'selectClass' => 'w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm cursor-pointer',
                            ])
                        </div>

                        <div class="md:col-span-2 space-y-3">
                            <div>
                                <label for="address_search" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Adresse / Position géographique</label>
                                <div class="relative">
                                    <i class="ph-fill ph-map-pin absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                    <input type="text" id="address_search" name="address"
                                        class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                        placeholder="Rechercher une ville, une rue..." autocomplete="off">
                                    <ul id="autocomplete-results" class="absolute z-[1100] w-full bg-white border border-slate-200 rounded-lg shadow-lg mt-1 hidden max-h-48 overflow-y-auto"></ul>
                                </div>
                            </div>
                            
                            <!-- Hidden fields for coords -->
                            <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', '5.359951') }}">
                            <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', '-4.008256') }}">

                            <div id="map" class="shadow-sm border border-slate-200"></div>
                        </div>

                    </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2" id="regFooterStep2" style="display:none">
                        <button type="button" onclick="goToRegisterStep(1)" class="border border-slate-300 text-slate-700 font-bold text-[14px] px-5 py-3 rounded-lg transition flex items-center justify-center gap-2 hover:bg-slate-50">
                            <i class="ph-bold ph-arrow-left"></i> Précédent
                        </button>
                        <button type="submit" class="flex-1 bg-[#2B5A73] hover:bg-[#1E4357] text-white font-bold text-[14px] py-3 rounded-lg shadow-md transition flex items-center justify-center gap-2">
                            Créer mon établissement <i class="ph-bold ph-arrow-right"></i>
                        </button>
                    </div>
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

    <!-- Leaflet JS (bundled) & Autocomplete script -->
    <script>
        let registerMap = null;
        let registerMarker = null;

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

        function initRegisterMap() {
            if (registerMap) {
                setTimeout(() => { registerMap.invalidateSize(); }, 200);
                return;
            }

            let lat = parseFloat(document.getElementById('latitude').value) || 5.359951;
            let lng = parseFloat(document.getElementById('longitude').value) || -4.008256;

            registerMap = L.map('map').setView([lat, lng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(registerMap);

            registerMarker = L.marker([lat, lng], {draggable: true}).addTo(registerMap);

            registerMarker.on('dragend', function(e) {
                const position = registerMarker.getLatLng();
                document.getElementById('latitude').value = position.lat;
                document.getElementById('longitude').value = position.lng;
                fetchAddressFromCoords(position.lat, position.lng);
            });

            registerMap.on('click', function(e) {
                registerMarker.setLatLng(e.latlng);
                document.getElementById('latitude').value = e.latlng.lat;
                document.getElementById('longitude').value = e.latlng.lng;
                fetchAddressFromCoords(e.latlng.lat, e.latlng.lng);
            });

            setTimeout(() => { registerMap.invalidateSize(); }, 200);
        }

        function goToRegisterStep(step) {
            const step1 = document.getElementById('registerStep1');
            const step2 = document.getElementById('registerStep2');
            const footer1 = document.getElementById('regFooterStep1');
            const footer2 = document.getElementById('regFooterStep2');

            if (step === 2) {
                const requiredInputs = step1.querySelectorAll('[required]');
                for (const input of requiredInputs) {
                    if (!input.checkValidity()) {
                        input.reportValidity();
                        return;
                    }
                }
            }

            step1.classList.toggle('hidden', step !== 1);
            step2.classList.toggle('hidden', step !== 2);
            footer1.style.display = step === 1 ? 'flex' : 'none';
            footer2.style.display = step === 2 ? 'flex' : 'none';

            const dot1Circle = document.querySelector('#regStepDot1 span:first-child');
            const dot2Circle = document.getElementById('regStepDot2Circle');
            const dot2Label = document.getElementById('regStepDot2Label');
            if (step === 2) {
                dot1Circle.className = 'w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold bg-emerald-500 text-white';
                dot1Circle.innerHTML = '<i class="ph ph-check text-xs font-bold"></i>';
                dot2Circle.className = 'w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold bg-[#2F5F76] text-white';
                dot2Label.className = 'text-slate-900 text-[13px] font-semibold';
                initRegisterMap();
            } else {
                dot1Circle.className = 'w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold bg-[#2F5F76] text-white';
                dot1Circle.textContent = '1';
                dot2Circle.className = 'w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold bg-slate-200 text-slate-500';
                dot2Label.className = 'text-slate-400 text-[13px] font-semibold';
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        document.addEventListener("DOMContentLoaded", function() {
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
                                        
                                        if (registerMap) {
                                            registerMap.setView([newLat, newLng], 15);
                                            registerMarker.setLatLng([newLat, newLng]);
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
