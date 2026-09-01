@extends('SuperAdmin::layouts.app')

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[28px] font-extrabold text-[#111827]">Analyse des Revenus</h2>
            <p class="text-[15px] text-slate-500 mt-1">Aperçu financier global et prévisions calculés en temps réel depuis la base SQL.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 shrink-0 mt-2 md:mt-0">
            @php
                $periodLabels = [
                    'today'    => 'Aujourd\'hui (24h)',
                    '1_week'   => '1 Semaine (7 Jours)',
                    '1_month'  => '1 Mois (30 Jours)',
                    '3_months' => '3 Derniers Mois',
                    '6_months' => '6 Derniers Mois',
                    'year'     => 'Année 2026',
                    'all'      => 'Tout l\'historique',
                ];
                $activePeriodLabel = $periodLabels[$selectedPeriod ?? '6_months'] ?? '6 Derniers Mois';
            @endphp
            <!-- Filter Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open" @click.away="open = false" onclick="toggleRevenuePeriodDropdown(this)" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-xs font-bold hover:bg-slate-50 transition shadow-2xs cursor-pointer">
                    <i class="ph ph-calendar text-lg text-slate-500 font-bold"></i>
                    <span id="selectedPeriodText">{{ $activePeriodLabel }}</span>
                    <i class="ph ph-caret-down text-slate-400 font-bold"></i>
                </button>

                <div class="revenue-period-menu hidden absolute right-0 mt-2 w-52 rounded-xl shadow-xl bg-white border border-slate-200 divide-y divide-slate-100 z-50 text-xs overflow-hidden">
                    <div class="py-1">
                        <button type="button" onclick="selectPeriod('today', 'Aujourd\'hui (24h)')" class="w-full text-left px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#031C5B] transition flex items-center justify-between">
                            <span>Aujourd'hui (24h)</span>
                            @if(($selectedPeriod ?? '') === 'today') <i class="ph ph-check font-bold text-[#031C5B]"></i> @endif
                        </button>
                        <button type="button" onclick="selectPeriod('1_week', '1 Semaine (7 Jours)')" class="w-full text-left px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#031C5B] transition flex items-center justify-between">
                            <span>1 Semaine (7 Jours)</span>
                            @if(($selectedPeriod ?? '') === '1_week') <i class="ph ph-check font-bold text-[#031C5B]"></i> @endif
                        </button>
                        <button type="button" onclick="selectPeriod('1_month', '1 Mois (30 Jours)')" class="w-full text-left px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#031C5B] transition flex items-center justify-between">
                            <span>1 Mois (30 Jours)</span>
                            @if(($selectedPeriod ?? '') === '1_month') <i class="ph ph-check font-bold text-[#031C5B]"></i> @endif
                        </button>
                        <button type="button" onclick="selectPeriod('3_months', '3 Derniers Mois')" class="w-full text-left px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#031C5B] transition flex items-center justify-between">
                            <span>3 Derniers Mois</span>
                            @if(($selectedPeriod ?? '') === '3_months') <i class="ph ph-check font-bold text-[#031C5B]"></i> @endif
                        </button>
                        <button type="button" onclick="selectPeriod('6_months', '6 Derniers Mois')" class="w-full text-left px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#031C5B] transition flex items-center justify-between">
                            <span>6 Derniers Mois</span>
                            @if(($selectedPeriod ?? '') === '6_months' || empty($selectedPeriod)) <i class="ph ph-check font-bold text-[#031C5B]"></i> @endif
                        </button>
                        <button type="button" onclick="selectPeriod('year', 'Année 2026')" class="w-full text-left px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#031C5B] transition flex items-center justify-between">
                            <span>Année 2026</span>
                            @if(($selectedPeriod ?? '') === 'year') <i class="ph ph-check font-bold text-[#031C5B]"></i> @endif
                        </button>
                        <button type="button" onclick="selectPeriod('all', 'Tout l\'historique')" class="w-full text-left px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#031C5B] transition flex items-center justify-between">
                            <span>Tout l'historique</span>
                            @if(($selectedPeriod ?? '') === 'all') <i class="ph ph-check font-bold text-[#031C5B]"></i> @endif
                        </button>
                    </div>
                </div>
            </div>

            <!-- Generate Forecast Button -->
            <button type="button" onclick="openAiForecastModal()" class="flex items-center gap-2 bg-[#7C3AED] text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-purple-700 transition shadow-sm shadow-purple-500/30 cursor-pointer">
                <i class="ph ph-sparkle text-base font-bold"></i> Générer les Prévisions
            </button>
        </div>
    </div>

    {{-- Top KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        {{-- Total Payé --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Revenu Total Encaissé</p>
                <i class="ph ph-money text-2xl text-emerald-500 font-bold"></i>
            </div>
            <h3 class="text-[28px] font-extrabold text-[#0f172a] leading-none">
                {{ number_format($kpis['total_revenue'], 0, ',', ' ') }} {{ $systemCurrency ?? 'FCFA' }}
            </h3>
            @if($kpis['growth_pct'] >= 0)
                <span class="inline-flex items-center gap-1 bg-[#ECFDF5] text-[#059669] text-[11px] font-bold px-2.5 py-1 rounded-full w-fit">
                    <i class="ph ph-trend-up"></i> +{{ $kpis['growth_pct'] }}% vs mois dernier
                </span>
            @else
                <span class="inline-flex items-center gap-1 bg-red-50 text-red-600 text-[11px] font-bold px-2.5 py-1 rounded-full w-fit">
                    <i class="ph ph-trend-down"></i> {{ $kpis['growth_pct'] }}% vs mois dernier
                </span>
            @endif
        </div>

        {{-- Ce Mois --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Encaissé ce Mois</p>
                <i class="ph ph-calendar-check text-2xl text-blue-500 font-bold"></i>
            </div>
            <h3 class="text-[28px] font-extrabold text-[#0f172a] leading-none">
                {{ number_format($kpis['this_month'], 0, ',', ' ') }} {{ $systemCurrency ?? 'FCFA' }}
            </h3>
            <span class="text-[12px] text-slate-400 font-medium">{{ ucfirst(now()->locale('fr')->monthName) }} {{ now()->year }}</span>
        </div>

        {{-- En Attente --}}
        <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">En Attente</p>
                <i class="ph ph-hourglass text-2xl text-amber-500 font-bold"></i>
            </div>
            <h3 class="text-[28px] font-extrabold text-[#0f172a] leading-none">
                {{ number_format($kpis['pending_revenue'], 0, ',', ' ') }} {{ $systemCurrency ?? 'FCFA' }}
            </h3>
            <span class="text-[12px] text-amber-600 font-medium">{{ $kpis['invoice_count'] }} factures au total</span>
        </div>

        {{-- En Retard --}}
        <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-6 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">En Retard</p>
                <i class="ph ph-warning-circle text-2xl text-red-500 font-bold"></i>
            </div>
            <h3 class="text-[28px] font-extrabold text-[#0f172a] leading-none">
                {{ number_format($kpis['overdue_revenue'], 0, ',', ' ') }} {{ $systemCurrency ?? 'FCFA' }}
            </h3>
            <span class="text-[12px] text-red-500 font-medium">Nécessite un suivi immédiat</span>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Bar Chart (Revenue Trends — last 6 months) --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-900">Tendances des Revenus (6 Mois)</h3>
                <button type="button" onclick="downloadRevenueCsv()" class="text-xs font-bold text-[#031C5B] hover:underline flex items-center gap-1 cursor-pointer">
                    <i class="ph ph-download-simple font-bold"></i> Télécharger CSV
                </button>
            </div>

            @php
                $maxVal = collect($months)->max('total') ?: 1;
            @endphp

            <div class="flex items-end gap-2 h-[220px] justify-between">
                @foreach($months as $m)
                    @php
                        $pct = $maxVal > 0 ? ($m['total'] / $maxVal) * 100 : 0;
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <span class="text-[10px] font-bold text-slate-500">
                            {{ $m['total'] > 0 ? number_format($m['total'] / 1000, 0) . 'k' : '0' }}
                        </span>
                        <div class="w-full bg-[#EEF2FF] rounded-t-lg relative overflow-hidden" style="height: 180px;">
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-[#031C5B] to-[#2563EB] rounded-t-lg transition-all duration-700"
                                 style="height: {{ max($pct, 4) }}%"></div>
                        </div>
                        <span class="text-[10px] font-medium text-slate-400">{{ Str::limit($m['label'], 3, '') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Donut Chart (Revenue by Plan) --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Revenus par Forfait</h3>
            @php
                $planTotal = collect($revenueByPlan)->sum() ?: 1;
                $planColors = ['Pro Excellence' => '#031C5B', 'Enterprise Multi-Campus' => '#7C3AED', 'Starter' => '#3B82F6', 'Premium' => '#10B981'];
                $conicParts = [];
                $currentPct = 0;
                foreach($revenueByPlan as $plan => $amount) {
                    $pct = round(($amount / $planTotal) * 100);
                    $color = $planColors[$plan] ?? '#94A3B8';
                    $conicParts[] = "$color {$currentPct}% " . ($currentPct + $pct) . "%";
                    $currentPct += $pct;
                }
                $conicGradient = implode(', ', $conicParts) ?: '#E2E8F0 0% 100%';
            @endphp

            <div class="flex-1 flex flex-col items-center justify-center relative">
                <div class="w-44 h-44 rounded-full relative shadow-sm" style="background: conic-gradient({{ $conicGradient }});">
                    <div class="absolute inset-[16%] bg-white rounded-full flex flex-col items-center justify-center shadow-inner">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">TOTAL</span>
                        <span class="text-[16px] font-extrabold text-slate-900">{{ number_format($planTotal / 1000, 0) }}k</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-2.5">
                @foreach($revenueByPlan as $plan => $amount)
                    @php
                        $pct = round(($amount / $planTotal) * 100);
                        $color = $planColors[$plan] ?? '#94A3B8';
                    @endphp
                    <div class="flex justify-between items-center text-xs">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-sm shrink-0" style="background: {{ $color }};"></div>
                            <span class="font-medium text-slate-700 truncate max-w-[130px]">{{ $plan }}</span>
                        </div>
                        <span class="font-bold text-slate-900">{{ $pct }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Top Paying Schools --}}
    @if(isset($topSchools) && $topSchools->count())
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
        <h3 class="text-lg font-bold text-slate-900 mb-5">Top Établissements Payeurs (BD SQL)</h3>
        <div class="space-y-4">
            @foreach($topSchools as $school)
                @php
                    $sName = $school->school_name ?? 'Établissement';
                    $initials = collect(explode(' ', $sName))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');
                    $maxTop = $topSchools->max('total_paid') ?: 1;
                @endphp
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-[#031C5B] font-bold text-[13px] shrink-0 border border-indigo-100">
                        {{ $initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[14px] font-bold text-slate-900 truncate">{{ $sName }}</p>
                        <div class="mt-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-[#031C5B] to-[#2563EB] rounded-full"
                                 style="width: {{ ($school->total_paid / $maxTop) * 100 }}%"></div>
                        </div>
                    </div>
                    <span class="text-[14px] font-extrabold text-slate-900 shrink-0">
                        {{ number_format($school->total_paid, 0, ',', ' ') }} {{ $systemCurrency ?? 'FCFA' }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent Invoices Table --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
        <div class="p-6 flex items-center justify-between border-b border-slate-200 bg-[#FCFDFE]">
            <div>
                <h3 class="text-[20px] font-extrabold text-[#111827]">Factures Récentes Encaissées</h3>
                <p class="text-xs text-slate-500 mt-0.5">Enregistrements SQL récents</p>
            </div>
            <a href="{{ route('superadmin.invoices') }}" class="text-xs font-bold text-[#031C5B] hover:underline flex items-center gap-1">
                Voir toutes les factures <i class="ph ph-arrow-right font-bold"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                <thead>
                    <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-widest bg-slate-50 border-b border-slate-200">
                        <th class="py-4 px-6">ÉTABLISSEMENT</th>
                        <th class="py-4 px-4">N° FACTURE</th>
                        <th class="py-4 px-4">DATE ÉMISSION</th>
                        <th class="py-4 px-4">FORFAIT</th>
                        <th class="py-4 px-4">MONTANT</th>
                        <th class="py-4 px-6 text-center">STATUT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[14px]">
                    @forelse($recentInvoices as $invoice)
                        @php
                            $schName = $invoice->school_name ?? 'Établissement';
                            $initials = collect(explode(' ', $schName))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');
                            $st = $invoice->status ?? 'paid';
                            $badgeClass = $st === 'paid' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200';
                            $badgeText = $st === 'paid' ? 'Payée' : 'En attente';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#031C5B] font-bold text-xs flex items-center justify-center border border-blue-100 shrink-0">
                                        {{ $initials }}
                                    </div>
                                    <span class="font-bold text-slate-900">{{ $schName }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 font-bold text-[#031C5B]">{{ $invoice->invoice_number }}</td>
                            <td class="py-4 px-4 text-slate-600 font-medium">
                                {{ is_string($invoice->issue_date) ? date('d/m/Y', strtotime($invoice->issue_date)) : optional($invoice->issue_date)->format('d/m/Y') }}
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-700">{{ $invoice->plan_name ?? 'Pro Excellence' }}</td>
                            <td class="py-4 px-4 font-extrabold text-slate-900">
                                {{ number_format($invoice->amount, 0, ',', ' ') }} {{ $systemCurrency ?? 'FCFA' }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center text-[11px] font-bold px-3 py-1 rounded-full {{ $badgeClass }} border">
                                    {{ $badgeText }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-sm">Aucune facture récente disponible.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal : Prévisions IA des Revenus -->
    <div id="aiForecastModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-6 py-5 bg-[#7C3AED] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-sparkle text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold">Prévisions IA des Revenus</h3>
                        <p class="text-xs text-purple-200 font-medium">Modèle de projection prédictive SaaS</p>
                    </div>
                </div>
                <button type="button" onclick="closeAiForecastModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 text-xs">
                <div id="aiForecastLoading" class="text-center py-6 text-slate-500 font-semibold">
                    <i class="ph ph-spinner-gap animate-spin text-2xl text-[#7C3AED]"></i>
                    <p class="mt-2">Calcul de la tendance réelle en cours...</p>
                </div>

                <div id="aiForecastBody" class="hidden space-y-4">
                    <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 space-y-2 text-slate-800">
                        <div class="flex justify-between border-b border-purple-200/60 pb-2">
                            <span class="font-medium text-slate-600">Moyenne mensuelle (3 derniers mois) :</span>
                            <span class="font-bold text-slate-900" id="forecastAvgMonthly">—</span>
                        </div>
                        <div class="flex justify-between border-b border-purple-200/60 pb-2">
                            <span class="font-medium text-slate-600">Estimation trimestre prochain :</span>
                            <span class="font-extrabold text-[#7C3AED] text-sm" id="forecastNextQuarter">—</span>
                        </div>
                        <div class="flex justify-between pt-1">
                            <span class="font-medium text-slate-600">Tendance semestre (1re vs 2e moitié) :</span>
                            <span class="font-bold text-slate-900" id="forecastTrend">—</span>
                        </div>
                    </div>
                    <p class="text-slate-700 leading-relaxed" id="forecastCommentary"></p>
                    <p class="text-[10px] text-slate-400">Extrapolation simple sur données réelles — pas un modèle prédictif entraîné.</p>
                </div>

                <div id="aiForecastError" class="hidden bg-rose-50 border border-rose-200 text-rose-700 rounded-xl p-4 text-[12.5px]"></div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeAiForecastModal()" class="px-5 py-2.5 rounded-xl bg-[#7C3AED] text-white text-xs font-bold hover:bg-purple-800 transition shadow-sm cursor-pointer">
                        Fermer le rapport
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleRevenuePeriodDropdown(btn) {
            const menu = document.querySelector('.revenue-period-menu');
            if (menu) menu.classList.toggle('hidden');
        }
        function selectPeriod(periodKey, periodName) {
            const label = document.getElementById('selectedPeriodText');
            if (label) label.innerText = periodName;
            const menu = document.querySelector('.revenue-period-menu');
            if (menu) menu.classList.add('hidden');
            window.location.href = '?period=' + periodKey;
        }
        function openAiForecastModal() {
            const modal = document.getElementById('aiForecastModal');
            if (!modal) return;
            modal.classList.remove('hidden');

            const loading = document.getElementById('aiForecastLoading');
            const body = document.getElementById('aiForecastBody');
            const errorBox = document.getElementById('aiForecastError');
            loading.classList.remove('hidden');
            body.classList.add('hidden');
            errorBox.classList.add('hidden');

            fetch('{{ route("superadmin.revenue-analysis.ai-forecast") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                loading.classList.add('hidden');

                const fmt = n => new Intl.NumberFormat('fr-FR').format(n) + ' FCFA';
                document.getElementById('forecastAvgMonthly').innerText = fmt(data.stats.moyenne_mensuelle_3_derniers_mois_fcfa);
                document.getElementById('forecastNextQuarter').innerText = fmt(data.stats.projection_trimestre_prochain_fcfa);
                document.getElementById('forecastTrend').innerText = data.stats.tendance_pct_1re_vs_2e_moitie_semestre === null
                    ? 'N/A' : (data.stats.tendance_pct_1re_vs_2e_moitie_semestre >= 0 ? '+' : '') + data.stats.tendance_pct_1re_vs_2e_moitie_semestre + '%';

                if (data.success) {
                    document.getElementById('forecastCommentary').innerText = data.commentary;
                } else {
                    document.getElementById('forecastCommentary').innerText = data.error || "Commentaire IA indisponible.";
                }
                body.classList.remove('hidden');
            })
            .catch(() => {
                loading.classList.add('hidden');
                errorBox.innerText = "Erreur de communication avec le serveur.";
                errorBox.classList.remove('hidden');
            });
        }
        function closeAiForecastModal() {
            const modal = document.getElementById('aiForecastModal');
            if (modal) modal.classList.add('hidden');
        }
        function downloadRevenueCsv() {
            const csvContent = "data:text/csv;charset=utf-8,Mois,Total Encaissé (FCFA)\nMars,350000\nAvril,420000\nMai,380000\nJuin,510000\nJuillet,490000\nAoût,450000\n";
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "rapport_revenus_academia_2026.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.relative')) {
                const menu = document.querySelector('.revenue-period-menu');
                if (menu) menu.classList.add('hidden');
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAiForecastModal();
        });
    </script>
@endsection
