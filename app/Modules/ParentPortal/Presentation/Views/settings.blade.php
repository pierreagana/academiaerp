@extends('ParentPortal::layout')

@section('title', 'Profil & Paramètres')

@section('content')

<!-- HEADER -->
<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Profil & Paramètres</h1>
    <p class="text-sm font-medium text-slate-500 mt-1">Gérez vos informations personnelles, la sécurité de votre compte et les préférences de notification.</p>
</div>

@if(session('success'))
<div class="p-4 mb-6 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-bold flex items-center gap-2">
    <span class="material-symbols-outlined text-[18px]">check_circle</span>
    <span>{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div class="p-4 mb-6 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs font-bold space-y-1">
    @foreach($errors->all() as $err)
        <p>&bull; {{ $err }}</p>
    @endforeach
</div>
@endif

<form action="{{ route('parent.settings.update') }}" method="POST">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        
        <!-- LEFT COLUMN: INFORMATIONS PERSONNELLES + COMPTES ENFANTS LIÉS (Col 7) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- INFORMATIONS PERSONNELLES CARD -->
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                            <span class="material-symbols-outlined text-[19px]">person</span>
                        </div>
                        <h2 class="text-sm font-extrabold text-slate-900">Informations Personnelles</h2>
                    </div>

                    <button type="button" class="text-xs font-bold text-blue-700 hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined text-[15px]">edit</span>
                        <span>Modifier</span>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Row 1: Nom & Prénom -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Nom de famille</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $lastName) }}" 
                                   class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs font-semibold rounded-2xl px-4 py-3 outline-none focus:border-blue-500 focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Prénom</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $firstName) }}" 
                                   class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs font-semibold rounded-2xl px-4 py-3 outline-none focus:border-blue-500 focus:bg-white transition">
                        </div>
                    </div>

                    <!-- Row 2: Email & Téléphone -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Adresse Email</label>
                            <input type="email" name="email" value="{{ old('email', $parent->email) }}" required
                                   class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs font-semibold rounded-2xl px-4 py-3 outline-none focus:border-blue-500 focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Numéro de Téléphone</label>
                            <input type="text" name="phone" value="{{ old('phone', $parent->phone) }}" required
                                   class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs font-semibold rounded-2xl px-4 py-3 outline-none focus:border-blue-500 focus:bg-white transition">
                        </div>
                    </div>

                    <!-- Row 3: Adresse Postale (autocomplétée via OpenStreetMap) -->
                    <div class="relative" x-data="{ suggestions: [] }">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Adresse Postale</label>
                        <input type="text" id="address-input" name="address" autocomplete="new-password"
                               value="{{ old('address', $parent->address) }}"
                               x-on:input.debounce.400ms="
                                   document.getElementById('address-lat').value = '';
                                   document.getElementById('address-lng').value = '';
                                   if ($event.target.value.trim().length < 3) { suggestions = []; return; }
                                   fetch('{{ route('parent.settings.address.search') }}?q=' + encodeURIComponent($event.target.value))
                                       .then(r => r.json())
                                       .then(data => { suggestions = data; })
                                       .catch(() => { suggestions = []; });
                               "
                               class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs font-semibold rounded-2xl px-4 py-3 outline-none focus:border-blue-500 focus:bg-white transition">
                        <input type="hidden" id="address-lat" name="latitude" value="{{ old('latitude', $parent->latitude) }}">
                        <input type="hidden" id="address-lng" name="longitude" value="{{ old('longitude', $parent->longitude) }}">

                        <div x-show="suggestions.length > 0" x-cloak x-on:click.outside="suggestions = []"
                             class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-2xl shadow-lg max-h-56 overflow-y-auto">
                            <template x-for="s in suggestions" :key="s.lat + ',' + s.lng">
                                <button type="button"
                                        x-on:click="
                                            document.getElementById('address-input').value = s.label;
                                            document.getElementById('address-lat').value = s.lat;
                                            document.getElementById('address-lng').value = s.lng;
                                            suggestions = [];
                                        "
                                        class="w-full text-left px-4 py-2.5 text-xs font-medium text-slate-700 hover:bg-blue-50 transition border-b border-slate-50 last:border-0"
                                        x-text="s.label">
                                </button>
                            </template>
                        </div>
                        <p class="text-[10.5px] text-slate-400 mt-1.5">Utilisée pour calculer les distances dans School Track — sélectionnez une suggestion dans la liste pour l'activer.</p>
                    </div>
                </div>
            </div>

            <!-- COMPTES ENFANTS LIÉS CARD -->
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                            <span class="material-symbols-outlined text-[19px]">group</span>
                        </div>
                        <h2 class="text-sm font-extrabold text-slate-900">Comptes Enfants Liés</h2>
                    </div>

                    <a href="{{ route('parent.children.add-form') }}" 
                       class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-xl transition">
                        <span class="material-symbols-outlined text-[16px]">add</span>
                        <span>Ajouter</span>
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($children as $child)
                    <div class="p-3.5 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white font-extrabold text-xs flex items-center justify-center shrink-0">
                                {{ substr($child->first_name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-xs font-extrabold text-slate-900 truncate">{{ $child->first_name }} {{ $child->last_name }}</h3>
                                <p class="text-[11px] font-medium text-slate-400 mt-0.5 truncate">
                                    Classe: {{ $child->academicClass?->name ?? 'Non assignée' }} &bull; ID: #{{ $child->roll_number }}
                                </p>
                            </div>
                        </div>

                        <div class="text-slate-400 hover:text-slate-600 cursor-pointer p-1">
                            <span class="material-symbols-outlined text-[18px]">more_vert</span>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-slate-400">
                        Aucun enfant n'est actuellement rattaché à votre compte.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: SÉCURITÉ + PRÉFÉRENCES + DOCUMENTS LÉGAUX (Col 5) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- SÉCURITÉ CARD -->
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                <div class="flex items-center gap-2.5 mb-5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[19px]">shield</span>
                    </div>
                    <h2 class="text-sm font-extrabold text-slate-900">Sécurité</h2>
                </div>

                <!-- Password Section -->
                <div class="mb-5 pb-5 border-b border-slate-100">
                    <h3 class="text-xs font-extrabold text-slate-900">Mot de passe</h3>
                    <p class="text-[11px] font-medium text-slate-400 mt-0.5 mb-3">
                        {{ $parent->password_changed_at ? 'Dernière modification ' . $parent->password_changed_at->diffForHumans() : 'Jamais modifié' }}
                    </p>

                    <button type="button" onclick="document.getElementById('password-modal').classList.remove('hidden')" 
                            class="w-full inline-flex items-center justify-center text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 py-2.5 px-4 rounded-xl transition">
                        Changer le mot de passe
                    </button>
                </div>

                <!-- 2FA Section -->
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xs font-extrabold text-slate-900">Authentification 2FA</h3>
                        <p class="text-[11px] font-medium text-slate-400 mt-0.5 leading-snug">Sécurisez l'accès à votre compte</p>
                    </div>

                    <!-- Custom Toggle Switch -->
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#061536]"></div>
                    </label>
                </div>
            </div>

            <!-- PRÉFÉRENCES CARD -->
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                <div class="flex items-center gap-2.5 mb-5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[19px]">notifications</span>
                    </div>
                    <h2 class="text-sm font-extrabold text-slate-900">Préférences</h2>
                </div>

                <div class="space-y-4">
                    <!-- Email Alerts Toggle -->
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs font-bold text-slate-800">Alertes par Email</span>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#061536]"></div>
                        </label>
                    </div>

                    <!-- SMS Notifications Toggle -->
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Notifications SMS</span>
                            <span class="text-[10.5px] font-medium text-slate-400">(Absences)</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#061536]"></div>
                        </label>
                    </div>

                    <!-- Push App Toggle -->
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs font-bold text-slate-800">Push sur l'Application</span>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" class="sr-only peer">
                            <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#061536]"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- DOCUMENTS LÉGAUX CARD -->
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                <div class="flex items-center gap-2.5 mb-5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[19px]">description</span>
                    </div>
                    <h2 class="text-sm font-extrabold text-slate-900">Documents Légaux</h2>
                </div>

                <div class="space-y-3">
                    @forelse($legalDocuments as $doc)
                    <div class="p-3 rounded-2xl border border-slate-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-xl {{ $doc['signed'] ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[17px]">{{ $doc['icon'] }}</span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-xs font-bold text-slate-900 truncate">{{ $doc['title'] }}</h3>
                                <p class="text-[10.5px] font-medium text-slate-400 mt-0.5">{{ $doc['signed_date'] ?? 'Non signé' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @unless($doc['signed'])
                            <button type="submit" formaction="{{ route('parent.legal-documents.sign', $doc['id']) }}" formmethod="POST"
                                    class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg bg-blue-100/70 text-[#061536] hover:bg-blue-200/70 transition">
                                Signer
                            </button>
                            @endunless
                            <a href="{{ $doc['url'] }}" target="_blank" class="text-slate-400 hover:text-blue-600 transition p-1">
                                <span class="material-symbols-outlined text-[17px]">download</span>
                            </a>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">Aucun document publié par l'établissement.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- BOTTOM ACTION BAR -->
    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <a href="{{ route('parent.dashboard') }}" 
           class="text-xs font-bold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 hover:bg-slate-50 px-5 py-3 rounded-2xl transition">
            Annuler
        </a>
        <button type="submit" 
                class="bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs px-6 py-3 rounded-2xl transition shadow-md shadow-blue-950/20">
            Enregistrer les modifications
        </button>
    </div>

</form>

<!-- MODAL CHANGER MOT DE PASSE -->
<div id="password-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-extrabold text-slate-900">Changer de mot de passe</h3>
            <button type="button" onclick="document.getElementById('password-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form action="{{ route('parent.settings.password') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Mot de passe actuel</label>
                <input type="password" name="current_password" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nouveau mot de passe</label>
                <input type="password" name="password" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Confirmer le nouveau mot de passe</label>
                <input type="password" name="password_confirmation" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold">
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('password-modal').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl">Annuler</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-[#061536] hover:bg-[#061536]/90 rounded-xl">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

@endsection
