@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[32px] font-extrabold text-[#111827]">Licences & Abonnements Établissements</h2>
            <p class="text-[15px] text-slate-600 mt-1 max-w-3xl">Suivi opérationnel des licences actives déployées par établissement, dates de renouvellement et souscriptions d'écoles.</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <button type="button" onclick="openAddPackageModal()" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-900 transition shadow-sm cursor-pointer">
                <i class="ph ph-plus-circle text-lg font-bold"></i> Nouveau Forfait
            </button>
        </div>
    </div>

    <!-- Pricing Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        @foreach($packages as $package)
        @php
            $isPopular = $package->is_popular ?? false;
            $isPremium = stripos($package->name, 'Premium') !== false || stripos($package->name, 'Enterprise') !== false || stripos($package->name, 'Pro') !== false;
            
            $borderColor = $isPremium ? 'border-purple-200' : 'border-slate-200';
            $ringClass = $isPopular ? 'ring-2 ring-[#031C5B]/10 shadow-xl' : '';
            $mtClass = $isPopular ? '' : 'mt-2';
            $btnBg = $isPremium ? 'bg-[#6F1DDF] hover:bg-purple-700 text-white' : ($isPopular ? 'bg-[#031C5B] text-white hover:bg-blue-900' : 'bg-white border-2 border-slate-200 text-slate-700 hover:border-[#031C5B] hover:text-[#031C5B]');
            $pillBg = $isPremium ? 'bg-[#F1E5FF] text-[#6F1DDF]' : ($isPopular ? 'bg-[#E1EDFF] text-[#4B79DB]' : 'bg-slate-100 text-slate-500');
        @endphp
        <!-- Package Card -->
        <div class="bg-white rounded-2xl border {{ $borderColor }} shadow-md flex flex-col pt-6 px-6 pb-6 relative transition hover:shadow-xl {{ $ringClass }} {{ $mtClass }}">
            @if($isPopular)
            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                <span class="bg-[#031C5B] text-white text-[10px] font-bold uppercase tracking-widest px-4 py-1 rounded-full shadow-md whitespace-nowrap">Le plus populaire</span>
            </div>
            @endif

            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-md {{ $pillBg }}">{{ $package->name }}</span>
                <div class="flex items-center gap-1">
                    <form action="{{ route('superadmin.packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce forfait ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-slate-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition" title="Supprimer">
                            <i class="ph ph-trash text-lg"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="mb-6">
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-extrabold text-slate-900">{{ number_format($package->price, 0, ',', ' ') }}</span>
                    <span class="text-sm font-semibold text-slate-600">{{ $systemCurrency ?? 'FCFA' }}</span>
                    <span class="text-xs font-medium text-slate-400">/ an</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    @if(!empty($package->maxStudents))
                        Jusqu'à {{ number_format($package->maxStudents, 0, ',', ' ') }} élèves
                    @else
                        Nombre d'élèves illimité
                    @endif
                </p>
            </div>

            <!-- Features List -->
            <div class="flex-1 space-y-3 mb-6 border-t border-slate-100 pt-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Modules & Fonctionnalités Incluses :</p>
                @php
                    $featuresList = is_array($package->features) ? $package->features : json_decode($package->features ?? '[]', true);
                @endphp
                @if(!empty($featuresList) && is_array($featuresList))
                    @foreach($featuresList as $feature)
                    <div class="flex items-start gap-2.5 text-xs text-slate-700">
                        <i class="ph ph-check-circle text-emerald-500 text-base shrink-0 mt-0.5 font-bold"></i>
                        <span>{{ is_array($feature) ? ($feature['text'] ?? '') : $feature }}</span>
                    </div>
                    @endforeach
                @else
                    <p class="text-xs text-slate-400 italic">Fonctionnalités standards incluses</p>
                @endif
            </div>

            <button type="button" onclick='openEditPackageModal(@json($package->id), @json($package->name), @json($package->description), @json($package->price), @json($package->billing_cycle), @json($package->maxStudents), @json($package->maxStorageGb), @json($package->is_popular), @json($featuresList))' class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-center transition shadow-xs {{ $btnBg }}">
                Gérer ce forfait
            </button>
        </div>
        @endforeach
    </div>

    <!-- Active School Licenses Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Souscriptions & Licences Actives par Établissement</h3>
                <p class="text-xs text-slate-500 mt-0.5">Suivi en direct des déploiements et états des souscriptions des écoles client</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg border border-emerald-200">
                    <i class="ph ph-shield-check font-bold mr-1"></i> Licences conformes
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-6">Établissement / École</th>
                        <th class="py-3.5 px-4">Localisation</th>
                        <th class="py-3.5 px-4">Forfait Actif</th>
                        <th class="py-3.5 px-4">Date Renouvellement</th>
                        <th class="py-3.5 px-4">Statut Licence</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($schools as $school)
                        @php
                            $st = $school->status ?? 'actif';
                            $isActif = $st === 'actif' || $st === 'active';
                            $badgeBg = $isActif ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200';
                            $dotBg = $isActif ? 'bg-emerald-500' : 'bg-amber-500';
                            $pkgName = $school->plan_name ?? '—';
                            $renewalDate = $school->subscription_renewal_date;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-bold text-slate-900">
                                {{ $school->name }}
                                <span class="block text-[11px] font-normal text-slate-400">{{ number_format($school->students_count ?? 0, 0, ',', ' ') }} Élèves inscrit(s)</span>
                            </td>
                            <td class="py-4 px-4 font-medium text-slate-600">{{ $school->location ?? '—' }}</td>
                            <td class="py-4 px-4">
                                <span class="font-bold text-[#031C5B] bg-blue-50 px-2.5 py-1 rounded-md border border-blue-100">{{ $pkgName }}</span>
                            </td>
                            <td class="py-4 px-4 font-medium">{{ $renewalDate ? $renewalDate->format('d M Y') : 'Non défini' }}</td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $badgeBg }} border">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotBg }}"></span> {{ ucfirst($st) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button type="button" onclick='openManageLicenseModal(@json($school->id), @json($school->name), @json($school->plan_name), @json($st), @json($renewalDate ? $renewalDate->format("Y-m-d") : null))' class="px-3.5 py-1.5 text-xs font-bold text-[#031C5B] bg-blue-50 hover:bg-[#031C5B] hover:text-white rounded-lg transition shadow-2xs cursor-pointer">
                                    <i class="ph ph-sliders font-bold mr-1"></i> Gérer
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 px-6 text-center text-slate-400 text-[13px]">Aucun établissement enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal : Nouveau Forfait SaaS -->
    <div id="addPackageModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-package text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Nouveau Forfait SaaS</h3>
                        <p class="text-xs text-blue-200 font-medium">Définissez une nouvelle offre commerciale</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddPackageModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('superadmin.packages.store') }}" method="POST" class="p-6 space-y-4 max-h-[82vh] overflow-y-auto">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nom du Forfait *</label>
                    <input type="text" name="name" required placeholder="Ex: Pro Excellence / Enterprise Custom" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Prix Annuel ({{ $systemCurrency ?? 'FCFA' }}) *</label>
                        <input type="number" name="price" required placeholder="Ex: 500000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Badge Populaire</label>
                        <select name="is_popular" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                            <option value="0">Non</option>
                            <option value="1">Oui (Recommandé)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Limite Élèves</label>
                        <input type="number" name="max_students" placeholder="Ex: 1000 (0 = Illimité)" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Espace Stockage (GB)</label>
                        <input type="number" name="max_storage_gb" placeholder="Ex: 100" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                    </div>
                </div>

                <!-- Activation des Options & Modules Inclus (Dynamique DB) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-slate-800">Modules & Options Inclus à Activer pour ce Forfait *</label>
                        <div class="flex items-center gap-2 text-[11px]">
                            <button type="button" onclick="toggleAllModalCheckboxes(this, true)" class="text-indigo-600 font-bold hover:underline cursor-pointer">Tout cocher</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" onclick="toggleAllModalCheckboxes(this, false)" class="text-slate-500 font-medium hover:underline cursor-pointer">Tout décocher</button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-slate-50/90 p-3.5 rounded-xl border border-slate-200/80 max-h-56 overflow-y-auto">
                        @if(isset($availableModules) && count($availableModules) > 0)
                            @foreach($availableModules as $mod)
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-slate-200/70 hover:border-indigo-500 hover:shadow-xs transition cursor-pointer">
                                    <input type="checkbox" name="features[]" value="{{ $mod->name }}" checked class="module-chk rounded border-slate-300 text-[#031C5B] focus:ring-0">
                                    <span class="text-xs font-semibold text-slate-800 leading-tight">{{ $mod->name }}</span>
                                </label>
                            @endforeach
                        @else
                            <p class="text-xs text-slate-400 p-2">Aucun module enregistré.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Description / Note interne</label>
                    <textarea name="description" rows="2" placeholder="Description courte de l'offre..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition resize-none"></textarea>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeAddPackageModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition cursor-pointer">
                        Annuler
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#031C5B] text-white text-xs font-bold hover:bg-blue-900 transition shadow-sm flex items-center gap-2 cursor-pointer">
                        <i class="ph ph-check text-sm"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal : Modifier un Forfait SaaS -->
    <div id="editPackageModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-pencil-simple text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Modifier le Forfait</h3>
                        <p class="text-xs text-blue-200 font-medium" id="editModalPackageName">Forfait sélectionné</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditPackageModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form id="editPackageForm" method="POST" class="p-6 space-y-4 max-h-[82vh] overflow-y-auto">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nom du Forfait *</label>
                    <input type="text" name="name" id="editPkgName" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Prix Annuel ({{ $systemCurrency ?? 'FCFA' }}) *</label>
                        <input type="number" name="price" id="editPkgPrice" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Badge Populaire</label>
                        <select name="is_popular" id="editPkgPopular" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                            <option value="0">Non</option>
                            <option value="1">Oui (Recommandé)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Limite Élèves</label>
                        <input type="number" name="max_students" id="editPkgMaxStudents" placeholder="0 = Illimité" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Espace Stockage (GB)</label>
                        <input type="number" name="max_storage_gb" id="editPkgMaxStorage" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-slate-800">Modules & Options Inclus à Activer pour ce Forfait</label>
                        <div class="flex items-center gap-2 text-[11px]">
                            <button type="button" onclick="toggleAllModalCheckboxes(this, true)" class="text-indigo-600 font-bold hover:underline cursor-pointer">Tout cocher</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" onclick="toggleAllModalCheckboxes(this, false)" class="text-slate-500 font-medium hover:underline cursor-pointer">Tout décocher</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-slate-50/90 p-3.5 rounded-xl border border-slate-200/80 max-h-56 overflow-y-auto">
                        @if(isset($availableModules) && count($availableModules) > 0)
                            @foreach($availableModules as $mod)
                                <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-slate-200/70 hover:border-indigo-500 hover:shadow-xs transition cursor-pointer">
                                    <input type="checkbox" name="features[]" value="{{ $mod->name }}" class="edit-module-chk module-chk rounded border-slate-300 text-[#031C5B] focus:ring-0">
                                    <span class="text-xs font-semibold text-slate-800 leading-tight">{{ $mod->name }}</span>
                                </label>
                            @endforeach
                        @else
                            <p class="text-xs text-slate-400 p-2">Aucun module enregistré.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Description / Note interne</label>
                    <textarea name="description" id="editPkgDescription" rows="2" placeholder="Description courte de l'offre..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition resize-none"></textarea>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeEditPackageModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition cursor-pointer">
                        Annuler
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#031C5B] text-white text-xs font-bold hover:bg-blue-900 transition shadow-sm flex items-center gap-2 cursor-pointer">
                        <i class="ph ph-check text-sm"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal : Gérer la Licence d'un Établissement -->
    <div id="manageLicenseModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-buildings text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold">Gestion de Licence École</h3>
                        <p class="text-xs text-blue-200 font-medium" id="modalSchoolNameDisplay">Établissement sélectionné</p>
                    </div>
                </div>
                <button type="button" onclick="closeManageLicenseModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="manageLicenseForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Changer le Forfait Actif</label>
                        <select name="plan_name" id="modalCurrentPackage" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                            <option value="">— Aucun —</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->name }}">{{ $pkg->name }} ({{ number_format($pkg->price, 0, ',', ' ') }} {{ $systemCurrency ?? 'FCFA' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Statut de la Licence</label>
                        <select name="status" id="modalCurrentStatus" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                            <option value="actif">Actif (Accès total)</option>
                            <option value="suspendu">Suspendu (Impayé / Blocage)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Date d'Échéance / Renouvellement</label>
                    <input type="date" name="subscription_renewal_date" id="modalRenewalDate" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>

                <p class="text-[11px] text-slate-400 leading-relaxed">
                    Les modules inclus sont ceux définis par le forfait sélectionné ci-dessus (voir <a href="{{ route('superadmin.modules') }}" class="underline hover:text-slate-600">Gestion des Modules</a>).
                </p>

                <!-- Modal Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('superadmin.schools') }}" class="text-xs font-bold text-[#031C5B] hover:underline flex items-center gap-1">
                        <i class="ph ph-eye font-bold"></i> Voir la fiche école complète
                    </a>
                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <button type="button" onclick="closeManageLicenseModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition cursor-pointer">
                            Annuler
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[#031C5B] text-white text-xs font-bold hover:bg-blue-900 transition shadow-sm flex items-center gap-2 cursor-pointer">
                            <i class="ph ph-check text-sm"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const SCHOOL_UPDATE_URL_BASE = '{{ url('/superadmin/schools') }}';
        const PACKAGE_UPDATE_URL_BASE = '{{ url('/superadmin/packages') }}';

        function openAddPackageModal() {
            const modal = document.getElementById('addPackageModal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeAddPackageModal() {
            const modal = document.getElementById('addPackageModal');
            if (modal) modal.classList.add('hidden');
        }

        function openEditPackageModal(id, name, description, price, billingCycle, maxStudents, maxStorageGb, isPopular, features) {
            document.getElementById('editPackageForm').action = PACKAGE_UPDATE_URL_BASE + '/' + id;
            document.getElementById('editModalPackageName').innerText = name;
            document.getElementById('editPkgName').value = name;
            document.getElementById('editPkgDescription').value = description || '';
            document.getElementById('editPkgPrice').value = price;
            document.getElementById('editPkgPopular').value = isPopular ? '1' : '0';
            document.getElementById('editPkgMaxStudents').value = maxStudents || '';
            document.getElementById('editPkgMaxStorage').value = maxStorageGb || '';

            const featureSet = new Set(features || []);
            document.querySelectorAll('.edit-module-chk').forEach(chk => {
                chk.checked = featureSet.has(chk.value);
            });

            document.getElementById('editPackageModal').classList.remove('hidden');
        }
        function closeEditPackageModal() {
            document.getElementById('editPackageModal').classList.add('hidden');
        }

        function openManageLicenseModal(schoolId, schoolName, currentPackage, currentStatus, renewalDate) {
            document.getElementById('manageLicenseForm').action = SCHOOL_UPDATE_URL_BASE + '/' + schoolId;
            document.getElementById('modalSchoolNameDisplay').innerText = schoolName;

            const pkgSelect = document.getElementById('modalCurrentPackage');
            pkgSelect.value = currentPackage || '';

            const stSelect = document.getElementById('modalCurrentStatus');
            stSelect.value = currentStatus || 'actif';

            document.getElementById('modalRenewalDate').value = renewalDate || '';

            document.getElementById('manageLicenseModal').classList.remove('hidden');
        }
        function closeManageLicenseModal() {
            const modal = document.getElementById('manageLicenseModal');
            if (modal) modal.classList.add('hidden');
        }
        function toggleAllModalCheckboxes(btn, checkAll) {
            const form = btn.closest('form');
            if (form) {
                const checkboxes = form.querySelectorAll('.module-chk');
                checkboxes.forEach(chk => chk.checked = checkAll);
            }
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAddPackageModal();
                closeEditPackageModal();
                closeManageLicenseModal();
            }
        });
    </script>
@endsection
