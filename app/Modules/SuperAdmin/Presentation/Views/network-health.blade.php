@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[32px] font-extrabold text-[#111827]">Santé du Réseau</h2>
            <p class="text-[15px] text-slate-500 mt-1">État réel de la base de données, du cache, de la file d'attente et du stockage.</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <button onclick="window.location.reload()" class="flex items-center gap-2 bg-[#7C3AED] text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-purple-700 transition shadow-sm">
                <i class="ph ph-arrows-clockwise text-base font-bold"></i> Vérifier Maintenant
            </button>
        </div>
    </div>

    @php
        $statusMeta = [
            'ok'      => ['label' => 'Opérationnel', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60', 'dot' => 'bg-emerald-600'],
            'warning' => ['label' => 'Attention', 'badge' => 'bg-amber-50 text-amber-700 border-amber-200/60', 'dot' => 'bg-amber-600'],
            'down'    => ['label' => 'Indisponible', 'badge' => 'bg-red-50 text-red-700 border-red-200/60', 'dot' => 'bg-red-600'],
            'unknown' => ['label' => 'Inconnu', 'badge' => 'bg-slate-100 text-slate-500 border-slate-200', 'dot' => 'bg-slate-400'],
        ];
        $overall = $statusMeta[$kpis['overall_status']] ?? $statusMeta['unknown'];
    @endphp

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <!-- Overall Status -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 tracking-wider uppercase">État Général</span>
                <div class="w-7 h-7 rounded-full {{ $overall['badge'] }} flex items-center justify-center">
                    <i class="ph ph-heartbeat text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight mb-1">{{ $overall['label'] }}</h3>
                <p class="text-xs font-medium text-slate-400">{{ $kpis['active_schools'] }} / {{ $kpis['total_schools'] }} établissements actifs</p>
            </div>
        </div>

        <!-- DB Latency -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 tracking-wider uppercase">Latence Base de Données</span>
                <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="ph ph-gauge text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-1">{{ $kpis['db_latency_ms'] }} ms</h3>
                <p class="text-xs font-medium text-slate-400">{{ $kpis['db_ok'] ? 'Connexion active' : 'Connexion en échec' }}</p>
            </div>
        </div>

        <!-- Queue -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 tracking-wider uppercase">File d'Attente</span>
                <div class="w-7 h-7 rounded-full {{ $kpis['failed_jobs'] > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
                    <i class="ph ph-stack text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-1">{{ $kpis['pending_jobs'] }}</h3>
                <p class="text-xs font-medium {{ $kpis['failed_jobs'] > 0 ? 'text-amber-600' : 'text-slate-400' }}">{{ $kpis['failed_jobs'] }} échec(s)</p>
            </div>
        </div>

        <!-- Disk -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 tracking-wider uppercase">Stockage</span>
                <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center">
                    <i class="ph ph-hard-drive text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-1">{{ $kpis['disk_used_pct'] !== null ? $kpis['disk_used_pct'] . '%' : '—' }}</h3>
                <p class="text-xs font-medium text-slate-400">Espace utilisé</p>
            </div>
        </div>

    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- LEFT COLUMN -->
        <div class="lg:col-span-2 space-y-8">

            <!-- System Components -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <h3 class="text-lg font-extrabold text-slate-900 mb-4">Composants Système</h3>

                <div class="space-y-3">
                    @foreach($components as $component)
                        @php $meta = $statusMeta[$component['status']] ?? $statusMeta['unknown']; @endphp
                        <div class="flex items-center justify-between p-4 border border-slate-200/70 rounded-xl bg-slate-50/30">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <i class="ph {{ $component['icon'] }} text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">{{ $component['name'] }}</h4>
                                    <p class="text-xs text-slate-500">{{ $component['detail'] }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 {{ $meta['badge'] }} text-xs font-bold px-3 py-1 rounded-full border">
                                <div class="w-1.5 h-1.5 rounded-full {{ $meta['dot'] }}"></div> {{ $meta['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN -->
        <div class="space-y-8">

            @if(count($notices) > 0)
            <!-- Notices -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <h3 class="text-lg font-extrabold text-slate-900 mb-4">Points d'Attention</h3>
                <div class="space-y-3">
                    @foreach($notices as $notice)
                        <div class="flex items-start gap-3 p-3 rounded-xl {{ $notice['type'] === 'error' ? 'bg-red-50' : 'bg-amber-50' }}">
                            <i class="ph ph-warning-circle {{ $notice['type'] === 'error' ? 'text-red-600' : 'text-amber-600' }} text-lg mt-0.5"></i>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900">{{ $notice['title'] }}</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ $notice['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Events -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-extrabold text-slate-900">Événements Récents</h3>
                    <a href="{{ route('superadmin.system-logs') }}" class="text-xs font-bold text-blue-600 hover:underline">Voir Tout</a>
                </div>

                @if(count($recentEvents) > 0)
                    <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                        @foreach($recentEvents as $event)
                            @php
                                $dotColor = $event['level'] === 'critical' || $event['level'] === 'error' ? 'bg-red-500' : ($event['level'] === 'warning' ? 'bg-amber-500' : 'bg-slate-400');
                            @endphp
                            <div class="relative">
                                <div class="absolute -left-[23px] top-1 w-2.5 h-2.5 rounded-full {{ $dotColor }} ring-4 ring-white"></div>
                                <p class="text-[11px] font-semibold text-slate-400 mb-0.5">{{ $event['timestamp'] }}</p>
                                <h4 class="text-xs font-bold text-slate-900 leading-snug mb-1">{{ $event['message'] }}</h4>
                                <p class="text-[11px] text-slate-500 leading-relaxed">Source : {{ $event['source'] ?? 'système' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400">Aucun événement enregistré.</p>
                @endif
            </div>

        </div>

    </div>
@endsection
