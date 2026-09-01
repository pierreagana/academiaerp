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
            <h2 class="text-[32px] font-extrabold text-[#111827]">Gestion des Inscriptions</h2>
            <p class="text-[15px] text-slate-600 mt-1">Suivez et gérez les demandes d'adhésion des nouvelles écoles à la plateforme.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-900 transition shadow-sm">
                <i class="ph ph-plus text-lg font-bold"></i> Nouvelle Inscription
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Stat 1 -->
        <div class="bg-white p-6 rounded-[16px] border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex justify-between items-start mb-6">
                <div class="w-[48px] h-[48px] rounded-[12px] bg-[#E1EDFF] flex items-center justify-center text-[#4B79DB]">
                    <i class="ph ph-clipboard-text text-[24px]"></i>
                </div>
                <span class="flex items-center gap-1 text-xs font-bold text-[#4B79DB] bg-[#E1EDFF] px-2 py-1 rounded-full">
                    Actives <i class="ph ph-trend-up"></i>
                </span>
            </div>
            <div>
                <h3 class="text-[32px] font-extrabold text-slate-900 mb-1">{{ $stats['pending'] ?? 0 }}</h3>
                <p class="text-[15px] text-slate-500 font-medium">Demandes en attente</p>
            </div>
        </div>

        <!-- Stat 2 -->
        <div class="bg-white p-6 rounded-[16px] border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex justify-between items-start mb-6">
                <div class="w-[48px] h-[48px] rounded-[12px] bg-[#0E794A] flex items-center justify-center text-white">
                    <i class="ph ph-check-circle text-[24px]"></i>
                </div>
                <span class="flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full">
                    Total validées
                </span>
            </div>
            <div>
                <h3 class="text-[32px] font-extrabold text-slate-900 mb-1">{{ $stats['approved'] ?? 0 }}</h3>
                <p class="text-[15px] text-slate-500 font-medium">Approuvées</p>
            </div>
        </div>

        <!-- Stat 3 -->
        <div class="bg-white p-6 rounded-[16px] border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex justify-between items-start mb-6">
                <div class="w-[48px] h-[48px] rounded-[12px] bg-[#6F1DDF] flex items-center justify-center text-white">
                    <i class="ph ph-chart-line-up text-[24px]"></i>
                </div>
                <span class="flex items-center gap-1 text-xs font-bold text-[#6F1DDF] bg-[#F1E5FF] px-3 py-1.5 rounded-full">
                    Statistique <i class="ph ph-chart-line-up"></i>
                </span>
            </div>
            <div>
                <h3 class="text-[32px] font-extrabold text-slate-900 mb-1">{{ $stats['conversion'] ?? 0 }}%</h3>
                <p class="text-[15px] text-slate-500 font-medium">Taux de conversion</p>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <form method="GET" action="{{ route('superadmin.registration-requests') }}" class="bg-white border-x border-t border-slate-200 rounded-t-[16px] p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher une école..." class="bg-slate-50 border border-slate-200 text-slate-700 px-4 py-2 rounded-lg text-[14px] font-medium outline-none focus:border-blue-500 pl-9 w-60">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            </div>

            <!-- Pays -->
            <div class="relative">
                <select name="country" onchange="this.form.submit()" class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 pr-8 pl-4 py-2 rounded-lg text-[14px] font-medium outline-none focus:border-blue-500">
                    <option value="">Tous les pays</option>
                    <option value="Sénégal" {{ request('country') == 'Sénégal' ? 'selected' : '' }}>Sénégal</option>
                    <option value="Côte d'Ivoire" {{ request('country') == "Côte d'Ivoire" ? 'selected' : '' }}>Côte d'Ivoire</option>
                    <option value="Cameroun" {{ request('country') == 'Cameroun' ? 'selected' : '' }}>Cameroun</option>
                    <option value="Mali" {{ request('country') == 'Mali' ? 'selected' : '' }}>Mali</option>
                </select>
                <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold pointer-events-none"></i>
            </div>

            <!-- Statut -->
            <div class="relative">
                <select name="status" onchange="this.form.submit()" class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 pr-8 pl-4 py-2 rounded-lg text-[14px] font-medium outline-none focus:border-blue-500">
                    <option value="">Tous les statuts</option>
                    <option value="en attente" {{ request('status') == 'en attente' ? 'selected' : '' }}>Nouveau / En attente</option>
                    <option value="rejeté" {{ request('status') == 'rejeté' ? 'selected' : '' }}>Rejeté</option>
                </select>
                <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold pointer-events-none"></i>
            </div>

            @if(request()->hasAny(['search', 'country', 'status']))
                <a href="{{ route('superadmin.registration-requests') }}" class="text-xs text-red-600 font-semibold hover:underline ml-2">Réinitialiser</a>
            @endif
        </div>
        
        <div>
            <button type="button" onclick="window.print()" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-lg text-[14px] font-semibold transition hover:bg-slate-50 shadow-sm">
                <i class="ph ph-download-simple text-lg font-bold"></i> Exporter
            </button>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white border-x border-b border-slate-200 rounded-b-[16px] overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="text-[13px] font-bold text-slate-500 bg-[#F8FAFC] border-y border-slate-200">
                        <th class="py-4 px-6 font-semibold">École & Contact</th>
                        <th class="py-4 px-4 font-semibold">Localisation</th>
                        <th class="py-4 px-4 font-semibold text-center">Forfait Demandé</th>
                        <th class="py-4 px-4 font-semibold">Date</th>
                        <th class="py-4 px-4 font-semibold text-center">Statut</th>
                        <th class="py-4 px-6 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @foreach($requests as $request)
                    @php
                        $dateParts = explode(',', $request->submittedAt);
                        $date = trim($dateParts[0]);
                        $time = isset($dateParts[1]) ? trim($dateParts[1]) : '';
                        
                        $cityCountry = explode(',', $request->region);
                        $city = trim($cityCountry[0]);
                        $country = isset($cityCountry[1]) ? trim($cityCountry[1]) : '';
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition group">
                        <td class="py-5 px-6">
                            <div class="flex items-center gap-4">
                                <div class="w-[48px] h-[48px] rounded-lg bg-[#E1EDFF] border border-blue-100 flex items-center justify-center text-[#4B79DB] font-bold text-sm shadow-sm overflow-hidden shrink-0">
                                    @if($loop->index == 0)
                                        <div class="w-6 h-4 bg-white shadow-sm flex items-center justify-center text-[10px]">🏫</div>
                                    @elseif($loop->index == 1)
                                        <span class="text-[18px]">IG</span>
                                    @elseif($loop->index == 2)
                                        <div class="w-6 h-4 bg-white shadow-sm flex items-center justify-center text-[10px]">🏫</div>
                                    @else
                                        <span class="text-[18px]">AF</span>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 text-[15px]">{{ $request->schoolName }}</p>
                                    <p class="text-[13px] text-slate-500 mt-0.5">{{ $request->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-5 px-4">
                            <p class="font-semibold text-slate-800 text-[14px]">{{ $city }}</p>
                            <p class="text-[13px] text-slate-500 mt-0.5">{{ $country }}</p>
                        </td>
                        <td class="py-5 px-4 text-center">
                            @if(str_contains(strtolower($request->packageRequested), 'premium') || str_contains(strtolower($request->packageRequested), 'entreprise') || str_contains(strtolower($request->packageRequested), 'enterprise'))
                                <span class="inline-flex items-center gap-1.5 bg-[#F1E5FF] text-[#6F1DDF] font-semibold px-3 py-2 rounded-lg text-[12px]">
                                    <i class="ph ph-sparkle text-[#6F1DDF]"></i> IA-Premium
                                </span>
                            @elseif(str_contains(strtolower($request->packageRequested), 'starter'))
                                <span class="inline-flex items-center bg-[#E1EDFF] text-[#4B79DB] font-semibold px-3 py-2 rounded-lg text-[12px]">
                                    Pro
                                </span>
                            @else
                                <span class="inline-flex items-center bg-[#E1EDFF] text-[#4B79DB] font-semibold px-3 py-2 rounded-lg text-[12px]">
                                    Base
                                </span>
                            @endif
                        </td>
                        <td class="py-5 px-4">
                            <p class="font-medium text-slate-800 text-[14px]">{{ $date }}</p>
                            <p class="text-[13px] text-slate-500 mt-0.5">{{ $time }}</p>
                        </td>
                        <td class="py-5 px-4 text-center">
                            @if(strtolower($request->status) == 'en attente')
                                <span class="inline-flex items-center gap-2 bg-[#E6EFFF] text-[#245CE5] text-[13px] font-semibold px-3 py-1.5 rounded-full">
                                    <div class="w-1.5 h-1.5 rounded-full bg-[#245CE5]"></div> Nouveau
                                </span>
                            @elseif(strtolower($request->status) == 'en cours d\'analyse')
                                <span class="inline-flex items-center gap-2 bg-[#F1F5F9] text-[#475569] text-[13px] font-semibold px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">
                                    <div class="w-1.5 h-1.5 rounded-full bg-[#64748B]"></div> En révision
                                </span>
                            @elseif(in_array(strtolower($request->status), ['validée', 'approuvé', 'approved', 'approuvée']))
                                <span class="inline-flex items-center gap-2 bg-[#DCFCE7] text-[#16A34A] text-[13px] font-semibold px-3 py-1.5 rounded-full">
                                    <div class="w-1.5 h-1.5 rounded-full bg-[#16A34A]"></div> Approuvé
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 bg-[#FEE2E2] text-[#DC2626] text-[13px] font-semibold px-3 py-1.5 rounded-full">
                                    <div class="w-1.5 h-1.5 rounded-full bg-[#DC2626]"></div> Rejeté
                                </span>
                            @endif
                        </td>
                        <td class="py-5 px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="relative inline-block text-left dropdown-container">
                                    <button onclick="toggleDropdown(this)" type="button" class="w-[34px] h-[34px] rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition shadow-sm">
                                        <i class="ph ph-dots-three-vertical text-[16px] font-bold pointer-events-none"></i>
                                    </button>
                                    <div class="dropdown-menu hidden absolute right-0 mt-1 z-50">
                                        <div class="w-48 bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden text-left">
                                            <a href="{{ route('superadmin.registration-requests.show', $request->id) }}" class="flex items-center px-4 py-2.5 text-[13px] font-medium text-slate-700 hover:bg-slate-50 transition">
                                                <i class="ph ph-eye text-[16px] text-slate-400 mr-2"></i> Voir les détails
                                            </a>
                                            @if(strtolower($request->status) == 'en attente' || strtolower($request->status) == 'en cours d\'analyse')
                                                <form action="{{ route('superadmin.registration-requests.approve', $request->id) }}" method="POST" class="w-full">
                                                    @csrf
                                                    <button type="submit" class="w-full flex items-center px-4 py-2.5 text-[13px] font-medium text-emerald-600 hover:bg-emerald-50 transition">
                                                        <i class="ph ph-check text-[16px] mr-2"></i> Approuver
                                                    </button>
                                                </form>
                                                <form action="{{ route('superadmin.registration-requests.reject', $request->id) }}" method="POST" class="w-full" onsubmit="return confirm('Rejeter cette demande ?');">
                                                    @csrf
                                                    <button type="submit" class="w-full flex items-center px-4 py-2.5 text-[13px] font-medium text-red-600 hover:bg-red-50 transition">
                                                        <i class="ph ph-x text-[16px] mr-2"></i> Rejeter
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Footer -->
        <div class="px-6 py-4 border-t border-slate-100 bg-[#FCFDFE]">
            {{ $requests->links() }}
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function toggleDropdown(button) {
        // Toggle the clicked dropdown
        const menu = button.nextElementSibling;
        const isHidden = menu.classList.contains('hidden');
        
        // Hide all other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
        
        if (isHidden) {
            menu.classList.remove('hidden');
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.dropdown-container')) {
            document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
        }
    });
</script>
@endpush
