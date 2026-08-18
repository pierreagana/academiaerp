@extends('SuperAdmin::layouts.app')

@section('content')
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl shadow-sm">
            <i class="ph ph-check-circle text-emerald-600 text-xl"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl shadow-sm">
            <i class="ph ph-warning-circle text-red-600 text-xl"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-[28px] font-extrabold text-[#111827]">Annuaire des Établissements</h2>
                    <p class="text-[15px] text-slate-600 mt-1">Gérez les écoles, collèges et lycées inscrits sur la plateforme.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="openAddSchoolModal()" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-900 transition shadow-sm">
                        <i class="ph ph-plus text-lg font-bold"></i> Ajouter une école
                    </button>
                </div>
            </div>

            <!-- Filters and IA Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Filtres Rapides -->
                <form method="GET" action="{{ route('superadmin.schools') }}" class="lg:col-span-2 bg-white rounded-[16px] border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-funnel text-slate-700 text-xl"></i>
                            <h3 class="font-bold text-slate-800 text-lg">Filtres Rapides</h3>
                        </div>
                        @if(request()->hasAny(['search', 'country', 'plan', 'status']))
                            <a href="{{ route('superadmin.schools') }}" class="text-xs text-red-600 font-semibold hover:underline">Réinitialiser les filtres</a>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Pays -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-[0.1em] mb-2">PAYS</label>
                            <div class="relative">
                                <select name="country" onchange="this.form.submit()" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium transition">
                                    <option value="">Tous les pays</option>
                                    <option value="Sénégal" {{ request('country') == 'Sénégal' ? 'selected' : '' }}>Sénégal</option>
                                    <option value="Côte d'Ivoire" {{ request('country') == "Côte d'Ivoire" ? 'selected' : '' }}>Côte d'Ivoire</option>
                                    <option value="Cameroun" {{ request('country') == 'Cameroun' ? 'selected' : '' }}>Cameroun</option>
                                    <option value="Mali" {{ request('country') == 'Mali' ? 'selected' : '' }}>Mali</option>
                                </select>
                                <i class="ph ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                            </div>
                        </div>
                        <!-- Forfait -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-[0.1em] mb-2">FORFAIT</label>
                            <div class="relative">
                                <select name="plan" onchange="this.form.submit()" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium transition">
                                    <option value="">Tous les forfaits</option>
                                    <option value="IA-Premium" {{ request('plan') == 'IA-Premium' ? 'selected' : '' }}>IA-Premium</option>
                                    <option value="Pro" {{ request('plan') == 'Pro' ? 'selected' : '' }}>Pro</option>
                                    <option value="Basique" {{ request('plan') == 'Basique' ? 'selected' : '' }}>Basique</option>
                                </select>
                                <i class="ph ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                            </div>
                        </div>
                        <!-- Statut -->
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-[0.1em] mb-2">STATUT</label>
                            <div class="relative">
                                <select name="status" onchange="this.form.submit()" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium transition">
                                    <option value="">Tous les statuts</option>
                                    <option value="actif" {{ request('status') == 'actif' ? 'selected' : '' }}>Actif</option>
                                    <option value="suspendu" {{ request('status') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                                </select>
                                <i class="ph ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Analyse IA -->
                <div class="bg-white rounded-[16px] border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2 text-purple-600 font-bold">
                                <i class="ph ph-trend-up text-xl"></i>
                                <span>Analyse IA</span>
                            </div>
                            <button class="text-slate-300 hover:text-slate-500 transition"><i class="ph ph-dots-three text-xl"></i></button>
                        </div>
                        <p class="text-slate-700 text-[15px] font-medium leading-relaxed">
                            <span class="font-extrabold text-slate-900">3 écoles</span> au Sénégal présentent des signes de sous-utilisation du forfait <span class="font-bold text-purple-600">IA-Premium</span> ce mois-ci.
                        </p>
                    </div>
                    <button class="w-full mt-6 py-2.5 rounded-lg border-2 border-purple-100 text-purple-600 font-bold text-xs tracking-wider uppercase hover:bg-purple-50 transition">
                        VOIR LE RAPPORT
                    </button>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-[16px] border border-slate-200 shadow-sm overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-[0.1em] border-b border-slate-100 bg-[#F8FAFC]">
                                <th class="py-4 px-6">ÉTABLISSEMENT</th>
                                <th class="py-4 px-4">LOCALISATION</th>
                                <th class="py-4 px-4">FORFAIT</th>
                                <th class="py-4 px-4 text-center">MEMBRES (É/E)</th>
                                <th class="py-4 px-4 text-center">STATUT</th>
                                <th class="py-4 px-6 text-right">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($schools as $school)
                            <tr class="hover:bg-slate-50/70 transition group">
                                <td class="py-5 px-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-[42px] h-[42px] rounded-lg bg-blue-50/50 border border-slate-100 flex items-center justify-center text-blue-900 font-bold text-xs shadow-sm overflow-hidden">
                                            @if($loop->index == 0)
                                                <!-- Mock image/logo placeholder from screenshot -->
                                                <div class="w-5 h-3.5 bg-white shadow-sm flex items-center justify-center text-[8px]">🏫</div>
                                            @elseif($loop->index == 1)
                                                <span class="text-[15px]">CS</span>
                                            @elseif($loop->index == 2)
                                                <div class="w-5 h-3.5 bg-white shadow-sm flex items-center justify-center text-[8px]">🏫</div>
                                            @else
                                                <span>{{ substr(preg_replace('/[^A-Z]/', '', ucwords($school->name)), 0, 2) ?: 'SC' }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('superadmin.schools.show', $school->id) }}" class="font-semibold text-slate-800 text-[15px] hover:text-blue-900 transition">{{ $school->name }}</a>
                                            <p class="text-[11px] font-bold text-slate-500 mt-0.5 uppercase tracking-wider">ID: {{ str_replace('#', 'EDU-', $school->id) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <span class="text-lg leading-none">
                                            @if(str_contains($school->region, 'Sénégal'))
                                            🇸🇳
                                            @elseif(str_contains($school->region, 'CI') || str_contains($school->region, 'Ivoire'))
                                            🇨🇮
                                            @elseif(str_contains($school->region, 'Cameroun'))
                                            🇨🇲
                                            @elseif(str_contains($school->region, 'Mali'))
                                            🇲🇱
                                            @elseif(str_contains($school->region, 'Gabon'))
                                            🇬🇦
                                            @else
                                            🌍
                                            @endif
                                        </span>
                                        <span class="text-slate-700 font-medium">{{ $school->region }}</span>
                                    </div>
                                </td>
                                <td class="py-5 px-4">
                                    @if(str_contains(strtolower($school->package), 'premium'))
                                        <span class="inline-flex items-center gap-1.5 bg-purple-100 text-purple-700 font-semibold px-3 py-1 rounded-full text-xs">
                                            <i class="ph ph-sparkle text-purple-600"></i> IA-Premium
                                        </span>
                                    @elseif(str_contains(strtolower($school->package), 'starter') || str_contains(strtolower($school->package), 'pro'))
                                        <span class="inline-flex items-center bg-blue-100 text-[#0F3294] font-semibold px-3 py-1 rounded-full text-xs">
                                            Pro
                                        </span>
                                    @else
                                        <span class="inline-flex items-center bg-slate-100 text-slate-600 font-semibold px-3 py-1 rounded-full text-xs">
                                            Basique
                                        </span>
                                    @endif
                                </td>
                                <td class="py-5 px-4 text-center">
                                    <p class="font-bold text-slate-900 text-[15px]">{{ number_format($school->studentsCount, 0, ',', ',') }}</p>
                                    <p class="text-[12px] text-slate-500 font-medium mt-0.5">{{ ceil($school->studentsCount / 15) }} ens.</p>
                                </td>
                                <td class="py-5 px-4 text-center">
                                    @if(strtolower($school->status) == 'actif')
                                        <span class="inline-flex items-center bg-emerald-50 text-emerald-600 text-[12px] font-semibold px-2.5 py-0.5 rounded-full">Actif</span>
                                    @elseif(strtolower($school->status) == 'en attente')
                                        <span class="inline-flex items-center bg-amber-50 text-amber-700 text-[12px] font-semibold px-2.5 py-0.5 rounded-full">En attente</span>
                                    @else
                                        <span class="inline-flex items-center bg-slate-100 text-slate-600 text-[12px] font-semibold px-2.5 py-0.5 rounded-full">Suspendu</span>
                                    @endif
                                </td>
                                <td class="py-5 px-6 text-right relative">
                                    <div class="relative inline-block text-left">
                                        <button type="button" onclick="toggleSchoolDropdown(event, 'school-dropdown-{{ $school->id }}')" class="w-8 h-8 rounded-lg bg-slate-100/80 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition focus:outline-none ml-auto">
                                            <i class="ph ph-dots-three-vertical text-lg font-bold"></i>
                                        </button>

                                        <div id="school-dropdown-{{ $school->id }}" class="school-action-dropdown hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-30 divide-y divide-slate-100 text-left">
                                            <div class="py-1">
                                                <a href="{{ route('superadmin.schools.show', $school->id) }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                                    <i class="ph ph-eye text-base text-blue-900"></i> Voir les détails
                                                </a>
                                                <button type="button" onclick="openEditSchoolModal({{ json_encode($school) }})" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition text-left">
                                                    <i class="ph ph-pencil-simple text-base text-indigo-600"></i> Modifier l'école
                                                </button>
                                            </div>
                                            <div class="py-1">
                                                <a href="{{ route('superadmin.packages') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                                    <i class="ph ph-sparkle text-base text-purple-600"></i> Changer forfait
                                                </a>
                                                @if(strtolower($school->status) == 'actif')
                                                    <form action="{{ route('superadmin.schools.suspend', $school->id) }}" method="POST" class="w-full">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-50 transition text-left">
                                                            <i class="ph ph-pause-circle text-base text-amber-600"></i> Suspendre
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('superadmin.schools.activate', $school->id) }}" method="POST" class="w-full">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 transition text-left">
                                                            <i class="ph ph-check-circle text-base text-emerald-600"></i> Activer
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                            <div class="py-1">
                                                <form action="{{ route('superadmin.schools.destroy', $school->id) }}" method="POST" onsubmit="return confirm('Supprimer « {{ $school->name }} » ? Cette action est réversible (archivage).');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition text-left">
                                                        <i class="ph ph-trash text-base text-red-500"></i> Supprimer (archiver)
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Footer -->
                <div class="px-6 py-4 border-t border-slate-100 bg-[#FCFDFE]">
                    {{ $schools->links() }}
                </div>
            </div>

    <!-- Modal : Ajouter une École -->
    <div id="addSchoolModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-buildings text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Ajouter un Établissement</h3>
                        <p class="text-xs text-blue-200 font-medium">Enregistrez un nouvel établissement dans le réseau</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddSchoolModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('superadmin.schools.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nom de l'Institution *</label>
                    <input type="text" name="name" required placeholder="Ex: Lycée d'Excellence Dakar" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Type d'Établissement</label>
                        <select name="type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                            <option value="Secondaire (Lycée)">Secondaire (Lycée)</option>
                            <option value="Collège">Collège</option>
                            <option value="Primaire">Primaire</option>
                            <option value="Complexe Scolaire">Complexe Scolaire</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Forfait SaaS</label>
                        <select name="plan_name" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                            <option value="Pro">Pro</option>
                            <option value="IA-Premium">IA-Premium</option>
                            <option value="Starter">Starter</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Ville & Pays</label>
                        <input type="text" name="location" placeholder="Ex: Dakar, Sénégal" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nombre d'Élèves Estimé</label>
                        <input type="number" name="students_count" placeholder="Ex: 850" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email de Contact</label>
                        <input type="email" name="contact_email" placeholder="contact@lycee.sn" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Téléphone</label>
                        <input type="text" name="contact_phone" placeholder="+221 33 800 00 00" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeAddSchoolModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                        Annuler
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#031C5B] text-white text-xs font-bold hover:bg-blue-900 transition shadow-sm flex items-center gap-2">
                        <i class="ph ph-check text-sm"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal : Modifier une École -->
    <div id="editSchoolModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-pencil-simple text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Modifier l'Établissement</h3>
                        <p class="text-xs text-blue-200 font-medium">Mettre à jour les informations de la structure</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditSchoolModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form id="editSchoolForm" action="" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nom de l'Institution *</label>
                    <input type="text" id="edit_school_name" name="name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Type d'Établissement</label>
                        <select id="edit_school_type" name="type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                            <option value="Secondaire (Lycée)">Secondaire (Lycée)</option>
                            <option value="Collège">Collège</option>
                            <option value="Primaire">Primaire</option>
                            <option value="Complexe Scolaire">Complexe Scolaire</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Forfait SaaS</label>
                        <select id="edit_school_plan" name="plan_name" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                            <option value="Pro">Pro</option>
                            <option value="IA-Premium">IA-Premium</option>
                            <option value="Starter">Starter</option>
                            <option value="Basique">Basique</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Ville / Emplacement</label>
                        <input type="text" id="edit_school_location" name="location" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Statut</label>
                        <select id="edit_school_status" name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                            <option value="actif">Actif</option>
                            <option value="en attente">En attente</option>
                            <option value="suspendu">Suspendu</option>
                        </select>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeEditSchoolModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                        Annuler
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#031C5B] text-white text-xs font-bold hover:bg-blue-900 transition shadow-sm flex items-center gap-2">
                        <i class="ph ph-check text-sm"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddSchoolModal() {
            const modal = document.getElementById('addSchoolModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }
        function closeAddSchoolModal() {
            const modal = document.getElementById('addSchoolModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function openEditSchoolModal(school) {
            const modal = document.getElementById('editSchoolModal');
            const form = document.getElementById('editSchoolForm');
            if (modal && form && school) {
                form.action = '/superadmin/schools/' + school.id;
                document.getElementById('edit_school_name').value = school.name || '';
                document.getElementById('edit_school_type').value = school.type || 'Secondaire (Lycée)';
                document.getElementById('edit_school_plan').value = school.package || school.plan_name || 'Pro';
                document.getElementById('edit_school_location').value = school.region || school.location || '';
                document.getElementById('edit_school_status').value = (school.status || 'actif').toLowerCase();
                modal.classList.remove('hidden');
            }
        }

        function closeEditSchoolModal() {
            const modal = document.getElementById('editSchoolModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function toggleSchoolDropdown(event, dropdownId) {
            event.stopPropagation();
            const allDropdowns = document.querySelectorAll('.school-action-dropdown');
            allDropdowns.forEach(d => {
                if (d.id !== dropdownId) {
                    d.classList.add('hidden');
                }
            });

            const dropdown = document.getElementById(dropdownId);
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }

        document.addEventListener('click', function() {
            const allDropdowns = document.querySelectorAll('.school-action-dropdown');
            allDropdowns.forEach(d => d.classList.add('hidden'));
        });
    </script>
@endsection
