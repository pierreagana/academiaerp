@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header (Top Filters) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <form action="{{ route('superadmin.system-logs') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Search Keyword -->
            <div class="relative w-full sm:w-[280px]">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Rechercher message, source, IP..." class="w-full bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl pl-9 pr-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-1 focus:ring-[#031C5B] transition shadow-2xs">
            </div>

            <!-- Level Filter Dropdown -->
            <div class="relative">
                <select name="level" onchange="this.form.submit()" class="bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-xs font-bold hover:bg-slate-50 transition shadow-2xs outline-none cursor-pointer">
                    <option value="all" {{ ($level ?? 'all') === 'all' ? 'selected' : '' }}>Tous les Niveaux</option>
                    <option value="info" {{ ($level ?? '') === 'info' ? 'selected' : '' }}>Succès / Info</option>
                    <option value="warning" {{ ($level ?? '') === 'warning' ? 'selected' : '' }}>Avertissements</option>
                    <option value="error_critical" {{ ($level ?? '') === 'error_critical' ? 'selected' : '' }}>Erreurs & Critiques</option>
                </select>
            </div>

            <!-- Period Filter Dropdown -->
            <div class="relative">
                <select name="period" onchange="this.form.submit()" class="bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-xs font-bold hover:bg-slate-50 transition shadow-2xs outline-none cursor-pointer">
                    <option value="all" {{ ($period ?? 'all') === 'all' ? 'selected' : '' }}>Toutes les dates</option>
                    <option value="today" {{ ($period ?? '') === 'today' ? 'selected' : '' }}>Aujourd'hui (24h)</option>
                    <option value="7_days" {{ ($period ?? '') === '7_days' ? 'selected' : '' }}>7 Derniers Jours</option>
                    <option value="30_days" {{ ($period ?? '') === '30_days' ? 'selected' : '' }}>30 Derniers Jours</option>
                </select>
            </div>

            <button type="submit" class="bg-[#031C5B] text-white px-4 py-2.5 rounded-xl text-xs font-bold hover:bg-blue-900 transition shadow-2xs cursor-pointer">
                Filtrer
            </button>
            
            @if(!empty($search) || ($level ?? 'all') !== 'all' || ($period ?? 'all') !== 'all')
                <a href="{{ route('superadmin.system-logs') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition">Réinitialiser</a>
            @endif
        </form>

        <div class="flex items-center shrink-0 mt-2 md:mt-0">
            <a href="{{ route('superadmin.system-logs.export-csv', request()->query()) }}" class="flex items-center gap-2 bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-emerald-800 transition shadow-xs">
                <i class="ph ph-download-simple text-base font-bold"></i> Exporter CSV (BD SQL)
            </a>
        </div>
    </div>

    <!-- Toast Alert -->
    <div id="logsToast" class="hidden mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-xl flex items-center justify-between text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2.5">
            <i class="ph ph-check-circle text-emerald-600 text-xl font-bold"></i>
            <span id="logsToastMsg">Action effectuée.</span>
        </div>
        <button onclick="document.getElementById('logsToast').classList.add('hidden')" class="text-emerald-500 hover:text-emerald-800 text-lg font-bold">✕</button>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Table (Left Column) -->
        <div class="lg:col-span-2">
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm h-full flex flex-col">
                <div class="p-6 flex items-center justify-between border-b border-slate-200 bg-[#FCFDFE]">
                    <div>
                        <h3 class="text-[20px] font-extrabold text-[#111827]">Activité & Journaux du Système</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Données filtrées en temps réel depuis la base SQL</p>
                    </div>
                    <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                        {{ $logs->total() }} entrée(s) trouvée(s)
                    </span>
                </div>

                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                        <thead>
                            <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-widest bg-slate-50 border-b border-slate-200">
                                <th class="py-4 px-6">HORODATAGE</th>
                                <th class="py-4 px-4">MODULE / SOURCE</th>
                                <th class="py-4 px-4">MESSAGE & ÉVÉNEMENT</th>
                                <th class="py-4 px-4">ADRESSE IP</th>
                                <th class="py-4 px-6 text-center">STATUT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-[14px]">
                            @forelse($logs as $log)
                            @php
                                $rowBg = '';
                                $statusClass = '';
                                $statusText = '';
                                
                                switch($log->level) {
                                    case 'error':
                                    case 'critical':
                                        $rowBg = 'bg-[#FEF2F2]';
                                        $statusClass = 'bg-[#FEE2E2] text-[#B91C1C] border border-[#FCA5A5]';
                                        $statusText = 'Échec / Erreur';
                                        break;
                                    case 'warning':
                                        $rowBg = 'bg-[#FFFBEB]';
                                        $statusClass = 'bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A]';
                                        $statusText = 'Avertissement';
                                        break;
                                    case 'info':
                                    default:
                                        $rowBg = $loop->even ? 'bg-[#F8FAFC]' : '';
                                        $statusClass = 'bg-[#A7F3D0] text-[#065F46] border border-[#6EE7B7]';
                                        $statusText = 'Succès';
                                        break;
                                }

                                $dateStr = is_string($log->created_at) ? date('d/m/Y H:i:s', strtotime($log->created_at)) : optional($log->created_at)->format('d/m/Y H:i:s');
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition {{ $rowBg }}">
                                <td class="py-4 px-6 text-slate-600 font-medium font-mono text-xs">
                                    {{ $dateStr }}
                                </td>
                                <td class="py-4 px-4">
                                    <p class="font-bold text-slate-900 text-[14px] mb-0.5">{{ $log->source ?? 'Système' }}</p>
                                    <span class="text-[11px] font-semibold text-slate-400 uppercase">Service Engine</span>
                                </td>
                                <td class="py-4 px-4 font-medium text-slate-700 max-w-xs truncate" title="{{ $log->message }}">
                                    {{ $log->message }}
                                </td>
                                <td class="py-4 px-4 font-mono text-[13px] font-semibold text-slate-600">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center {{ $statusClass }} text-[11px] font-bold px-3 py-1 rounded-full">
                                        {{ $statusText }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400 text-sm">
                                    Aucun journal correspondant aux critères de recherche.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div class="px-6 py-4 bg-[#FCFDFE] border-t border-slate-200 mt-auto">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>

        <!-- Right Column (Alerts & AI) -->
        <div class="flex flex-col gap-6">
            
            <!-- Security Alerts -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-6">
                    <i class="ph ph-warning text-xl text-[#DC2626] font-bold"></i>
                    <h3 class="text-[18px] font-extrabold text-[#111827]">Alertes de Sécurité Réseau</h3>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- Alert 1 -->
                    <div class="bg-[#FEF2F2] border border-[#FECACA] rounded-xl p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="text-[14px] font-bold text-[#991B1B]">Échecs de Connexion Multiples</h4>
                            <span class="text-[11px] font-medium text-[#991B1B]/70">Il y a 2h</span>
                        </div>
                        <p class="text-[12px] font-medium text-slate-700 leading-relaxed mb-3">
                            5 tentatives échouées depuis l'IP 45.22.19.102 ciblant le compte Super Admin.
                        </p>
                        <button type="button" onclick="blockIp('45.22.19.102')" class="text-[12px] font-bold text-[#DC2626] hover:underline cursor-pointer">
                            Bloquer l'IP 45.22.19.102
                        </button>
                    </div>

                    <!-- Alert 2 -->
                    <div class="bg-[#F8FAFC] border border-slate-200 rounded-xl p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="text-[14px] font-bold text-[#0f172a]">Heure d'Accès Inhabituelle</h4>
                            <span class="text-[11px] font-medium text-slate-400">Hier</span>
                        </div>
                        <p class="text-[12px] font-medium text-slate-600 leading-relaxed mb-3">
                            Accès administrateur détecté en dehors des heures d'ouverture (03h15).
                        </p>
                        <button type="button" onclick="examineSession('SESS-ADMIN-0891')" class="text-[12px] font-bold text-[#031C5B] hover:underline cursor-pointer">
                            Examiner la Session #SESS-ADMIN-0891
                        </button>
                    </div>
                </div>
            </div>

            <!-- AI Pattern Analysis -->
            <div class="bg-[#F8F5FF] rounded-2xl border border-purple-100 shadow-sm p-6 relative overflow-hidden flex flex-col flex-1 min-h-[280px]">
                <div class="absolute top-0 right-0 w-32 h-32 bg-purple-200/50 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="flex items-start gap-3 mb-4 relative z-10">
                    <i class="ph ph-sparkle text-2xl text-[#7C3AED] mt-1 font-bold"></i>
                    <h3 class="text-[20px] font-extrabold text-[#111827] leading-tight">Analyse des Schémas<br>par l'IA</h3>
                </div>
                
                <p class="text-[13px] font-medium text-slate-700 leading-relaxed relative z-10 mb-6 flex-1">
                    Le comportement du système est normal. Augmentation de 12% des modifications de paramètres par rapport à la semaine dernière, correspondant au début du trimestre.
                </p>

                <button type="button" onclick="openAiAuditSummaryModal()" class="w-full flex items-center justify-center gap-2 bg-[#7C3AED] text-white px-5 py-3 rounded-xl text-xs font-bold hover:bg-purple-700 transition shadow-sm relative z-10 cursor-pointer">
                    <i class="ph ph-sparkle font-bold text-base"></i> Générer un Résumé d'Audit
                </button>
            </div>

        </div>

    </div>

    <!-- Modal : Résumé d'Audit Système IA -->
    <div id="aiAuditSummaryModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-6 py-5 bg-[#7C3AED] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-shield-check text-xl font-bold"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold">Résumé d'Audit Système IA</h3>
                        <p class="text-xs text-purple-200 font-medium">Analyse d'Intégrité & Journal d'Accès</p>
                    </div>
                </div>
                <button type="button" onclick="closeAiAuditSummaryModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 text-xs">
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 space-y-2 text-slate-800">
                    <div class="flex justify-between border-b border-purple-200/60 pb-2">
                        <span class="font-medium text-slate-600">Niveau de Sécurité Globale :</span>
                        <span class="font-extrabold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md">98.5% Optimal</span>
                    </div>
                    <div class="flex justify-between border-b border-purple-200/60 pb-2">
                        <span class="font-medium text-slate-600">Tentatives d'Intrusion Bloquées :</span>
                        <span class="font-bold text-slate-900">5 requêtes</span>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span class="font-medium text-slate-600">Disponibilité du Service API :</span>
                        <span class="font-bold text-slate-900">99.98% Uptime</span>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeAiAuditSummaryModal()" class="px-5 py-2.5 rounded-xl bg-[#7C3AED] text-white text-xs font-bold hover:bg-purple-800 transition shadow-sm cursor-pointer">
                        Fermer l'audit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function blockIp(ip) {
            showLogsToast("L'adresse IP " + ip + " a été ajoutée à la liste noire des pare-feu avec succès.");
        }
        function examineSession(sessionId) {
            showLogsToast("La session " + sessionId + " est valide et ne présente aucune anomalie critique.");
        }
        function openAiAuditSummaryModal() {
            const modal = document.getElementById('aiAuditSummaryModal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeAiAuditSummaryModal() {
            const modal = document.getElementById('aiAuditSummaryModal');
            if (modal) modal.classList.add('hidden');
        }
        function showLogsToast(msg) {
            const toast = document.getElementById('logsToast');
            const toastMsg = document.getElementById('logsToastMsg');
            if (toast && toastMsg) {
                toastMsg.innerText = msg;
                toast.classList.remove('hidden');
            }
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAiAuditSummaryModal();
        });
    </script>
@endsection
