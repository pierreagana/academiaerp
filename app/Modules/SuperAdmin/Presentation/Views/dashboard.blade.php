@extends('SuperAdmin::layouts.app')

@section('content')
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">{{ __('dashboard_title') }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ __('dashboard_welcome') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('superadmin.schools') }}" class="flex items-center gap-2 text-slate-600 bg-white border border-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
                        <i class="ph ph-plus"></i> {{ __('add_school') }}
                    </a>
                    <a href="{{ route('superadmin.revenue-analysis') }}" class="flex items-center gap-2 bg-indigo-100 text-indigo-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-200 transition">
                        <i class="ph ph-download-simple"></i> {{ __('generate_saas_report') }}
                    </a>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                
                <!-- Stat 1 -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <i class="ph ph-graduation-cap text-xl"></i>
                        </div>
                        @if($stats->totalSchoolsGrowth)
                            @php $up = str_starts_with($stats->totalSchoolsGrowth, '+') || $stats->totalSchoolsGrowth === 'Nouveau'; @endphp
                            <span class="flex items-center gap-1 text-xs font-semibold {{ $up ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50' }} px-2 py-1 rounded-full">
                                <i class="ph {{ $up ? 'ph-trend-up' : 'ph-trend-down' }}"></i> {{ $stats->totalSchoolsGrowth }}
                            </span>
                        @endif
                    </div>
                    <div class="mt-4">
                        <h3 class="text-3xl font-bold text-slate-900">{{ number_format($stats->totalSchools, 0, ',', ' ') }}</h3>
                        <p class="text-sm text-slate-500 font-medium">{{ __('total_schools') }}</p>
                    </div>
                </div>

                <!-- Stat 2 -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <i class="ph ph-check-circle text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-3xl font-bold text-slate-900">{{ number_format($stats->activeSubscriptions, 0, ',', ' ') }}</h3>
                        <p class="text-sm text-slate-500 font-medium">{{ __('active_subscriptions') }}</p>
                    </div>
                </div>

                <!-- Stat 3 -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600">
                            <i class="ph ph-money text-xl"></i>
                        </div>
                        @if($stats->totalRevenuesGrowth)
                            @php $upRev = str_starts_with($stats->totalRevenuesGrowth, '+') || $stats->totalRevenuesGrowth === 'Nouveau'; @endphp
                            <span class="flex items-center gap-1 text-xs font-semibold {{ $upRev ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50' }} px-2 py-1 rounded-full">
                                <i class="ph {{ $upRev ? 'ph-trend-up' : 'ph-trend-down' }}"></i> {{ $stats->totalRevenuesGrowth }}
                            </span>
                        @endif
                    </div>
                    <div class="mt-4">
                        <h3 class="text-3xl font-bold text-slate-900">{{ $stats->totalRevenues }}</h3>
                        <p class="text-sm text-slate-500 font-medium">{{ __('total_mrr') }}</p>
                    </div>
                </div>

                <!-- Stat: Total Students -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                            <i class="ph ph-student text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-3xl font-bold text-slate-900">{{ number_format($totalStudents, 0, ',', ' ') }}</h3>
                        <p class="text-sm text-slate-500 font-medium">{{ __('total_students') }}</p>
                    </div>
                </div>

                <!-- Stat: Registered Parents -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center text-sky-600">
                            <i class="ph ph-users-three text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-3xl font-bold text-slate-900">{{ number_format($totalParents, 0, ',', ' ') }}</h3>
                        <p class="text-sm text-slate-500 font-medium">{{ __('total_parents') }}</p>
                    </div>
                </div>

                <!-- Pending Approvals Card -->
                <a href="{{ route('superadmin.registration-requests') }}" class="bg-white p-6 rounded-2xl border-2 border-indigo-100 shadow-sm shadow-indigo-100 relative overflow-hidden flex flex-col justify-center hover:border-indigo-200 transition">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <i class="ph ph-clipboard-text text-6xl text-indigo-500"></i>
                    </div>
                    <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md uppercase tracking-wider w-max mb-3">File d'attente</span>
                    <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center text-white mb-2 shadow-md shadow-indigo-200">
                        <i class="ph ph-clipboard-text text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-indigo-900">{{ \App\Modules\SuperAdmin\Domain\Models\RegistrationRequest::whereIn('status', ['en attente', 'pending', 'nouveau'])->count() }} demande(s) en attente</h3>
                    <p class="text-sm text-indigo-600/80 font-medium">{{ __('validation_required') }}</p>
                </a>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Revenue Chart Placeholder -->
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">{{ __('revenue_growth') }}</h3>
                            <p class="text-sm text-slate-500">{{ __('revenue_performance') }}</p>
                        </div>
                        <button class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                            {{ __('monthly') }} <i class="ph ph-caret-down text-slate-400"></i>
                        </button>
                    </div>
                    
                    <!-- Real 6-month paid-invoice revenue trend -->
                    @php
                        $maxRevenue = collect($stats->monthlyRevenueData)->max('value') ?: 1;
                        $lastMonth = collect($stats->monthlyRevenueData)->last();
                    @endphp
                    <div class="h-64 relative flex items-end justify-between px-4 pb-8 pt-4">
                        <!-- Y Axis Guides -->
                        <div class="absolute inset-0 flex flex-col justify-between pb-8 pointer-events-none">
                            <div class="border-b border-slate-100 w-full h-0"></div>
                            <div class="border-b border-slate-100 w-full h-0"></div>
                            <div class="border-b border-slate-100 w-full h-0"></div>
                            <div class="border-b border-slate-100 w-full h-0"></div>
                            <div class="border-b border-slate-200 w-full h-0"></div>
                        </div>
                        @foreach($stats->monthlyRevenueData as $i => $month)
                            @php
                                $heightPct = $maxRevenue > 0 ? max(4, round(($month['value'] / $maxRevenue) * 100)) : 4;
                                $isLast = $i === count($stats->monthlyRevenueData) - 1;
                            @endphp
                            <div class="relative w-12 z-10 flex flex-col justify-end" style="height: 100%;">
                                @if($isLast && $month['value'] > 0)
                                    <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs font-bold px-2 py-1 rounded shadow-md whitespace-nowrap z-20">{{ number_format($month['value'], 0, ',', ' ') }}</div>
                                @endif
                                <div class="w-full {{ $isLast ? 'bg-indigo-600' : 'bg-indigo-500' }} rounded-t-sm" style="height: {{ $heightPct }}%;"></div>
                                <span class="absolute -bottom-6 text-xs {{ $isLast ? 'text-slate-800 font-bold' : 'text-slate-400 font-medium' }} left-1/2 -translate-x-1/2">{{ $month['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    @if($maxRevenue <= 1 && (!$lastMonth || $lastMonth['value'] == 0))
                        <p class="text-center text-xs text-slate-400 -mt-2">Aucun revenu encaissé sur les 6 derniers mois.</p>
                    @endif
                </div>

                <!-- Donut Chart Placeholder -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col">
                    <h3 class="text-lg font-bold text-slate-900">{{ __('packages_distribution') }}</h3>
                    <p class="text-sm text-slate-500 mb-6">{{ __('schools_per_level') }}</p>
                    
                    <div class="flex-1 flex flex-col items-center justify-center relative">
                        <!-- Mockup Donut -->
                        <div class="w-40 h-40 rounded-full border-[16px] border-indigo-600 border-r-emerald-600 border-t-purple-500 flex items-center justify-center">
                            <div class="text-center">
                                <span class="block text-2xl font-bold text-slate-900">{{ $planStats['total'] }}</span>
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('total') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-indigo-600"></span><span class="text-slate-600">IA-Premium</span></div>
                            <span class="font-bold text-slate-900">{{ $planStats['premium'] }}%</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-purple-500"></span><span class="text-slate-600">Pro</span></div>
                            <span class="font-bold text-slate-900">{{ $planStats['pro'] }}%</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-600"></span><span class="text-slate-600">Basique</span></div>
                            <span class="font-bold text-slate-900">{{ $planStats['basic'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section (Table & Quick {{ __('actions') }}) -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                <!-- Table -->
                <div class="lg:col-span-3 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-slate-900">{{ __('recent_registrations') }}</h3>
                        <a href="{{ route('superadmin.schools') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition">{{ __('view_all_schools') }}</a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-xs font-bold text-slate-500 uppercase tracking-wider bg-slate-50 border-y border-slate-100">
                                    <th class="py-3 px-4 font-semibold">{{ __('school_name') }}</th>
                                    <th class="py-3 px-4 font-semibold">{{ __('country') }}</th>
                                    <th class="py-3 px-4 font-semibold">{{ __('package') }}</th>
                                    <th class="py-3 px-4 font-semibold">{{ __('status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($recentSchools as $school)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded bg-slate-100 flex items-center justify-center font-bold text-slate-500 text-sm">
                                                {{ strtoupper(substr($school->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('superadmin.schools.show', $school->id) }}" class="font-bold text-slate-900 text-sm hover:text-indigo-600 transition">{{ $school->name }}</a>
                                                <p class="text-xs text-slate-400">ID Reg: {{ str_replace('#', 'EDU-', '#SCH-' . str_pad($school->id, 3, '0', STR_PAD_LEFT)) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-sm text-slate-600 flex items-center gap-2">
                                        <span class="text-lg">🌍</span> {{ $school->location ?? 'N/A' }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wide bg-indigo-50 text-indigo-700">{{ $school->plan_name }}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        @if(strtolower($school->status) == 'actif')
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wide bg-emerald-50 text-emerald-700">{{ __('active') }}</span>
                                        @elseif(strtolower($school->status) == 'en attente')
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wide bg-blue-50 text-blue-700">{{ __('pending') }}</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wide bg-red-50 text-red-700">{{ __('suspended') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick {{ __('actions') }} sidebar -->
                <div class="bg-slate-100 p-6 rounded-2xl border border-slate-200">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">{{ __('actions') }} Rapides</h3>
                    
                    <div class="space-y-4">
                        <a href="{{ route('superadmin.packages') }}" class="flex items-center gap-4 p-4 bg-white rounded-xl shadow-sm hover:shadow transition border border-transparent hover:border-slate-200">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600">
                                <i class="ph ph-archive-box text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">{{ __('create_package') }}</h4>
                                <p class="text-xs text-slate-500">{{ __('define_pricing') }}</p>
                            </div>
                        </a>

                        <a href="{{ route('superadmin.support') }}" class="flex items-center gap-4 p-4 bg-white rounded-xl shadow-sm hover:shadow transition border border-transparent hover:border-slate-200">
                            <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600">
                                <i class="ph ph-headset text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">{{ __('global_support') }}</h4>
                                <p class="text-xs text-slate-500">{{ $activeTicketsCount }} ticket(s) actif(s)</p>
                            </div>
                        </a>

                        <a href="{{ route('superadmin.network-health') }}" class="flex items-center gap-4 p-4 bg-white rounded-xl shadow-sm hover:shadow transition border border-transparent hover:border-slate-200">
                            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                                <i class="ph ph-shield-check text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Suivi Système</h4>
                                <p class="text-xs text-slate-500">Base de données, file d'attente, stockage</p>
                            </div>
                        </a>
                    </div>
                </div>
                
            </div>
@endsection
