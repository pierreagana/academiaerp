@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[32px] font-extrabold text-[#111827]">Catalogue de Services & Grille Tarifaire</h2>
            <p class="text-[15px] text-slate-600 mt-1 max-w-3xl">Définition des offres commerciales, grille des prix par forfait, catalogue d'add-ons optionnels et niveaux de service (SLA).</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <button type="button" onclick="openAddPackageModal()" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-900 transition shadow-sm cursor-pointer">
                <i class="ph ph-plus-circle text-lg font-bold"></i> Nouveau Forfait
            </button>
        </div>
    </div>

    <!-- Section 1 : Catalogue d'Add-ons & Modules Optionnels -->
    <div class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-extrabold text-slate-900">Catalogue des Modules & Add-ons Optionnels</h3>
                <p class="text-xs text-slate-500 mt-0.5">Services activables individuellement ou intégrés dans les forfaits Premium</p>
            </div>
            <a href="{{ route('superadmin.modules') }}" class="text-xs font-bold text-[#031C5B] hover:underline flex items-center gap-1">
                Gérer le registre système <i class="ph ph-arrow-right font-bold"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @if(isset($addons) && count($addons) > 0)
                @foreach($addons as $item)
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl {{ $item['icon_bg'] ?? 'bg-blue-100 text-blue-600' }} flex items-center justify-center text-xl font-bold">
                                <i class="ph {{ $item['icon'] ?? 'ph-puzzle-piece' }}"></i>
                            </div>
                            <span class="text-xs font-bold px-3 py-1 rounded-full {{ $item['price_color'] ?? 'text-emerald-600' }} bg-slate-50 border border-slate-100">
                                {{ $item['price_tag'] ?? 'Inclus' }}
                            </span>
                        </div>
                        <h4 class="text-base font-bold text-slate-900">{{ $item['name'] }}</h4>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">{{ $item['description'] }}</p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <form action="{{ route('superadmin.service-catalog.toggle', $item['id']) }}" method="POST">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 cursor-pointer group" title="Cliquer pour basculer l'état système">
                                <div class="w-10 h-5 rounded-full p-0.5 transition-colors duration-200 ease-in-out {{ !empty($item['is_enabled']) ? 'bg-[#031C5B]' : 'bg-slate-300' }} flex items-center">
                                    <div class="w-4 h-4 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out {{ !empty($item['is_enabled']) ? 'translate-x-5' : 'translate-x-0' }}"></div>
                                </div>
                                <span class="font-bold text-xs {{ !empty($item['is_enabled']) ? 'text-[#031C5B]' : 'text-slate-500' }}">
                                    {{ !empty($item['is_enabled']) ? 'Activé système' : 'Désactivé' }}
                                </span>
                            </button>
                        </form>
                        @if($item['slug'])
                            <a href="{{ route('superadmin.module-details.show', $item['slug']) }}" class="font-bold text-slate-500 hover:text-[#031C5B] hover:underline flex items-center gap-0.5">Détails <i class="ph ph-arrow-right font-bold"></i></a>
                        @endif
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Section 2 : Engagements & SLAs par Forfait -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-12">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-900">Garanties de Service (SLA & Uptime)</h3>
            <p class="text-xs text-slate-500 mt-0.5">Engagements contractuels de la plateforme SaaS selon le niveau d'abonnement</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-6">Forfait</th>
                        <th class="py-3.5 px-4">Uptime Garanti</th>
                        <th class="py-3.5 px-4">Temps de Réponse Support</th>
                        <th class="py-3.5 px-6 text-right">Pénalités SLA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @if(isset($slas) && count($slas) > 0)
                        @foreach($slas as $sla)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-bold text-slate-900">{{ $sla['forfait'] }}</td>
                            <td class="py-4 px-4 font-bold text-emerald-600">{{ $sla['uptime'] }}</td>
                            <td class="py-4 px-4 font-medium">{{ $sla['support_response'] }}</td>
                            <td class="py-4 px-6 text-right font-medium text-slate-500">{{ $sla['penalty'] }}</td>
                        </tr>
                        @endforeach
                    @endif
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

    <script>
        function openAddPackageModal() {
            const modal = document.getElementById('addPackageModal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeAddPackageModal() {
            const modal = document.getElementById('addPackageModal');
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
            if (e.key === 'Escape') closeAddPackageModal();
        });
    </script>
@endsection
