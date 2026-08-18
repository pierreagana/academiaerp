@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::infirmary._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Aperçu du Jour</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">{{ now()->translatedFormat('l d F Y') }}</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Consultations du Jour</p>
                <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center text-blue-600"><i class="ph-fill ph-chart-line text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ number_format($stats['daily_consultations'], 0, ',', ' ') }}</h3>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Alertes Stock Faible</p>
                <div class="w-9 h-9 rounded-full bg-red-50 flex items-center justify-center text-red-600"><i class="ph-fill ph-first-aid-kit text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $stats['low_stock_alerts'] }}</h3>
            <p class="text-[12px] text-slate-400 font-semibold mt-1">Médicaments</p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Incidents Actifs</p>
                <div class="w-9 h-9 rounded-full bg-purple-50 flex items-center justify-center text-purple-600"><i class="ph-fill ph-asterisk text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $stats['active_incidents'] }}</h3>
            <p class="text-[12px] text-slate-400 font-semibold mt-1">{{ $stats['active_incidents'] > 0 ? 'Nécessite suivi' : 'Aucun suivi requis' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Consultation Motives Today -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Motifs de Consultation du Jour</h3>
            @php $maxCount = max(1, collect($motivesToday)->max('count') ?? 1); @endphp
            <div class="flex items-end justify-between gap-3 h-56">
                @forelse($motivesToday as $row)
                    @php $heightPct = $row['count'] > 0 ? max(6, round(($row['count'] / $maxCount) * 100)) : 3; @endphp
                    <div class="flex-1 flex flex-col items-center justify-end h-full gap-2">
                        <span class="text-[11px] font-bold text-slate-500">{{ $row['count'] }}</span>
                        <div class="w-full max-w-[48px] bg-[#1E3A8A] rounded-t-lg" style="height: {{ $heightPct }}%"></div>
                        <span class="text-[11px] font-semibold text-slate-500 text-center">{{ $row['motive'] }}</span>
                    </div>
                @empty
                    <p class="text-slate-400 text-[13px] text-center w-full">Aucune consultation aujourd'hui.</p>
                @endforelse
            </div>
        </div>

        <!-- AI Trends -->
        <div class="bg-gradient-to-br from-[#F5F3FF] to-purple-50/50 border border-purple-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <i class="ph-fill ph-sparkle text-purple-600 text-lg"></i>
                <h3 class="font-extrabold text-slate-800 text-[15px]">Tendances IA</h3>
            </div>
            @if($feverTrend['increased'])
                <div class="bg-white/70 rounded-xl p-4 border border-red-100">
                    <p class="text-[11.5px] font-bold text-red-600 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="ph-fill ph-warning"></i> Alerte Saisonnière</p>
                    <p class="text-[13px] text-slate-700 leading-relaxed">Hausse de <strong>{{ $feverTrend['percentChange'] }}%</strong> des consultations pour "{{ $feverTrend['motive'] }}" cette semaine ({{ $feverTrend['thisWeek'] }}) par rapport à la semaine dernière ({{ $feverTrend['lastWeek'] }}).</p>
                </div>
            @else
                <div class="bg-white/70 rounded-xl p-4 border border-slate-100">
                    <p class="text-[11.5px] font-bold text-emerald-600 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="ph-fill ph-check-circle"></i> Aucune Anomalie</p>
                    <p class="text-[13px] text-slate-700 leading-relaxed">Aucune hausse anormale des consultations pour "{{ $feverTrend['motive'] }}" cette semaine ({{ $feverTrend['thisWeek'] }}) par rapport à la semaine dernière ({{ $feverTrend['lastWeek'] }}).</p>
                </div>
            @endif
            <a href="{{ route('school.infirmary.interventions') }}" class="mt-4 inline-flex items-center justify-center w-full gap-2 border border-purple-300 text-purple-700 px-4 py-2.5 rounded-xl text-[13px] font-bold hover:bg-purple-50 transition">
                Voir les Interventions
            </a>
        </div>
    </div>

    <!-- Recent Interventions -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Interventions Récentes</h3>
            <a href="{{ route('school.infirmary.interventions') }}" class="text-[#031C5B] font-bold text-[13px] hover:underline">Voir tout</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Nom de l'Élève</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Classe</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Motif</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Heure</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut</th>
                    </tr>
                </thead>
                @php
                    $decisionStyles = ['retour_classe' => 'bg-emerald-100 text-emerald-700', 'repos_infirmerie' => 'bg-blue-100 text-blue-700', 'parents_appeles' => 'bg-red-100 text-red-700'];
                @endphp
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentInterventions as $intervention)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-[13.5px] font-bold text-slate-800">{{ $intervention->student->first_name ?? '' }} {{ $intervention->student->last_name ?? '' }}</td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $intervention->student->academicClass->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $intervention->motive }}</td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $intervention->arrival_time->format('H:i') }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $decisionStyles[$intervention->decision] ?? '' }}">
                                {{ \App\Modules\Infirmary\Domain\Models\Intervention::DECISIONS[$intervention->decision] ?? $intervention->decision }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-500 font-medium">Aucune intervention récente.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
