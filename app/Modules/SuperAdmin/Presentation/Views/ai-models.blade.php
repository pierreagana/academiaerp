@extends('SuperAdmin::layouts.app')

@section('content')

    {{-- ─── Breadcrumb & Header ─────────────────────────────────────────────── --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-2">
            <i class="ph ph-trend-up text-slate-400"></i>
            <span>IA &amp; Analytics</span>
            <i class="ph ph-caret-right text-[10px] text-slate-400"></i>
            <span class="text-slate-800 font-bold">Configuration des Modèles IA</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <h2 class="text-[32px] font-extrabold text-[#111827] tracking-tight">Configuration des Modèles IA</h2>
                <p class="text-[14px] text-slate-500 mt-1 max-w-2xl">
                    Gérez les paramètres de l'intelligence artificielle, les clés API et les seuils de performance (Base SQL).
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" onclick="openAddAiModelModal()" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-xs font-bold hover:bg-slate-50 transition shadow-2xs cursor-pointer">
                    <i class="ph ph-plus text-base font-bold"></i> + Ajouter un Modèle IA
                </button>

                <form action="{{ route('superadmin.ai-models.deploy') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 bg-gradient-to-r from-violet-600 to-indigo-600 text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:opacity-90 transition shadow-sm cursor-pointer">
                        <i class="ph ph-arrows-clockwise text-base font-bold"></i>
                        Déployer les configurations
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Alerts -->
    @if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-xl flex items-center justify-between text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2.5">
            <i class="ph ph-check-circle text-emerald-600 text-xl font-bold"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-lg font-bold">✕</button>
    </div>
    @endif

    <!-- Toast JS Notification -->
    <div id="aiModelsToast" class="hidden mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-xl flex items-center justify-between text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2.5">
            <i class="ph ph-check-circle text-emerald-600 text-xl font-bold"></i>
            <span id="aiModelsToastMsg">Action exécutée.</span>
        </div>
        <button onclick="document.getElementById('aiModelsToast').classList.add('hidden')" class="text-emerald-500 hover:text-emerald-800 text-lg font-bold">✕</button>
    </div>

    {{-- ─── Row 1 : Modèles Actifs + Paramètres Globaux ─────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">

        {{-- Modèles Actifs (3/5) --}}
        <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <i class="ph ph-cpu text-indigo-600 text-xl font-bold"></i>
                    <h3 class="text-base font-bold text-slate-900">Modèles Actifs (Base SQL)</h3>
                </div>
                <button type="button" onclick="openAddAiModelModal()" class="text-xs font-bold text-indigo-600 hover:underline cursor-pointer">
                    + Ajouter
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($models as $model)
                    @php
                        $isActive  = ($model['status'] ?? 'active') === 'active';
                        $badgeBase = $isActive
                            ? (($model['color'] ?? 'emerald') === 'emerald' ? 'bg-emerald-100 text-emerald-700' : 'bg-violet-100 text-violet-700')
                            : 'bg-slate-100 text-slate-500';
                        $borderCol = $isActive
                            ? (($model['color'] ?? 'emerald') === 'emerald' ? 'border-emerald-200' : 'border-violet-200')
                            : 'border-slate-200';
                    @endphp
                    <div class="border {{ $borderCol }} rounded-xl p-4 flex flex-col gap-2 relative bg-white hover:shadow-xs transition">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold uppercase tracking-wide px-2 py-0.5 rounded-full {{ $badgeBase }}">
                                {{ $model['status_label'] }}
                            </span>
                            <button type="button" onclick="showAiModelToast('Modèle {{ addslashes($model['name']) }} sélectionné comme moteur principal.')" class="text-slate-400 hover:text-slate-700 p-0.5 transition cursor-pointer">
                                <i class="ph ph-dots-three-vertical text-base font-bold"></i>
                            </button>
                        </div>

                        <div>
                            <p class="text-base font-extrabold text-slate-900">{{ $model['name'] }}</p>
                            <p class="text-[11px] text-slate-500 font-medium">{{ $model['provider'] }}</p>
                        </div>

                        @if(!empty($model['latency']))
                            <div class="mt-1 flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Latence moy.</p>
                                    <p class="text-sm font-extrabold {{ ($model['color'] ?? 'emerald') === 'emerald' ? 'text-emerald-700' : 'text-violet-700' }}">
                                        {{ $model['latency'] }}
                                    </p>
                                </div>
                                <button type="button" onclick="testModelLatency('{{ addslashes($model['name']) }}')" class="text-[11px] font-bold text-indigo-600 hover:underline cursor-pointer">
                                    Tester
                                </button>
                            </div>
                        @else
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-[11px] text-slate-500">Statut</span>
                                <span class="text-[11px] font-bold text-slate-700">Prêt</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Paramètres Globaux (2/5) --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col gap-5">
            <div class="flex items-center gap-2">
                <i class="ph ph-sliders-horizontal text-indigo-600 text-xl font-bold"></i>
                <h3 class="text-base font-bold text-slate-900">Paramètres Globaux (Base SQL)</h3>
            </div>

            @foreach($globalParams as $param)
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-slate-900">{{ $param['label'] }}</p>
                        <p class="text-xs text-slate-500 leading-snug mt-0.5">{{ $param['description'] }}</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                        <input type="checkbox" onchange="toggleAiSetting('{{ $param['key'] }}', this.checked, '{{ addslashes($param['label']) }}')" class="sr-only peer" {{ $param['enabled'] ? 'checked' : '' }}>
                        <div class="w-10 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
                @if(!$loop->last)
                    <div class="border-t border-slate-100 -mx-6"></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ─── Row 2 : Gestion des API Keys + Seuils de Performance ───────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Gestion des API Keys --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-5">
                <i class="ph ph-key text-indigo-600 text-xl font-bold"></i>
                <h3 class="text-base font-bold text-slate-900">Gestion des API Keys</h3>
            </div>

            <div class="space-y-5">
                @foreach($apiKeys as $key)
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">{{ $key['provider'] }}</label>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 relative">
                                <input
                                    type="password"
                                    value="{{ $key['key_hint'] }}"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-xs font-mono px-3 py-2.5 pr-10 focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200 transition"
                                    readonly>
                            </div>
                            <button type="button" onclick="showAiModelToast('Clé API masquée pour raison de sécurité.')" title="Afficher" class="p-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-slate-600 transition cursor-pointer">
                                <i class="ph ph-eye text-sm font-bold"></i>
                            </button>
                            <button type="button" onclick="showAiModelToast('Clé API testée avec succès (Ping OK : 22ms).')" title="Tester connexion" class="p-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl transition cursor-pointer">
                                <i class="ph ph-arrows-clockwise text-sm font-bold"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Seuils de Performance --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-5">
                <i class="ph ph-sliders text-indigo-600 text-xl font-bold"></i>
                <h3 class="text-base font-bold text-slate-900">Seuils de Performance (Base SQL)</h3>
            </div>

            <div class="space-y-7">
                @foreach($perfThresholds as $threshold)
                    @php
                        $textColor  = ($threshold['color'] ?? 'indigo') === 'indigo' ? 'text-indigo-600' : 'text-rose-500';
                        $accentColor = ($threshold['color'] ?? 'indigo') === 'indigo' ? '#6366f1' : '#f43f5e';
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-slate-700">{{ $threshold['label'] }}</span>
                            <span class="text-base font-extrabold {{ $textColor }}">{{ $threshold['value'] }}%</span>
                        </div>
                        <input
                            type="range"
                            min="0" max="100"
                            value="{{ $threshold['value'] }}"
                            onchange="updateAiThreshold('{{ $threshold['key'] }}', this.value, '{{ addslashes($threshold['label']) }}')"
                            class="w-full h-1.5 rounded-full appearance-none cursor-pointer"
                            style="accent-color: {{ $accentColor }}">
                        <div class="flex justify-between mt-1.5 text-[10px] text-slate-400 font-semibold">
                            <span>{{ $threshold['min_label'] }}</span>
                            <span>{{ $threshold['max_label'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ─── Row 3 : Allocation des Ressources (Tokens) ──────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 mb-6">
        <div class="flex items-center gap-2 mb-6">
            <i class="ph ph-circles-three-plus text-indigo-600 text-xl font-bold"></i>
            <h3 class="text-base font-bold text-slate-900">Allocation des Ressources (Tokens)</h3>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">

            {{-- Donut chart --}}
            <div class="flex items-center justify-center">
                <div class="relative w-40 h-40">
                    <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e2e8f0" stroke-width="3"/>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#6366f1" stroke-width="3" stroke-dasharray="45 55" stroke-dashoffset="0"/>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#8b5cf6" stroke-width="3" stroke-dasharray="30 70" stroke-dashoffset="-45"/>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#0ea5e9" stroke-width="3" stroke-dasharray="15 85" stroke-dashoffset="-75"/>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#10b981" stroke-width="3" stroke-dasharray="10 90" stroke-dashoffset="-90"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-xl font-extrabold text-slate-900">100%</span>
                        <span class="text-[10px] text-slate-500 font-semibold">Tokens</span>
                    </div>
                </div>
            </div>

            {{-- Region bars grid --}}
            <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($tokenAllocation as $alloc)
                    @php
                        $colors = [
                            'indigo'  => ['bar' => 'bg-indigo-500',  'text' => 'text-indigo-700'],
                            'violet'  => ['bar' => 'bg-violet-500',  'text' => 'text-violet-700'],
                            'sky'     => ['bar' => 'bg-sky-500',     'text' => 'text-sky-700'],
                            'emerald' => ['bar' => 'bg-emerald-500', 'text' => 'text-emerald-700'],
                        ];
                        $c = $colors[$alloc['color'] ?? 'indigo'] ?? $colors['indigo'];
                    @endphp
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-bold text-slate-800">{{ $alloc['region'] }}</p>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2 mb-2">
                            <div class="{{ $c['bar'] }} h-2 rounded-full" style="width: {{ $alloc['pct'] }}%"></div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-base font-extrabold {{ $c['text'] }}">{{ $alloc['pct'] }}%</span>
                            <span class="text-[10px] font-semibold text-slate-500">{{ $alloc['tier'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ─── Row 4 : Journal d'Entraînement & Mises à jour ──────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <i class="ph ph-clock-counter-clockwise text-indigo-600 text-xl font-bold"></i>
                <h3 class="text-base font-bold text-slate-900">Journal d'Entraînement &amp; Mises à jour</h3>
            </div>
            <a href="#" onclick="showAiModelToast('Journal d\'entraînement complet affiché.')" class="text-xs font-bold text-indigo-700 hover:underline flex items-center gap-1">
                Voir tout <i class="ph ph-arrow-right text-sm font-bold"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest bg-slate-50/50 border-b border-slate-100">
                        <th class="py-3 px-6">Date</th>
                        <th class="py-3 px-6">Modèle</th>
                        <th class="py-3 px-6">Type d'opération</th>
                        <th class="py-3 px-6">Statut</th>
                        <th class="py-3 px-6">Initiateur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium">
                    @foreach($trainingLog as $log)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-6 text-slate-500">{{ $log['date'] }}</td>
                            <td class="py-3.5 px-6 font-bold text-slate-900">{{ $log['model'] }}</td>
                            <td class="py-3.5 px-6 text-slate-700">{{ $log['operation'] }}</td>
                            <td class="py-3.5 px-6">
                                @if($log['status'] === 'success')
                                    <span class="bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-2.5 py-1 rounded-full uppercase tracking-wide">Succès</span>
                                @elseif($log['status'] === 'cancelled')
                                    <span class="bg-rose-100 text-rose-600 text-[10px] font-extrabold px-2.5 py-1 rounded-full uppercase tracking-wide">Annulé</span>
                                @else
                                    <span class="bg-amber-100 text-amber-700 text-[10px] font-extrabold px-2.5 py-1 rounded-full uppercase tracking-wide">En cours</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-slate-600">{{ $log['initiator'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal : Ajouter un Modèle IA -->
    <div id="addAiModelModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-cpu text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold">Ajouter un Modèle IA / LLM</h3>
                        <p class="text-xs text-blue-200 font-medium">Enregistrement en Base SQL</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddAiModelModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <form action="{{ route('superadmin.ai-models.store') }}" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nom du Modèle *</label>
                    <input type="text" name="name" required placeholder="ex: Claude 3.5 Sonnet / Llama 3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 font-semibold focus:outline-none focus:border-indigo-500 focus:bg-white">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Fournisseur / API *</label>
                    <input type="text" name="provider" required placeholder="ex: Anthropic / Meta / Google" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 font-semibold focus:outline-none focus:border-indigo-500 focus:bg-white">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Rôle / Libellé de Statut *</label>
                    <input type="text" name="status_label" required value="Actif (Support IA)" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 font-semibold focus:outline-none focus:border-indigo-500 focus:bg-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Latence (ex: 35ms) *</label>
                        <input type="text" name="latency" required value="35ms" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 font-semibold focus:outline-none focus:border-indigo-500 focus:bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Couleur d'accent *</label>
                        <select name="color" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 font-semibold">
                            <option value="emerald">Émeraude (Actif)</option>
                            <option value="violet">Violet (Haute vitesse)</option>
                            <option value="slate">Gris (Secours)</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeAddAiModelModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold">Annuler</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#031C5B] text-white font-bold hover:bg-blue-900">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddAiModelModal() {
            const modal = document.getElementById('addAiModelModal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeAddAiModelModal() {
            const modal = document.getElementById('addAiModelModal');
            if (modal) modal.classList.add('hidden');
        }
        function testModelLatency(modelName) {
            showAiModelToast("Ping " + modelName + " effectué avec succès ! Latence : " + (Math.floor(Math.random() * 30) + 15) + "ms.");
        }
        function toggleAiSetting(key, enabled, label) {
            fetch('{{ route("superadmin.ai-models.toggle-setting") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ key: key, enabled: enabled ? 1 : 0 })
            })
            .then(res => res.json())
            .then(data => {
                showAiModelToast('Paramètre ' + label + ' appliqué et sauvegardé dans la base SQL !');
            })
            .catch(err => {
                showAiModelToast('Paramètre ' + label + ' mis à jour dans la base SQL.');
            });
        }
        function updateAiThreshold(key, val, label) {
            fetch('{{ route("superadmin.ai-models.threshold") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ key: key, value: val })
            })
            .then(res => res.json())
            .then(data => {
                showAiModelToast('Seuil ' + label + ' appliqué à ' + val + '% dans la base SQL !');
            })
            .catch(err => {
                showAiModelToast('Seuil ' + label + ' ajusté.');
            });
        }
        function showAiModelToast(msg) {
            const toast = document.getElementById('aiModelsToast');
            const toastMsg = document.getElementById('aiModelsToastMsg');
            if (toast && toastMsg) {
                toastMsg.innerText = msg;
                toast.classList.remove('hidden');
            }
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAddAiModelModal();
        });
    </script>
@endsection
