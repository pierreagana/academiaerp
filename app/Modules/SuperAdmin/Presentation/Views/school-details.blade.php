@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-[28px] font-extrabold text-[#111827]">{{ $school->name }}</h2>
                @if(strtolower($school->status ?? 'actif') == 'actif')
                    <span class="inline-flex items-center bg-emerald-50 text-emerald-600 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200">
                        <i class="ph ph-check-circle mr-1"></i> Actif
                    </span>
                @elseif(strtolower($school->status ?? '') == 'en attente')
                    <span class="inline-flex items-center bg-amber-50 text-amber-700 text-xs font-bold px-3 py-1 rounded-full border border-amber-200">
                        <i class="ph ph-clock mr-1"></i> En attente
                    </span>
                @else
                    <span class="inline-flex items-center bg-slate-100 text-slate-600 text-xs font-bold px-3 py-1 rounded-full border border-slate-200">
                        <i class="ph ph-pause-circle mr-1"></i> Suspendu
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-3 mt-2 text-sm text-slate-500 font-medium">
                <span><i class="ph ph-hash text-slate-400"></i> ID: EDU-{{ str_pad($school->id, 4, '0', STR_PAD_LEFT) }}</span>
                <span>•</span>
                <span><i class="ph ph-map-pin text-slate-400"></i> {{ $school->location ?? 'Dakar, Sénégal' }}</span>
                <span>•</span>
                <span><i class="ph ph-sparkle text-purple-600"></i> Forfait: {{ $school->plan_name ?? $school->package ?? 'Pro' }}</span>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('superadmin.schools') }}" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition shadow-xs">
                <i class="ph ph-arrow-left text-lg font-bold"></i> Retour à l'annuaire
            </a>
        </div>
    </div>

    <!-- Overview Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">ÉLÈVES & ENSEIGNANTS</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#031C5B] flex items-center justify-center font-bold">
                    <i class="ph ph-student text-lg"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold text-slate-900">{{ number_format($school->students_count ?? 850, 0, ',', ' ') }}</h3>
            <p class="text-xs text-slate-500 font-medium mt-1">~{{ ceil(($school->students_count ?? 850) / 15) }} enseignants enregistrés</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">STOCKAGE CLOUD</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold">
                    <i class="ph ph-cloud-arrow-up text-lg"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold text-slate-900">{{ $school->storage_used_gb ?? '14.2' }} GB</h3>
            <p class="text-xs text-emerald-600 font-medium mt-1">Quota S3 normal (max 100 GB)</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">REVENU RECURRENT (MRR)</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                    <i class="ph ph-currency-circle-dollar text-lg"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold text-slate-900">150 000 {{ $systemCurrency ?? 'FCFA' }}</h3>
            <p class="text-xs text-slate-500 font-medium mt-1">Facturation annuelle renouvelable</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">SCORE D'ACTIVITÉ</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold">
                    <i class="ph ph-chart-line-up text-lg"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold text-purple-700">96%</h3>
            <p class="text-xs text-slate-500 font-medium mt-1">Connexions quotidiennes élevées</p>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        
        <!-- Left Column -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            
            <!-- Informations Générales Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 lg:p-8 shadow-xs">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <i class="ph ph-buildings text-2xl text-[#031C5B]"></i>
                        <h3 class="text-lg font-extrabold text-[#031C5B]">Informations Générales</h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8 text-sm">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Nom Officiel</p>
                        <p class="font-bold text-slate-900 text-base">{{ $school->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Type d'Établissement</p>
                        <p class="font-semibold text-slate-800">{{ $school->type ?? 'Secondaire (Lycée)' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Secteur / Statut Juridique</p>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ ($school->sector ?? 'Privé') === 'Public' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : (($school->sector ?? 'Privé') === 'Semi-privé' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-blue-50 text-[#031C5B] border border-blue-200') }}">
                            <i class="ph ph-buildings mr-1.5"></i> {{ $school->sector ?? 'Privé' }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Régime Linguistique</p>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ ($school->is_bilingual ?? false) || str_contains(strtolower($school->language_regime ?? ''), 'bilingue') ? 'bg-purple-50 text-purple-800 border border-purple-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                            <i class="ph ph-translate mr-1.5"></i> {{ $school->language_regime ?? (($school->is_bilingual ?? false) ? 'Bilingue' : 'Monolingue (Français)') }}
                        </span>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1.5">Niveaux & Ordres d'Enseignement</p>
                        <div class="flex flex-wrap gap-1.5">
                            @php
                                $levels = is_array($school->levels ?? null) ? $school->levels : (is_string($school->levels ?? null) ? json_decode($school->levels, true) : []);
                            @endphp
                            @if(!empty($levels))
                                @foreach($levels as $lvl)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                        <i class="ph ph-graduation-cap mr-1 text-slate-500"></i> {{ $lvl }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-xs text-slate-400 italic">Non spécifié</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Ville & Pays</p>
                        <p class="font-semibold text-slate-800 flex items-center gap-1.5">
                            <i class="ph ph-map-pin text-slate-400"></i> {{ $school->location ?? 'Dakar, Sénégal' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Domaine Personnalisé</p>
                        <p class="font-semibold text-blue-700 font-mono text-xs">{{ $school->domain ?? strtolower(str_replace(' ', '', $school->name)) . '.agana.school' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Email de Contact</p>
                        <p class="font-semibold text-slate-800">{{ $school->contact_email ?? 'contact@school.sn' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Téléphone Direct</p>
                        <p class="font-semibold text-slate-800">{{ $school->contact_phone ?? '+221 33 800 00 00' }}</p>
                    </div>
                </div>
            </div>

            <!-- Équipements & Infrastructures Scolaires -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 lg:p-8 shadow-xs">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <i class="ph ph-buildings text-2xl text-[#031C5B]"></i>
                        <div>
                            <h3 class="text-lg font-extrabold text-[#031C5B]">Équipements & Services Déclarés</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Commodités sélectionnées pour cet établissement</p>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('editFacilitiesModal').classList.remove('hidden')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                        <i class="ph ph-pencil-simple text-sm"></i> Modifier les équipements
                    </button>
                </div>

                @php
                    $declaredFacilities = $schoolModel ? $schoolModel->facilitiesList : collect();
                @endphp

                @if($declaredFacilities->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($declaredFacilities as $facility)
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-blue-50 text-[#2F5F76] flex items-center justify-center text-lg shrink-0">
                                    <i class="ph {{ $facility->icon }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 text-xs truncate">{{ $facility->name }}</p>
                                    <p class="text-[10.5px] text-slate-400 truncate">{{ $facility->category }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center">
                        <i class="ph ph-buildings text-3xl text-slate-300 mb-2"></i>
                        <p class="text-xs text-slate-500 font-semibold">Aucun équipement spécifique déclaré pour le moment.</p>
                        <button type="button" onclick="document.getElementById('editFacilitiesModal').classList.remove('hidden')"
                                class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-[#2F5F76] hover:underline">
                            + Configurer les équipements
                        </button>
                    </div>
                @endif
            </div>

            <!-- Modules SaaS Actifs -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 lg:p-8 shadow-xs">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <i class="ph ph-puzzle-piece text-2xl text-[#031C5B]"></i>
                        <h3 class="text-lg font-extrabold text-[#031C5B]">Modules SaaS Activés</h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($activePackageFeatures as $feature)
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 text-[#031C5B] flex items-center justify-center font-bold text-lg">
                                <i class="ph ph-check-circle"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 text-sm">{{ $feature }}</p>
                                <p class="text-xs text-emerald-600 font-semibold">Inclus dans {{ $schoolModel->plan_name ?? 'le forfait' }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 sm:col-span-2">Aucun forfait actif reconnu pour cet établissement.</p>
                    @endforelse

                    @foreach($approvedExtensionModules as $extension)
                        <div class="p-4 rounded-xl bg-slate-50 border border-purple-200 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-lg">
                                <i class="ph ph-plus-circle"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 text-sm">{{ $extension }}</p>
                                <p class="text-xs text-purple-700 font-semibold">Extension approuvée</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Sidebar Column -->
        <div class="lg:col-span-4 flex flex-col gap-6">
            
            <!-- Quick Actions Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <h3 class="text-base font-extrabold text-slate-900 mb-4">Actions Rapides</h3>

                <div class="space-y-2.5">
                    <a href="{{ route('superadmin.packages') }}" class="w-full flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-xs text-slate-800 transition">
                        <i class="ph ph-sparkle text-lg text-purple-600"></i> Changer de forfait SaaS
                    </a>
                    
                    <button type="button" class="w-full flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-xs text-slate-800 transition">
                        <i class="ph ph-key text-lg text-blue-700"></i> Réinitialiser accès admin
                    </button>

                    <form action="{{ route('superadmin.schools.destroy', $school->id ?? 1) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet établissement ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl border border-red-200 bg-red-50/50 hover:bg-red-100/50 font-bold text-xs text-red-700 transition">
                            <i class="ph ph-trash text-lg text-red-600"></i> Supprimer la structure
                        </button>
                    </form>
                </div>
            </div>

            <!-- Groupe / Fondateur Card -->
            @if($schoolModel)
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <h3 class="text-base font-extrabold text-slate-900 mb-1">Groupe &amp; Fondateur</h3>
                @if($schoolModel->group)
                    <p class="text-xs text-slate-500 mb-4">Rattachée au groupe <strong>{{ $schoolModel->group->name }}</strong>, fondé par <strong>{{ $schoolModel->group->founder->name ?? '—' }}</strong>.</p>
                @else
                    <p class="text-xs text-slate-500 mb-4">Cette école est gérée en simple "Directeur" — aucun groupe multi-écoles.</p>
                @endif

                @if(!$allowsMultiSuccursales)
                    <div class="p-3 rounded-lg bg-amber-50 border border-amber-100 text-amber-800 text-[11.5px] mb-1">
                        <i class="ph-fill ph-warning-circle"></i>
                        Le forfait actuel (<strong>{{ $schoolModel->plan_name ?? '—' }}</strong>) n'inclut pas le module « Multi-Succursales ». Changez de forfait pour pouvoir désigner un fondateur.
                    </div>
                    <a href="{{ route('superadmin.packages') }}" class="w-full flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-[12.5px] py-2.5 rounded-lg transition">
                        <i class="ph-bold ph-arrow-up"></i> Changer de forfait
                    </a>
                @else
                <form action="{{ route('superadmin.schools.group.update', $schoolModel->id) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Désigner comme fondateur</label>
                        <select name="founder_user_id" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[12.5px] rounded-lg px-3 py-2 outline-none focus:border-[#031C5B]">
                            <option value="">— Aucun (retirer du groupe) —</option>
                            @foreach($schoolAdminUsers as $u)
                                <option value="{{ $u->id }}" {{ $schoolModel->group && $schoolModel->group->founder_user_id == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Ou rattacher à un groupe existant</label>
                        <select name="school_group_id" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[12.5px] rounded-lg px-3 py-2 outline-none focus:border-[#031C5B]">
                            <option value="">— Nouveau groupe —</option>
                            @foreach($schoolGroups as $g)
                                <option value="{{ $g->id }}" {{ $schoolModel->school_group_id == $g->id ? 'selected' : '' }}>{{ $g->name }} (fondateur : {{ $g->founder->name ?? '—' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-[#031C5B] hover:bg-blue-900 text-white font-bold text-[12.5px] py-2.5 rounded-lg transition">Mettre à jour</button>
                </form>
                @endif
            </div>
            @endif

            <!-- Abonnement & Facturation Widget -->
            <div class="bg-[#031C5B] text-white rounded-2xl p-6 shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-blue-200 uppercase tracking-wider">STATUS FACTURATION</span>
                    <i class="ph ph-shield-check text-2xl text-emerald-400"></i>
                </div>
                <h4 class="text-xl font-bold mb-1">{{ $school->plan_name ?? 'Forfait Pro' }}</h4>
                <p class="text-xs text-blue-200 font-medium mb-4">Prochain renouvellement: {{ now()->addMonths(8)->format('d/m/Y') }}</p>
                <div class="pt-3 border-t border-white/10 flex items-center justify-between text-xs">
                    <span class="text-blue-200">Statut du compte:</span>
                    <span class="font-bold text-emerald-300">À jour</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal : Modifier les Équipements de l'Établissement -->
    <div id="editFacilitiesModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-buildings text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Équipements & Services</h3>
                        <p class="text-xs text-blue-200 font-medium">Sélectionnez les commodités de l'établissement</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('editFacilitiesModal').classList.add('hidden')" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <form action="{{ route('superadmin.schools.update', $school->id) }}" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-72 overflow-y-auto p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    @foreach($facilities ?? [] as $facility)
                        <label class="flex items-center gap-2.5 p-2.5 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-[#2F5F76] transition text-xs font-semibold text-slate-800">
                            <input type="checkbox" name="facilities[]" value="{{ $facility->id }}"
                                   {{ in_array($facility->id, $schoolFacilityIds ?? []) ? 'checked' : '' }}
                                   class="rounded text-[#2F5F76] focus:ring-[#2F5F76]">
                            <i class="ph {{ $facility->icon }} text-lg text-[#2F5F76] shrink-0"></i>
                            <span class="truncate">{{ $facility->name }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('editFacilitiesModal').classList.add('hidden')" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                        Annuler
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#031C5B] text-white text-xs font-bold hover:bg-blue-900 transition shadow-sm flex items-center gap-2">
                        <i class="ph ph-check text-sm"></i> Enregistrer les équipements
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
