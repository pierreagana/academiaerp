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
            <i id="aiModelsToastIcon" class="ph ph-check-circle text-emerald-600 text-xl font-bold"></i>
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
                            @if($model['is_real'] ?? false)
                                <form action="{{ route('superadmin.ai-models.toggle-status', $model['id']) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-[10px] font-bold {{ $isActive ? 'text-slate-400 hover:text-rose-600' : 'text-slate-400 hover:text-emerald-600' }} transition cursor-pointer">
                                        {{ $isActive ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div>
                            <p class="text-base font-extrabold text-slate-900">{{ $model['name'] }}</p>
                            <p class="text-[11px] text-slate-500 font-medium">{{ $model['provider'] }}</p>
                        </div>

                        @if(!empty($model['latency']))
                            <div class="mt-1">
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Latence indicative</p>
                                <p class="text-sm font-extrabold {{ ($model['color'] ?? 'emerald') === 'emerald' ? 'text-emerald-700' : 'text-violet-700' }}">
                                    {{ $model['latency'] }}
                                </p>
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
                        <input type="checkbox" onchange="toggleAiSetting(this, '{{ $param['key'] }}', this.checked, '{{ addslashes($param['label']) }}')" class="sr-only peer" {{ $param['enabled'] ? 'checked' : '' }}>
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
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <i class="ph ph-key text-indigo-600 text-xl font-bold"></i>
                    <h3 class="text-base font-bold text-slate-900">Fournisseur IA</h3>
                </div>
                <a href="{{ route('superadmin.global-settings') }}" class="text-[11px] font-bold text-indigo-600 hover:underline">
                    Modifier les clés
                </a>
            </div>

            <p class="text-[11px] text-slate-400 mb-4">
                Choisissez quel fournisseur traite réellement les demandes IA de la plateforme (brouillons de support, résumés, etc.). Les clés se configurent dans Paramètres Globaux.
            </p>

            <div class="space-y-4">
                {{-- OpenAI --}}
                <div class="border {{ $activeProvider === 'openai' ? 'border-indigo-300 bg-indigo-50/40' : 'border-slate-200' }} rounded-xl p-3">
                    <div class="flex items-center justify-between mb-2">
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="radio" name="aiProviderRadio" value="openai" onchange="selectAiProvider('openai')" {{ $activeProvider === 'openai' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                            OpenAI
                        </label>
                        @if($activeProvider === 'openai')
                            <span class="text-[10px] font-extrabold uppercase tracking-wide px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">Actif</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" value="{{ $openAiKeyConfigured ? $openAiKeyHint : 'Non configurée' }}" class="flex-1 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-xs font-mono px-3 py-2.5 focus:outline-none" readonly>
                        <button type="button" onclick="testAiConnection('openai')" id="btnTestOpenai" title="Tester la connexion réelle" class="p-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl transition cursor-pointer">
                            <i class="ph ph-arrows-clockwise text-sm font-bold"></i>
                        </button>
                    </div>
                </div>

                {{-- Claude --}}
                <div class="border {{ $activeProvider === 'claude' ? 'border-indigo-300 bg-indigo-50/40' : 'border-slate-200' }} rounded-xl p-3">
                    <div class="flex items-center justify-between mb-2">
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="radio" name="aiProviderRadio" value="claude" onchange="selectAiProvider('claude')" {{ $activeProvider === 'claude' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                            Anthropic Claude
                        </label>
                        @if($activeProvider === 'claude')
                            <span class="text-[10px] font-extrabold uppercase tracking-wide px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">Actif</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" value="{{ $anthropicKeyConfigured ? $anthropicKeyHint : 'Non configurée' }}" class="flex-1 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-xs font-mono px-3 py-2.5 focus:outline-none" readonly>
                        <button type="button" onclick="testAiConnection('claude')" id="btnTestClaude" title="Tester la connexion réelle" class="p-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl transition cursor-pointer">
                            <i class="ph ph-arrows-clockwise text-sm font-bold"></i>
                        </button>
                    </div>
                </div>
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
        function testAiConnection(provider) {
            const btn = document.getElementById(provider === 'claude' ? 'btnTestClaude' : 'btnTestOpenai');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');

            fetch('{{ route("superadmin.ai-models.test-connection") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ provider: provider })
            })
            .then(res => res.json())
            .then(data => {
                showAiModelToast(data.message, data.success);
            })
            .catch(() => {
                showAiModelToast("Erreur de communication avec le serveur.", false);
            })
            .finally(() => {
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        }
        function selectAiProvider(provider) {
            fetch('{{ route("superadmin.ai-models.set-provider") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ provider: provider })
            })
            .then(res => {
                if (!res.ok) throw new Error('save failed');
                return res.json();
            })
            .then(data => {
                showAiModelToast('Fournisseur IA actif : ' + (provider === 'claude' ? 'Anthropic Claude' : 'OpenAI') + '.');
                setTimeout(() => window.location.reload(), 600);
            })
            .catch(() => {
                showAiModelToast("Échec du changement de fournisseur.", false);
            });
        }
        function toggleAiSetting(checkbox, key, enabled, label) {
            fetch('{{ route("superadmin.ai-models.toggle-setting") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ key: key, enabled: enabled ? 1 : 0 })
            })
            .then(res => {
                if (!res.ok) throw new Error('save failed');
                return res.json();
            })
            .then(data => {
                showAiModelToast('Paramètre ' + label + ' enregistré.');
            })
            .catch(err => {
                checkbox.checked = !enabled;
                showAiModelToast("Échec de l'enregistrement du paramètre " + label + ".", false);
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
            .then(res => {
                if (!res.ok) throw new Error('save failed');
                return res.json();
            })
            .then(data => {
                showAiModelToast('Seuil ' + label + ' enregistré à ' + val + '%.');
            })
            .catch(err => {
                showAiModelToast("Échec de l'enregistrement du seuil " + label + ".", false);
            });
        }
        function showAiModelToast(msg, success = true) {
            const toast = document.getElementById('aiModelsToast');
            const toastMsg = document.getElementById('aiModelsToastMsg');
            const toastIcon = document.getElementById('aiModelsToastIcon');
            if (!toast || !toastMsg) return;

            toastMsg.innerText = msg;
            toast.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-800', 'bg-rose-50', 'border-rose-200', 'text-rose-800');
            toastIcon.classList.remove('text-emerald-600', 'text-rose-600', 'ph-check-circle', 'ph-warning-circle');

            if (success) {
                toast.classList.add('bg-emerald-50', 'border-emerald-200', 'text-emerald-800');
                toastIcon.classList.add('text-emerald-600', 'ph-check-circle');
            } else {
                toast.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-800');
                toastIcon.classList.add('text-rose-600', 'ph-warning-circle');
            }
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAddAiModelModal();
        });
    </script>
@endsection
