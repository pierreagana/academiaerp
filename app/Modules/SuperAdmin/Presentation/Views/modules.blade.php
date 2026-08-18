@extends('SuperAdmin::layouts.app')

@section('content')
    @php
        $activeCount = collect($modules)->where('status', 'active')->count();
        $otherCount = collect($modules)->count() - $activeCount;
    @endphp

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[28px] font-extrabold text-[#111827]">Gestionnaire de Modules</h2>
            <p class="text-[15px] text-slate-500 mt-1 max-w-3xl">
                Modules réellement disponibles dans la plateforme, avec leur adoption mesurée sur les établissements connectés.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3 shrink-0 mt-2 md:mt-0">
            <a href="{{ route('superadmin.packages') }}" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-50 transition shadow-sm">
                Gérer les Tarifs
            </a>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">

        <!-- Left Area: Modules Déployés (occupies 2 columns on xl) -->
        <div class="xl:col-span-2 flex flex-col gap-4">

            <div class="flex items-center justify-between mb-2">
                <h3 class="text-[18px] font-extrabold text-[#111827]">Modules Déployés</h3>
                <p class="text-[14px] font-medium text-slate-500">{{ $activeCount }} Actif(s){{ $otherCount > 0 ? ', ' . $otherCount . ' Autre(s)' : '' }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($modules as $module)
                @php
                    $isActive = $module->status === 'active';
                    $isBeta = $module->status === 'beta';

                    $cardClass = $isBeta ? 'bg-white opacity-80' : 'bg-white hover:border-[#7C3AED] hover:shadow-md';
                    $iconBg = $isBeta ? 'bg-slate-100 text-slate-500' : 'bg-blue-50 text-[#031C5B]';
                    $titleColor = $isBeta ? 'text-slate-700' : 'text-[#111827]';
                    $statusBadgeBg = $isActive ? 'bg-[#A7F3D0] text-[#065F46]' : 'bg-slate-100 text-slate-600';
                    $statusDot = $isActive ? 'bg-[#065F46]' : 'bg-slate-400';
                    $statusText = $isActive ? 'Actif' : ucfirst($module->status);
                    $adoptionRate = $module->usage_pct;
                @endphp
                <!-- Module Card -->
                <div onclick="window.location.href='/superadmin/module-details/{{ $module->slug ?? 'scolaire' }}'" class="{{ $cardClass }} rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden flex flex-col transition cursor-pointer">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl {{ $iconBg }} flex items-center justify-center shrink-0">
                                <i class="{{ $module->icon }} text-2xl font-bold"></i>
                            </div>
                            <div>
                                <h4 class="text-[16px] font-extrabold {{ $titleColor }} leading-tight">{{ $module->name }}</h4>
                                <p class="text-[12px] font-medium text-slate-400 mt-1">v {{ $module->version }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 {{ $statusBadgeBg }} text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wider shrink-0">
                            <div class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></div> {{ $statusText }}
                        </span>
                    </div>
                    <p class="text-[13px] font-medium text-slate-500 leading-relaxed mb-6 flex-1">
                        {{ $module->description }}
                    </p>
                    <div class="w-full">
                        <div class="flex justify-between items-end mb-1.5">
                            <span class="text-[12px] font-bold text-slate-700">Adoption Écoles</span>
                            <span class="text-[13px] font-bold text-[#031C5B]">{{ $adoptionRate }}% ({{ $module->active_schools_count }})</span>
                        </div>
                        <div class="w-full h-1.5 bg-blue-50 rounded-full overflow-hidden">
                            <div class="h-full bg-[#031C5B] rounded-full" style="width: {{ $adoptionRate }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Right Area: Résumé du Catalogue -->
        <div class="xl:col-span-1">
            <div class="bg-white rounded-[24px] border border-slate-200 shadow-sm p-6 sticky top-6">

                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-100">
                    <div class="w-12 h-12 rounded-xl bg-[#F5F3FF] text-[#7C3AED] flex items-center justify-center shrink-0">
                        <i class="ph ph-chart-bar text-2xl font-bold"></i>
                    </div>
                    <div>
                        <h3 class="text-[18px] font-extrabold text-[#111827] leading-tight">Résumé du<br>Catalogue</h3>
                    </div>
                </div>

                @php
                    $modulesCollection = collect($modules);
                    $mostAdopted = $modulesCollection->sortByDesc('active_schools_count')->first();
                    $totalModuleMrr = $modulesCollection->sum('revenue_mrr');
                @endphp

                <div class="space-y-5">
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] font-medium text-slate-500">Modules disponibles</span>
                        <span class="text-[15px] font-bold text-slate-900">{{ $modulesCollection->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] font-medium text-slate-500">Module le plus adopté</span>
                        <span class="text-[13px] font-bold text-slate-900 text-right">{{ $mostAdopted->name ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] font-medium text-slate-500">MRR modules payants</span>
                        <span class="text-[15px] font-bold text-slate-900">{{ number_format($totalModuleMrr, 0, ',', ' ') }} {{ $systemCurrency ?? 'FCFA' }}</span>
                    </div>
                </div>

                <p class="text-[11px] font-medium text-slate-400 mt-6 pt-4 border-t border-slate-100 leading-relaxed">
                    L'adoption est mesurée à partir des données réelles saisies par chaque établissement dans ce module.
                </p>
            </div>
        </div>
    </div>

@endsection
