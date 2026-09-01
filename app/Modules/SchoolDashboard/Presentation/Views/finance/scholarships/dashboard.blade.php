@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::finance.scholarships._tabs')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Tableau de Bord Bourses</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Aperçu des fonds et attributions pour l'année scolaire en cours.</p>
        </div>
        @include('SchoolDashboard::finance.scholarships._scholarship_modal')
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Budget Total (Actif)</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ number_format($stats['totalBudget'], 0, ',', ' ') }} FCFA</h3>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Boursiers Actifs</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ number_format($stats['activeCount'], 0, ',', ' ') }}</h3>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Utilisation Budget</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ $stats['utilization'] }}%</h3>
            <div class="w-full h-1.5 bg-slate-100 rounded-full mt-2 overflow-hidden">
                <div class="h-full bg-[#031C5B] rounded-full" style="width: {{ min($stats['utilization'], 100) }}%"></div>
            </div>
        </div>
        <div class="bg-violet-50 rounded-2xl p-5 border border-violet-100 shadow-sm">
            <p class="text-[11px] font-bold text-violet-500 uppercase tracking-wider mb-2 flex items-center gap-1"><i class="ph-fill ph-sparkle"></i> Budget Restant</p>
            <h3 class="text-2xl font-bold text-violet-900">{{ number_format($remainingBudget / 1000000, 1) }}M FCFA</h3>
            <p class="text-[11px] text-violet-500 font-semibold">Non décaissé à ce jour</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Répartition par type -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Répartition par Type</h3>
            <div class="space-y-5">
                @forelse($breakdown as $b)
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[13px] font-semibold text-slate-700">{{ $b['name'] }}</span>
                        <span class="text-[13px] font-bold text-slate-900">{{ $b['percentage'] }}% ({{ number_format($b['amount'] / 1000000, 1) }}M FCFA)</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-[#031C5B] rounded-full" style="width: {{ $b['percentage'] }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-slate-400 text-[13px] text-center py-6">Aucune bourse active pour le moment.</p>
                @endforelse
            </div>
        </div>

        <!-- Insights (données réelles) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="ph-fill ph-sparkle text-violet-500"></i> Insights</h3>
            <div class="space-y-3">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <p class="text-[13px] text-slate-700"><span class="font-bold">Dossiers en attente :</span> {{ $pending->count() }} demande(s) de bourse à traiter.</p>
                </div>
                @if($topType)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <p class="text-[13px] text-slate-700"><span class="font-bold">Type le plus alloué :</span> {{ $topType['name'] }} ({{ $topType['percentage'] }}% du budget).</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Demandes en attente -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Demandes en Attente de Validation</h3>
            <a href="{{ route('school.finance.scholarships.students', ['status' => 'pending']) }}" class="text-[#031C5B] font-bold text-[13px] hover:underline">Voir tout</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Candidat</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Type de Bourse</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Moyenne Déclarée</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pending as $s)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="font-extrabold text-[#0F172A] text-[14px]">{{ $s->student->first_name }} {{ $s->student->last_name }}</div>
                            <div class="text-[12px] text-slate-500">{{ $s->student->academicClass->name ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-[11px] font-bold rounded uppercase">{{ $s->type->name ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-700">{{ $s->declared_average ? number_format($s->declared_average, 1) . '/20' : '-' }}</td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($s->priority)
                                <span class="px-2 py-1 bg-amber-50 text-amber-700 text-[11px] font-bold rounded-full mr-1"><i class="ph-fill ph-lightning"></i> Priorité</span>
                                @endif
                                <form action="{{ route('school.finance.scholarships.approve', $s->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition" title="Approuver">
                                        <i class="ph-bold ph-check"></i>
                                    </button>
                                </form>
                                <form action="{{ route('school.finance.scholarships.reject', $s->id) }}" method="POST" class="inline" onsubmit="return confirm('Rejeter cette demande ?');">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-100 transition" title="Rejeter">
                                        <i class="ph-bold ph-x"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-slate-500 font-medium">Aucune demande en attente.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
