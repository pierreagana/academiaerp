@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Breadcrumbs & Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-[14px] font-medium text-slate-500 mb-4">
            <a href="{{ route('superadmin.modules') }}" class="hover:text-slate-800 transition">Gestionnaire de Modules</a>
            <i class="ph ph-caret-right text-[12px]"></i>
            <span class="text-[#031C5B] font-bold">{{ $module['name'] }}</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-4 mb-2">
                    <h2 class="text-[32px] font-extrabold text-[#1E1B4B] leading-none">{{ $module['name'] }}</h2>
                    <span class="bg-[#EFF6FF] text-[#2563EB] text-[12px] font-bold px-3 py-1 rounded-full border border-[#BFDBFE]">
                        {{ str_starts_with($module['version'], 'v') ? $module['version'] : 'v' . $module['version'] }}
                    </span>
                    @if($module['status'] === 'active')
                        <span class="bg-[#ECFDF5] text-[#059669] text-[12px] font-bold px-3 py-1 rounded-full border border-[#A7F3D0]">Actif</span>
                    @else
                        <span class="bg-slate-100 text-slate-500 text-[12px] font-bold px-3 py-1 rounded-full border border-slate-200">{{ ucfirst($module['status']) }}</span>
                    @endif
                </div>
                <p class="text-[14px] font-medium text-slate-500">
                    {{ $module['description'] }}
                </p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl shadow-sm">
            <i class="ph ph-check-circle text-emerald-600 text-xl"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Main Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

        <!-- Left Column -->
        <div class="lg:col-span-2 flex flex-col gap-8">

            <!-- Features Section -->
            <div class="bg-white border border-slate-200 rounded-[24px] p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <i class="ph ph-sliders text-2xl text-[#7C3AED] font-bold"></i>
                    <h3 class="text-[20px] font-extrabold text-[#1E1B4B]">Fonctionnalités du Module</h3>
                </div>

                <div class="space-y-4">
                    @forelse($module['features'] as $feature)
                        <div class="bg-slate-50/70 border border-slate-200 rounded-xl p-5">
                            <h4 class="text-[15px] font-bold text-[#111827] mb-1">{{ $feature['title'] }}</h4>
                            <p class="text-[13px] font-medium text-slate-500 leading-relaxed">
                                {{ $feature['desc'] }}
                            </p>
                        </div>
                    @empty
                        <p class="text-[13px] text-slate-400">Aucune fonctionnalité documentée pour ce module.</p>
                    @endforelse
                </div>
            </div>

            <!-- Déploiement par Établissement Section -->
            <div class="bg-white rounded-[24px] border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-slate-200 flex items-center gap-3">
                    <i class="ph ph-hierarchy text-2xl text-[#031C5B] font-bold"></i>
                    <h3 class="text-[20px] font-extrabold text-[#111827]">Utilisation par Établissement</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-widest bg-[#F8FAFC] border-b border-slate-200">
                                <th class="py-4 px-6">ÉTABLISSEMENT</th>
                                <th class="py-4 px-4">LOCALISATION</th>
                                <th class="py-4 px-4 text-center">UTILISATION DU MODULE</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-[14px]">
                            @forelse($module['schools'] as $school)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-4 px-6 font-bold text-slate-700">{{ $school['name'] }}</td>
                                    <td class="py-4 px-4 text-slate-500 font-medium">{{ $school['location'] }}</td>
                                    <td class="py-4 px-4 text-center">
                                        @if($school['adopted'])
                                            <span class="inline-flex bg-[#ECFDF5] text-[#059669] text-[11px] font-bold px-3 py-1 rounded-full border border-[#A7F3D0]">
                                                Données réelles présentes
                                            </span>
                                        @else
                                            <span class="inline-flex bg-slate-100 text-slate-500 text-[11px] font-bold px-3 py-1 rounded-full border border-slate-200">
                                                Non utilisé
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 px-6 text-center text-slate-400 text-[13px]">Aucun établissement enregistré.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Column -->
        <div class="flex flex-col gap-6">

            <!-- Stats Card -->
            <div class="bg-white rounded-[24px] border border-slate-200 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-6">
                    <i class="ph ph-chart-bar text-2xl text-[#031C5B] font-bold"></i>
                    <h3 class="text-[18px] font-extrabold text-[#111827]">Statistiques</h3>
                </div>

                <div class="space-y-5">
                    <div>
                        <div class="flex justify-between items-end mb-1.5">
                            <span class="text-[12px] font-bold text-slate-600">Adoption Écoles</span>
                            <span class="text-[13px] font-bold text-[#031C5B]">{{ $module['usage_pct'] }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-blue-50 rounded-full overflow-hidden mb-1">
                            <div class="h-full bg-[#031C5B] rounded-full" style="width: {{ $module['usage_pct'] }}%"></div>
                        </div>
                        <p class="text-[11px] text-slate-400">{{ $module['adopted_count'] }} / {{ $module['total_schools'] }} établissements</p>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-[13px] font-medium text-slate-500">MRR généré</span>
                        <span class="text-[15px] font-bold text-slate-900">{{ number_format($module['revenue_mrr'], 0, ',', ' ') }} {{ $systemCurrency ?? 'FCFA' }}</span>
                    </div>
                </div>

                <p class="text-[11px] font-medium text-slate-400 mt-6 pt-4 border-t border-slate-100 leading-relaxed">
                    L'adoption est mesurée à partir des données réelles saisies par chaque établissement dans ce module.
                </p>
            </div>

            <!-- Pricing Card -->
            <div class="bg-white rounded-[24px] border border-slate-200 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-5">
                    <i class="ph ph-tag text-2xl text-[#031C5B] font-bold"></i>
                    <h3 class="text-[18px] font-extrabold text-[#111827]">Tarification</h3>
                </div>

                <form action="{{ route('superadmin.module-details.update-price', $module['slug']) }}" method="POST" class="flex flex-col gap-3">
                    @csrf
                    <label class="text-[12px] font-bold text-slate-600">Prix du module ({{ $systemCurrency ?? 'FCFA' }}/an)</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $module['price']) }}"
                               class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-[14px] font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#031C5B]/20" required>
                        <button type="submit" class="px-4 py-2.5 bg-[#031C5B] hover:bg-[#052a7a] text-white text-[13px] font-bold rounded-xl transition">
                            Enregistrer
                        </button>
                    </div>
                    @error('price')
                        <p class="text-[12px] text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </form>
                <p class="text-[11px] font-medium text-slate-400 mt-4 pt-4 border-t border-slate-100 leading-relaxed">
                    Ce prix est celui facturé aux établissements qui demandent l'activation de ce module en extension payante.
                </p>
            </div>

        </div>

    </div>
@endsection
