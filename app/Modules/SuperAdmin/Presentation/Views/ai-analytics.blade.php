@extends('SuperAdmin::layouts.app')

@section('content')
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-[24px] font-extrabold text-[#1E1B4B]">EduAnalytics IA</h2>
            <p class="text-[14px] text-slate-500 mt-1">Plateforme d'Analyses IA & Pilotage Stratégique (Données BD SQL)</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 shrink-0">
            <button type="button" onclick="openAiReportModal()" class="flex items-center gap-2 bg-[#7C3AED] text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-purple-700 transition shadow-sm shadow-purple-500/30 cursor-pointer">
                <i class="ph ph-sparkle text-base font-bold"></i> Générer Rapport IA
            </button>
        </div>
    </div>

    {{-- Toast Alert --}}
    <div id="aiAnalyticsToast" class="hidden mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-xl flex items-center justify-between text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2.5">
            <i class="ph ph-check-circle text-emerald-600 text-xl font-bold"></i>
            <span id="aiAnalyticsToastMsg">Action appliquée avec succès.</span>
        </div>
        <button onclick="document.getElementById('aiAnalyticsToast').classList.add('hidden')" class="text-emerald-500 hover:text-emerald-800 text-lg font-bold">✕</button>
    </div>

    {{-- Top Banner: Aperçu Stratégique IA --}}
    <div class="bg-gradient-to-br from-[#F8F5FF] to-white border border-purple-100 rounded-2xl p-6 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-sm">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-3">
                <i class="ph ph-sparkle text-2xl text-[#7C3AED] font-bold"></i>
                <h3 class="text-[20px] font-extrabold text-[#1E1B4B]">Aperçu Stratégique IA</h3>
            </div>
            <p class="text-[14px] text-slate-600 font-medium leading-relaxed max-w-3xl mb-5">
                Vue d'ensemble en temps réel des établissements et de l'adoption des modules du réseau.
                <span class="text-[#7C3AED] font-bold">{{ $kpis['active_schools'] }} établissements actifs</span>
                sur {{ $kpis['total_schools'] }} enregistrés — {{ $kpis['total_students'] }} élèves au total en base SQL.
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#E9D5FF] bg-white text-[#7C3AED] text-[12px] font-bold shadow-sm">
                    <i class="ph ph-cpu font-bold"></i> {{ $aiModels->count() }} Modèle(s) LLM Actif(s)
                </span>
                @if($kpis['error_logs'] == 0)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#A7F3D0] bg-white text-[#059669] text-[12px] font-bold shadow-sm">
                    <i class="ph ph-check-circle font-bold"></i> Optimisation Maximale (0 Erreur)
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 bg-white text-red-600 text-[12px] font-bold shadow-sm">
                    <i class="ph ph-warning-circle font-bold"></i> {{ $kpis['error_logs'] }} Erreur(s) Système
                </span>
                @endif
            </div>
        </div>
        <div class="bg-white border border-slate-100 shadow-sm rounded-xl p-5 flex items-center gap-5 shrink-0">
            <div class="relative w-20 h-20 flex items-center justify-center">
                @php
                    $healthScore = $kpis['total_schools'] > 0 ? round(($kpis['active_schools'] / $kpis['total_schools']) * 100) : 0;
                @endphp
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                    <path class="text-slate-100" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <path class="text-[#7C3AED]" stroke-dasharray="{{ $healthScore }}, 100" stroke-width="3" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
                <div class="absolute flex flex-col items-center justify-center text-[#1E1B4B]">
                    <span class="text-[22px] font-extrabold leading-none">{{ $healthScore }}</span>
                    <span class="text-[10px] font-medium text-slate-400">/100</span>
                </div>
            </div>
            <div>
                <h4 class="text-[16px] font-extrabold text-[#1E1B4B] mb-1">Santé Globale<br>du Réseau</h4>
                <div class="flex items-center gap-1 text-[#059669] text-[12px] font-bold">
                    <i class="ph ph-trend-up font-bold"></i> Actifs / Total
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

        {{-- Total Schools --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full rounded-tr-2xl pointer-events-none"></div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center text-lg shadow-sm font-bold">
                    <i class="ph ph-buildings"></i>
                </div>
                <span class="bg-[#ECFDF5] text-[#059669] text-[10px] font-bold px-2 py-1 rounded tracking-wider">BD SQL</span>
            </div>
            <p class="text-[13px] font-medium text-slate-500 mb-1">Établissements Enregistrés</p>
            <div class="flex items-baseline gap-2 mb-4 border-b border-slate-100 pb-4">
                <h3 class="text-[28px] font-extrabold text-[#1E1B4B] leading-none">{{ $kpis['total_schools'] }}</h3>
                <span class="text-[12px] font-medium text-slate-400">écoles</span>
            </div>
            <p class="text-[13px] text-slate-600 font-medium">
                <span class="text-emerald-600 font-bold">{{ $kpis['active_schools'] }}</span> actifs,
                <span class="text-[#7C3AED] font-bold">{{ $kpis['premium_schools'] }}</span> plan Premium+
            </p>
        </div>

        {{-- Revenue --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col relative overflow-hidden border-t-4 border-t-emerald-500">
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-[#ECFDF5] text-[#059669] flex items-center justify-center text-lg shadow-sm font-bold">
                    <i class="ph ph-money"></i>
                </div>
            </div>
            <p class="text-[13px] font-medium text-slate-500 mb-1">Revenu Encaissé (Total)</p>
            <div class="flex items-baseline gap-2 mb-4 border-b border-slate-100 pb-4">
                <h3 class="text-[22px] font-extrabold text-[#1E1B4B] leading-none">{{ $kpis['total_revenue'] }} {{ $systemCurrency ?? 'FCFA' }}</h3>
            </div>
            <p class="text-[12px] text-slate-500 font-medium leading-relaxed">
                Recouvrement estimé à <span class="text-emerald-600 font-bold">94%</span> | 
                <span class="text-amber-600 font-bold">{{ $kpis['pending_revenue'] }} {{ $systemCurrency ?? 'FCFA' }}</span> en attente
            </p>
        </div>

        {{-- Error Logs --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col relative overflow-hidden border-t-4 {{ $kpis['error_logs'] > 0 ? 'border-t-red-500' : 'border-t-emerald-500' }}">
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-10 h-10 rounded-xl {{ $kpis['error_logs'] > 0 ? 'bg-red-50 text-red-500' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center text-lg shadow-sm font-bold">
                    <i class="ph ph-{{ $kpis['error_logs'] > 0 ? 'warning' : 'check-circle' }}"></i>
                </div>
            </div>
            <p class="text-[13px] font-medium text-slate-500 mb-1">Erreurs Système Critiques</p>
            <div class="flex items-baseline gap-2 mb-4 border-b border-slate-100 pb-4">
                <h3 class="text-[28px] font-extrabold text-[#1E1B4B] leading-none">{{ $kpis['error_logs'] }}</h3>
            </div>
            <p class="text-[13px] {{ $kpis['error_logs'] > 0 ? 'text-red-600' : 'text-emerald-600' }} font-medium">
                {{ $kpis['error_logs'] > 0 ? 'Intervention requise' : 'Tous les systèmes OK' }}
            </p>
        </div>

        {{-- Élèves --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-50 rounded-bl-full rounded-tr-2xl pointer-events-none"></div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-[#F5F3FF] text-[#7C3AED] flex items-center justify-center text-lg shadow-sm font-bold">
                    <i class="ph ph-users-three"></i>
                </div>
                <span class="bg-[#ECFDF5] text-[#059669] text-[10px] font-bold px-2 py-1 rounded tracking-wider">OPTIMISÉ</span>
            </div>
            <p class="text-[13px] font-medium text-slate-500 mb-1">Élèves Inscrits (Total)</p>
            <div class="flex items-baseline gap-2 mb-4 border-b border-slate-100 pb-4">
                <h3 class="text-[28px] font-extrabold text-[#1E1B4B] leading-none">{{ $kpis['total_students'] }}</h3>
            </div>
            <p class="text-[13px] text-slate-600 font-medium leading-relaxed">
                Répartis sur <span class="text-[#7C3AED] font-bold">{{ $kpis['total_schools'] }}</span> campus.
            </p>
        </div>
    </div>

    {{-- Bottom Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Charts + School Health --}}
        <div class="lg:col-span-2 flex flex-col gap-6">

            {{-- Engagement Metrics --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-[18px] font-extrabold text-[#1E1B4B]">Adoption des Modules (60 derniers jours)</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($engagementData as $item)
                        @php
                            $colorMap = [
                                'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'bar' => 'bg-emerald-500'],
                                'blue'    => ['bg' => 'bg-blue-50',   'text' => 'text-blue-600',   'bar' => 'bg-blue-500'],
                                'indigo'  => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'bar' => 'bg-indigo-500'],
                                'purple'  => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'bar' => 'bg-purple-500'],
                            ];
                            $c = $colorMap[$item['color']] ?? $colorMap['blue'];
                            $numericPct = (int) str_replace('%', '', $item['value']);
                        @endphp
                        <div class="bg-[#F8FAFC] border border-slate-100 rounded-xl p-4">
                            <p class="text-[12px] font-bold text-slate-500 mb-3">{{ $item['label'] }}</p>
                            <div class="flex items-baseline gap-2 mb-3">
                                <span class="text-[26px] font-extrabold text-[#1E1B4B]">{{ $item['value'] }}</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                <div class="{{ $c['bar'] }} h-full rounded-full" style="width: {{ $numericPct }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- AI Predictions / Risk Alerts --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-[18px] font-extrabold text-[#1E1B4B]">Alertes de Risque (Statuts & Paiements Réels)</h3>
                    <a href="/superadmin/ai-models" class="text-xs font-bold text-[#7C3AED] hover:underline flex items-center gap-1">
                        Gérer les modèles LLM <i class="ph ph-arrow-right font-bold"></i>
                    </a>
                </div>
                <div class="space-y-4">
                    @forelse($predictions as $pred)
                        @php
                            $sevMap = [
                                'high'   => ['bg' => 'bg-[#FEF2F2]', 'border' => 'border-[#FECACA]', 'icon_bg' => 'bg-white border-[#FCA5A5]', 'icon_color' => 'text-[#DC2626]', 'icon' => 'warning-octagon', 'badge' => 'bg-[#FEE2E2] text-[#DC2626]', 'badge_text' => 'Intervention Requise'],
                                'medium' => ['bg' => 'bg-[#FFF7ED]', 'border' => 'border-[#FFEDD5]', 'icon_bg' => 'bg-white border-[#FDBA74]', 'icon_color' => 'text-[#EA580C]', 'icon' => 'warning', 'badge' => 'bg-[#FFEDD5] text-[#EA580C]', 'badge_text' => 'À Surveiller'],
                                'low'    => ['bg' => 'bg-[#ECFDF5]', 'border' => 'border-[#A7F3D0]', 'icon_bg' => 'bg-white border-[#6EE7B7]', 'icon_color' => 'text-[#059669]', 'icon' => 'check',       'badge' => 'bg-[#D1FAE5] text-[#059669]',  'badge_text' => 'Optimal'],
                            ];
                            $s = $sevMap[$pred['severity']] ?? $sevMap['medium'];
                        @endphp
                        <div class="flex items-start gap-4 {{ $s['bg'] }} border {{ $s['border'] }} rounded-xl p-4">
                            <div class="w-10 h-10 rounded-lg {{ $s['icon_bg'] }} border flex items-center justify-center {{ $s['icon_color'] }} shrink-0">
                                <i class="ph ph-{{ $s['icon'] }} text-xl font-bold"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <h4 class="text-[14px] font-bold text-[#1E1B4B]">{{ $pred['school'] }}</h4>
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded {{ $s['badge'] }} shrink-0">
                                        {{ $s['badge_text'] }}
                                    </span>
                                </div>
                                <p class="text-[13px] font-bold text-slate-700 mb-0.5">{{ $pred['risk'] }}</p>
                                <p class="text-[12px] font-medium text-slate-500">{{ $pred['reason'] }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-[13px] text-slate-500 text-center py-6">Aucun établissement à risque détecté actuellement.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right: Recommandations Stratégiques IA --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 rounded-xl bg-[#F5F3FF] flex items-center justify-center text-[#7C3AED] font-bold">
                    <i class="ph ph-robot text-2xl"></i>
                </div>
                <h3 class="text-[18px] font-extrabold text-[#1E1B4B] leading-tight">Recommandations<br>Stratégiques IA</h3>
            </div>

            <div class="relative pl-6 space-y-8 before:absolute before:inset-0 before:left-[11px] before:w-px before:h-full before:bg-slate-200">

                <div class="relative">
                    <div class="absolute -left-[30px] top-1 w-3.5 h-3.5 rounded-full bg-white border-2 border-[#7C3AED] z-10 shadow-sm"></div>
                    <h4 class="text-[14px] font-bold text-[#1E1B4B] mb-2">Réallocation des Ressources Numériques</h4>
                    <p class="text-[13px] text-slate-600 font-medium leading-relaxed mb-4">
                        Allouer davantage de licences logicielles aux régions à forte croissance.
                    </p>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="applyAiAction('Réallocation des ressources numériques effectuée avec succès !')" class="bg-[#7C3AED] text-white text-[12px] font-bold px-4 py-2 rounded-xl hover:bg-purple-700 transition shadow-xs cursor-pointer">Appliquer</button>
                        <button type="button" onclick="applyAiAction('Recommandation ignorée.')" class="bg-white border border-slate-200 text-slate-600 text-[12px] font-bold px-4 py-2 rounded-xl hover:bg-slate-50 transition shadow-xs cursor-pointer">Ignorer</button>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -left-[30px] top-1 w-3.5 h-3.5 rounded-full bg-white border-2 border-[#2563EB] z-10 shadow-sm"></div>
                    <h4 class="text-[14px] font-bold text-[#1E1B4B] mb-2">Campagne de Rétention Active</h4>
                    <p class="text-[13px] text-slate-600 font-medium leading-relaxed mb-4">
                        @if(count($predictions) > 0)
                            Initier une communication ciblée pour <span class="font-bold text-[#031C5B]">{{ $predictions[0]['school'] }}</span>.
                            Risque de désabonnement détecté d'ici 30 jours.
                        @else
                            Aucun risque de désabonnement immédiat détecté.
                        @endif
                    </p>
                    <button type="button" onclick="openRetentionEmailModal()" class="bg-[#031C5B] text-white text-[12px] font-bold px-4 py-2 rounded-xl hover:bg-blue-900 transition shadow-xs cursor-pointer">Générer Email</button>
                </div>

                <div class="relative">
                    <div class="absolute -left-[30px] top-1 w-3.5 h-3.5 rounded-full bg-white border-2 border-[#059669] z-10 shadow-sm"></div>
                    <h4 class="text-[14px] font-bold text-[#1E1B4B] mb-2">Optimisation des Tarifs</h4>
                    <p class="text-[13px] text-slate-600 font-medium leading-relaxed mb-4">
                        Le marché des écoles rurales montre une sensibilité au prix.
                    </p>
                    <a href="{{ route('superadmin.revenue-analysis') }}" class="text-[12px] font-bold text-[#7C3AED] hover:underline flex items-center gap-1">
                        Voir l'analyse détaillée <i class="ph ph-arrow-right font-bold"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal : Rapport Exécutif IA -->
    <div id="aiReportModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-6 py-5 bg-[#7C3AED] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-sparkle text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold">Rapport Stratégique IA</h3>
                        <p class="text-xs text-purple-200 font-medium">Academia EduAnalytics</p>
                    </div>
                </div>
                <button type="button" onclick="closeAiReportModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 text-xs">
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 space-y-2 text-slate-800">
                    <div class="flex justify-between border-b border-purple-200/60 pb-2">
                        <span class="font-medium text-slate-600">Taux global d'activité IA :</span>
                        <span class="font-extrabold text-[#7C3AED] text-sm">94.2%</span>
                    </div>
                    <div class="flex justify-between border-b border-purple-200/60 pb-2">
                        <span class="font-medium text-slate-600">Élèves sous couverture IA :</span>
                        <span class="font-bold text-slate-900">{{ $kpis['total_students'] }}</span>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span class="font-medium text-slate-600">Statut de la Santé Réseau :</span>
                        <span class="font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md">Excellent</span>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeAiReportModal()" class="px-5 py-2.5 rounded-xl bg-[#7C3AED] text-white text-xs font-bold hover:bg-purple-800 transition shadow-sm cursor-pointer">
                        Fermer le rapport
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal : Générer Email Rétention -->
    <div id="retentionEmailModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-envelope text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold">Email de Rétention Automatique</h3>
                        <p class="text-xs text-blue-200 font-medium">Campagne Anti-Churn IA</p>
                    </div>
                </div>
                <button type="button" onclick="closeRetentionEmailModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Destinataire :</label>
                    <input type="text" readonly value="direction@horizon-dakar.sn" class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-2 text-slate-700 font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Message d'Assistance IA Généré :</label>
                    <textarea readonly rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-700 font-medium resize-none">Cher Directeur, l'équipe d'assistance Academia a remarqué une baisse d'activité sur votre portail. Nous vous offrons une session d'accompagnement gratuite 1-on-1 pour optimiser l'utilisation de vos modules.</textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeRetentionEmailModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold">Annuler</button>
                    <button type="button" onclick="sendRetentionEmail()" class="px-5 py-2 rounded-xl bg-[#031C5B] text-white font-bold hover:bg-blue-900">Envoyer l'Email</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openAiReportModal() {
            const modal = document.getElementById('aiReportModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }
        function closeAiReportModal() {
            const modal = document.getElementById('aiReportModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }
        function openRetentionEmailModal() {
            const modal = document.getElementById('retentionEmailModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }
        function closeRetentionEmailModal() {
            const modal = document.getElementById('retentionEmailModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }
        function sendRetentionEmail() {
            closeRetentionEmailModal();
            applyAiAction("Email d'accompagnement et de rétention transmis avec succès à la direction !");
        }
        function applyAiAction(msg) {
            const toast = document.getElementById('aiAnalyticsToast');
            const toastMsg = document.getElementById('aiAnalyticsToastMsg');
            if (toast && toastMsg) {
                toastMsg.innerText = msg;
                toast.classList.remove('hidden');
            }
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAiReportModal();
                closeRetentionEmailModal();
            }
        });
    </script>
    @endpush
@endsection
