@extends('SuperAdmin::layouts.app')

@section('content')
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl shadow-sm">
            <i class="ph ph-check-circle text-emerald-600 text-xl"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl shadow-sm">
            <i class="ph ph-warning-circle text-red-600 text-xl"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[32px] font-extrabold text-[#111827]">Alertes Systèmes</h2>
            <p class="text-[15px] text-slate-500 mt-1">Gestion des règles de seuils de tolérance et des alertes de la plateforme.</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <button onclick="window.location.reload()" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-950 transition shadow-sm">
                <i class="ph ph-arrows-clockwise text-base font-bold"></i> Actualiser les Alertes
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        
        <!-- KPI 1: RÈGLES CONFIGURÉES -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-slate-500 tracking-wider uppercase">Règles Configurées</span>
                <i class="ph ph-sliders text-slate-400 text-lg"></i>
            </div>
            <div>
                <h3 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-1">{{ $kpis['total_alerts'] }}</h3>
                <p class="text-xs font-medium text-slate-400">Seuils de tolérance définis</p>
            </div>
        </div>

        <!-- KPI 2: CRITICAL TRIGGERS -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-slate-500 tracking-wider uppercase">Règles Critiques</span>
                <i class="ph ph-warning-circle text-red-500 text-xl font-bold"></i>
            </div>
            <div>
                <h3 class="text-3xl font-extrabold text-red-600 tracking-tight mb-1">{{ $kpis['critical_triggers'] }}</h3>
                <p class="text-xs font-medium text-slate-400">
                    Sévérité critique
                </p>
            </div>
        </div>

        <!-- KPI 3: RÈGLES ACTIVES -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-slate-500 tracking-wider uppercase">Règles Actives</span>
                <i class="ph ph-check-circle text-emerald-500 text-lg"></i>
            </div>
            <div>
                <h3 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-1">{{ $kpis['active_rules'] }} / {{ $kpis['total_alerts'] }}</h3>
                <p class="text-xs font-medium text-slate-400">
                    {{ $kpis['active_rules_pct'] }}% de couverture
                </p>
            </div>
        </div>

    </div>

    <!-- Main Section (Left Table + Right Panels) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- LEFT AREA: Recent Alerts Table (Occupies 2 Cols on lg) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
            <div>
                <!-- Header with Title, Search, Filter -->
                <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100">
                    <h3 class="text-xl font-bold text-slate-900">Recent Alerts</h3>
                    
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1 sm:w-64">
                            <i class="ph ph-magnifying-glass text-slate-400 absolute left-3.5 top-3 text-sm pointer-events-none"></i>
                            <input type="text" placeholder="Search alerts..." class="w-full bg-slate-50/80 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-blue-600">
                        </div>
                        <button class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition shadow-xs">
                            <i class="ph ph-faders text-sm"></i> Filter
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest bg-slate-50/50 border-b border-slate-100">
                                <th class="py-3.5 px-6">SEVERITY</th>
                                <th class="py-3.5 px-4">CATEGORY</th>
                                <th class="py-3.5 px-6">DETAILS</th>
                                <th class="py-3.5 px-4">STATUS</th>
                                <th class="py-3.5 px-6 text-right">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-medium">
                            @foreach($alerts as $alert)
                                <tr class="hover:bg-slate-50/60 transition">
                                    
                                    <!-- Severity Badge -->
                                    <td class="py-4 px-6">
                                        @if($alert['severity_type'] === 'critical')
                                            <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 text-[11px] font-bold px-3 py-1 rounded-full border border-red-200/60">
                                                <div class="w-1.5 h-1.5 rounded-full bg-red-600"></div> Critical
                                            </span>
                                        @elseif($alert['severity_type'] === 'warning')
                                            <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 text-[11px] font-bold px-3 py-1 rounded-full border border-amber-200/60">
                                                <div class="w-1.5 h-1.5 rounded-full bg-amber-600"></div> Warning
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 text-[11px] font-bold px-3 py-1 rounded-full border border-blue-200/60">
                                                <div class="w-1.5 h-1.5 rounded-full bg-blue-600"></div> {{ ucfirst($alert['severity_type']) }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Category -->
                                    <td class="py-4 px-4 font-bold text-slate-800">
                                        {{ $alert['category'] }}
                                    </td>

                                    <!-- Details -->
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-900 text-xs leading-snug">{{ $alert['title'] }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $alert['context'] }}</p>
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="py-4 px-4">
                                        @if($alert['is_active'])
                                            <span class="inline-flex items-center bg-emerald-100/70 text-emerald-800 text-[11px] font-bold px-2.5 py-0.5 rounded-md">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center bg-slate-100 text-slate-500 text-[11px] font-bold px-2.5 py-0.5 rounded-md">
                                                Désactivée
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Actions Dropdown -->
                                    <td class="py-4 px-6 text-right">
                                        <div class="relative inline-block text-left dropdown-container">
                                            <button onclick="toggleDropdown(this)" type="button" class="w-[32px] h-[32px] rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition shadow-xs">
                                                <i class="ph ph-dots-three-vertical text-[16px] font-bold pointer-events-none"></i>
                                            </button>
                                            <div class="dropdown-menu hidden absolute right-0 mt-1 z-50">
                                                <div class="w-48 bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden text-left">
                                                    <form action="{{ route('superadmin.system-alerts.toggle', $alert['id']) }}" method="POST" class="w-full">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-xs font-semibold {{ $alert['is_active'] ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50' }} transition text-left">
                                                            <i class="ph {{ $alert['is_active'] ? 'ph-pause-circle text-amber-600' : 'ph-check-circle text-emerald-600' }} text-base"></i>
                                                            {{ $alert['is_active'] ? 'Désactiver alerte' : 'Activer alerte' }}
                                                        </button>
                                                    </form>
                                                    <div class="border-t border-slate-100"></div>
                                                    <form action="{{ route('superadmin.system-alerts.destroy', $alert['id']) }}" method="POST" onsubmit="return confirm('Supprimer cette règle d\'alerte ?');" class="w-full">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition text-left">
                                                            <i class="ph ph-trash text-base text-red-500"></i> Supprimer règle
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT AREA: Panels (Configurations + Notification History) -->
        <div class="space-y-6">
            
            <!-- Configurations Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-slate-900">Configurations</h3>
                    <a href="/superadmin/specific-configuration" class="text-xs font-bold text-blue-600 hover:underline">Edit</a>
                </div>

                <div class="space-y-4">
                    @foreach($configurations as $configItem)
                        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 mb-0.5">{{ $configItem['title'] }}</h4>
                                <p class="text-[11px] text-slate-400">{{ $configItem['subtitle'] }}</p>
                            </div>
                            <span class="bg-blue-50 text-blue-700 text-xs font-bold px-3 py-1.5 rounded-lg border border-blue-100">
                                {{ $configItem['value'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Severity Breakdown Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <h3 class="text-lg font-bold text-slate-900 mb-1">Répartition par Sévérité</h3>
                <p class="text-xs font-medium text-slate-400 mb-6">Règles configurées par niveau</p>

                <div class="space-y-4">
                    @foreach($severityBreakdown as $bucket)
                        @php $pct = $kpis['total_alerts'] > 0 ? round(($bucket['count'] / $kpis['total_alerts']) * 100) : 0; @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold text-slate-600 mb-1">
                                <span>{{ $bucket['label'] }}</span>
                                <span>{{ $bucket['count'] }}</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="{{ $bucket['color'] }} h-full rounded-full" style="width: {{ $pct }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>
@endsection
