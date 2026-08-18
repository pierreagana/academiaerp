@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Breadcrumb & Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-2">
            <span>Gestion Établissements</span>
            <i class="ph ph-caret-right text-[10px] text-slate-400"></i>
            <span class="text-slate-800 font-bold">Configuration Spécifique</span>
        </div>
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Configuration Spécifique Globale</h2>
                <p class="text-xs text-slate-500 mt-1">Paramétrez les règles métiers, les intégrations de paiement et les politiques de rétention de données pour l'ensemble du réseau éducatif.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" onclick="window.location.reload()" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition shadow-xs">
                    <i class="ph ph-arrows-counter-clockwise text-base"></i>
                    Rétablir par défaut
                </button>
                <button type="submit" form="config-form" class="flex items-center gap-2 px-5 py-2.5 bg-[#031C5B] text-white rounded-xl text-xs font-semibold hover:bg-blue-950 transition shadow-sm">
                    <i class="ph ph-floppy-disk text-base"></i>
                    Enregistrer les modifications
                </button>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-2">
            <i class="ph ph-check-circle text-lg text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif

    <form id="config-form" action="{{ route('superadmin.specific-configuration.update') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- SECTION 1: Localisation & Dates -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
                <div>
                    <!-- Header -->
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="ph ph-globe text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Localisation & Dates</h3>
                            <p class="text-[11px] text-slate-500">Standards régionaux applicables</p>
                        </div>
                    </div>

                    <div class="border-b border-slate-100 mb-5"></div>

                    <!-- Fields -->
                    <div class="space-y-4">
                        <!-- Devise par défaut -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Devise par défaut du réseau</label>
                            <div class="relative">
                                <select name="currency" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 appearance-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition">
                                    <option value="Franc CFA (XOF)" {{ ($config['currency'] ?? '') == 'Franc CFA (XOF)' ? 'selected' : '' }}>Franc CFA (XOF)</option>
                                    <option value="Euro (EUR)" {{ ($config['currency'] ?? '') == 'Euro (EUR)' ? 'selected' : '' }}>Euro (EUR)</option>
                                    <option value="US Dollar (USD)" {{ ($config['currency'] ?? '') == 'US Dollar (USD)' ? 'selected' : '' }}>US Dollar (USD)</option>
                                    <option value="Franc Guinéen (GNF)" {{ ($config['currency'] ?? '') == 'Franc Guinéen (GNF)' ? 'selected' : '' }}>Franc Guinéen (GNF)</option>
                                </select>
                                <div class="absolute right-3 top-3 pointer-events-none text-slate-400">
                                    <i class="ph ph-caret-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Fuseau horaire -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Fuseau horaire système</label>
                            <div class="relative">
                                <select name="timezone" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 appearance-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition">
                                    <option value="GMT+1 (Douala, Kinshasa)" {{ ($config['timezone'] ?? '') == 'GMT+1 (Douala, Kinshasa)' ? 'selected' : '' }}>GMT+1 (Douala, Kinshasa)</option>
                                    <option value="GMT+0 (Dakar, Abidjan)" {{ ($config['timezone'] ?? '') == 'GMT+0 (Dakar, Abidjan)' ? 'selected' : '' }}>GMT+0 (Dakar, Abidjan)</option>
                                    <option value="GMT+2 (Paris, Cairo)" {{ ($config['timezone'] ?? '') == 'GMT+2 (Paris, Cairo)' ? 'selected' : '' }}>GMT+2 (Paris, Cairo)</option>
                                </select>
                                <div class="absolute right-3 top-3 pointer-events-none text-slate-400">
                                    <i class="ph ph-caret-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Dates d'année scolaire -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Début d'année</label>
                                <div class="relative flex items-center">
                                    <input type="text" name="start_month" value="{{ $config['start_month'] ?? 'septembre' }}" class="w-full bg-white border border-slate-200 rounded-xl pl-3 py-2 pr-8 text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-600">
                                    <i class="ph ph-calendar text-slate-400 absolute right-3 text-sm pointer-events-none"></i>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Fin d'année</label>
                                <div class="relative flex items-center">
                                    <input type="text" name="end_year" value="{{ $config['end_year'] ?? 'juin 2025' }}" class="w-full bg-white border border-slate-200 rounded-xl pl-3 py-2 pr-8 text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-600">
                                    <i class="ph ph-calendar text-slate-400 absolute right-3 text-sm pointer-events-none"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Passerelles de Paiement Globales -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="ph ph-credit-card text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Passerelles de Paiement Globales</h3>
                            <p class="text-[11px] text-slate-500">Activation des moyens d'encaissement et frais</p>
                        </div>
                    </div>
                    <span class="bg-blue-100/70 text-blue-800 border border-blue-200/60 text-[11px] font-semibold px-3 py-1 rounded-full">Environnement de Production</span>
                </div>

                <div class="border-b border-slate-100 mb-5"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Fournisseurs autorisés -->
                    <div>
                        <h4 class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase mb-3">Fournisseurs autorisés</h4>
                        
                        <div class="space-y-2.5">
                            <!-- Academia Pay (Natif) -->
                            <div class="flex items-center justify-between p-2.5 border border-indigo-200/80 rounded-xl bg-indigo-50/50">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-[#031C5B] text-white flex items-center justify-center font-black text-[10px] shadow-xs">
                                        AP
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold text-[#031C5B]">Academia Pay</span>
                                        <span class="inline-block ml-1.5 px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase bg-purple-100 text-purple-700">Natif</span>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="payment_academia_pay" value="1" class="sr-only peer" {{ ($config['payment_academia_pay'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#031C5B]"></div>
                                </label>
                            </div>

                            <!-- Orange Money -->
                            <div class="flex items-center justify-between p-2.5 border border-slate-200/70 rounded-xl bg-slate-50/40">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-orange-500 text-white flex items-center justify-center font-black text-[10px]">
                                        OM
                                    </div>
                                    <span class="text-xs font-semibold text-slate-800">Orange Money</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="payment_orange_money" value="1" class="sr-only peer" {{ ($config['payment_orange_money'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#031C5B]"></div>
                                </label>
                            </div>

                            <!-- Wave Mobile Money -->
                            <div class="flex items-center justify-between p-2.5 border border-slate-200/70 rounded-xl bg-slate-50/40">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-sky-400 text-white flex items-center justify-center font-black text-[10px]">
                                        WV
                                    </div>
                                    <span class="text-xs font-semibold text-slate-800">Wave Mobile Money</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="payment_wave" value="1" class="sr-only peer" {{ ($config['payment_wave'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#031C5B]"></div>
                                </label>
                            </div>

                            <!-- MTN Mobile Money -->
                            <div class="flex items-center justify-between p-2.5 border border-slate-200/70 rounded-xl bg-slate-50/40">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-amber-400 text-slate-900 flex items-center justify-center font-black text-[10px]">
                                        MTN
                                    </div>
                                    <span class="text-xs font-semibold text-slate-800">MTN Mobile Money</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="payment_mtn" value="1" class="sr-only peer" {{ ($config['payment_mtn'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#031C5B]"></div>
                                </label>
                            </div>

                            <!-- Cartes Bancaires -->
                            <div class="flex items-center justify-between p-2.5 border border-slate-200/70 rounded-xl bg-slate-50/40">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-blue-900 text-white flex items-center justify-center font-black text-[10px]">
                                        CB
                                    </div>
                                    <span class="text-xs font-semibold text-slate-800">Cartes Bancaires (Visa/MC)</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="payment_card" value="1" class="sr-only peer" {{ ($config['payment_card'] ?? '0') == '1' ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#031C5B]"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Règles de tarification -->
                    <div>
                        <h4 class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase mb-3">Règles de tarification</h4>
                        
                        <div class="border border-slate-200/80 rounded-xl p-3 bg-slate-50/30 mb-3">
                            <p class="text-[11px] font-semibold text-slate-700 mb-2">Frais de transaction réseau supportés par</p>
                            
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="fee_payer" value="school" class="text-[#031C5B] focus:ring-0" {{ ($config['fee_payer'] ?? 'school') == 'school' ? 'checked' : '' }}>
                                    <span class="text-xs font-semibold text-slate-800">L'établissement</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="fee_payer" value="parents" class="text-[#031C5B] focus:ring-0" {{ ($config['fee_payer'] ?? '') == 'parents' ? 'checked' : '' }}>
                                    <span class="text-xs font-medium text-slate-600">Les parents d'élèves</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Taux de frais global par défaut (%)</label>
                            <div class="relative flex items-center">
                                <input type="text" name="fee_rate" value="{{ $config['fee_rate'] ?? '1,5' }}" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:border-blue-600">
                                <span class="absolute right-3 text-xs font-semibold text-slate-400">%</span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">Ce taux peut être surchargé au niveau de chaque établissement.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Seuils d'Alertes -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <!-- Header -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                        <i class="ph ph-bell-ringing text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Seuils d'Alertes</h3>
                        <p class="text-[11px] text-slate-500">Déclencheurs d'anomalies</p>
                    </div>
                </div>

                <div class="border-b border-slate-100 mb-5"></div>

                <div class="space-y-4">
                    <!-- Alerte retard de paiement -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alerte retard de paiement (Jours)</label>
                        <input type="number" name="alert_payment_delay_days" value="{{ $config['alert_payment_delay_days'] ?? '15' }}" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:border-blue-600">
                    </div>

                    <!-- Seuil d'alerte charge serveur -->
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                            <span class="text-slate-700">Seuil d'alerte de charge serveur (%)</span>
                            <span class="font-bold text-slate-900" id="server-load-val">{{ $config['alert_server_load_percent'] ?? '85' }}%</span>
                        </div>
                        <input type="range" name="alert_server_load_percent" min="10" max="100" value="{{ $config['alert_server_load_percent'] ?? '85' }}" oninput="document.getElementById('server-load-val').innerText = this.value + '%'" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-red-600">
                    </div>

                    <!-- Alerte baisse d'assiduité -->
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                            <span class="text-slate-700">Alerte baisse d'assiduité globale (%)</span>
                            <span class="font-bold text-slate-900" id="attendance-drop-val">{{ $config['alert_attendance_drop_percent'] ?? '15' }}%</span>
                        </div>
                        <input type="range" name="alert_attendance_drop_percent" min="1" max="50" value="{{ $config['alert_attendance_drop_percent'] ?? '15' }}" oninput="document.getElementById('attendance-drop-val').innerText = this.value + '%'" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                    </div>
                </div>
            </div>

            <!-- SECTION 4: Paramètres IA & Analyse de Données -->
            <div class="bg-indigo-50/40 rounded-2xl border border-indigo-100/80 shadow-xs p-6">
                <!-- Header -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                        <i class="ph ph-sparkle text-xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-slate-900 text-sm">Paramètres IA & Analyse de Données</h3>
                            <span class="bg-purple-100 text-purple-700 text-[10px] font-extrabold px-1.5 py-0.5 rounded uppercase">GLOBAL</span>
                        </div>
                        <p class="text-[11px] text-slate-500">Contrôle des modèles prédictifs et rétention</p>
                    </div>
                </div>

                <div class="border-b border-indigo-100/80 mb-5"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Left: Toggles -->
                    <div class="space-y-3">
                        <!-- Fonctionnalités IA expérimentales -->
                        <div class="bg-white border border-indigo-100/80 rounded-xl p-3.5">
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-xs font-bold text-slate-800 leading-snug">Activer les fonctionnalités IA expérimentales</span>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                                    <input type="checkbox" name="ai_experimental_features" value="1" class="sr-only peer" {{ ($config['ai_experimental_features'] ?? '0') == '1' ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#031C5B]"></div>
                                </label>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1.5 leading-relaxed">Déploie les modèles bêta d'assistance à la notation et de détection de plagiat sur l'ensemble des établissements.</p>
                        </div>

                        <!-- Analyse prédictive des performances -->
                        <div class="bg-white border border-indigo-100/80 rounded-xl p-3.5">
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-xs font-bold text-slate-800 leading-snug">Analyse prédictive des performances</span>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                                    <input type="checkbox" name="ai_predictive_performance" value="1" class="sr-only peer" {{ ($config['ai_predictive_performance'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#031C5B]"></div>
                                </label>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1.5 leading-relaxed">Autoriser l'IA à analyser l'historique des notes pour identifier les élèves en risque de décrochage.</p>
                        </div>
                    </div>

                    <!-- Right: Data retention -->
                    <div class="flex flex-col justify-between pl-1">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <i class="ph ph-database text-purple-600 text-sm"></i>
                                <h4 class="text-xs font-bold text-slate-800">Politique de Rétention des Données Analytiques</h4>
                            </div>
                            <p class="text-[10px] text-slate-400 leading-relaxed mb-4">Détermine la durée de conservation des données brutes utilisées pour l'entraînement continu des modèles locaux.</p>
                        </div>

                        <div class="relative">
                            <select name="data_retention_years" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-800 appearance-none focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600">
                                <option value="3 Années Scolaires (Recommandé)" {{ ($config['data_retention_years'] ?? '') == '3 Années Scolaires (Recommandé)' ? 'selected' : '' }}>3 Années Scolaires (Recommandé)</option>
                                <option value="1 Année Scolaire" {{ ($config['data_retention_years'] ?? '') == '1 Année Scolaire' ? 'selected' : '' }}>1 Année Scolaire</option>
                                <option value="5 Années Scolaires" {{ ($config['data_retention_years'] ?? '') == '5 Années Scolaires' ? 'selected' : '' }}>5 Années Scolaires</option>
                            </select>
                            <div class="absolute right-3 top-3 pointer-events-none text-slate-400">
                                <i class="ph ph-caret-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
@endsection
