@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 pb-10">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
        <div>
            <h1 class="text-[26px] font-bold text-slate-900 tracking-tight">Bienvenue, {{ auth()->user()->name }}</h1>
            <p class="text-slate-500 text-[14px] mt-1">Voici ce qui se passe à {{ $school->name ?? 'votre établissement' }} aujourd'hui.</p>
        </div>
        @if(auth()->user()->isBranchDirector())
        <div class="flex items-center gap-3 bg-white p-2 pr-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-600">
                <i class="ph-fill ph-buildings text-[20px]"></i>
            </div>
            <div class="flex flex-col text-left">
                <span class="text-[12.5px] font-bold text-slate-800 leading-tight">{{ $activeBranch->name ?? ($school->name ?? 'Branche Principale') }}</span>
                <span class="text-[10px] font-medium text-slate-500">Votre succursale</span>
            </div>
        </div>
        @else
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open" class="flex items-center gap-3 bg-white p-2 pr-4 rounded-xl border border-slate-200 shadow-sm hover:border-slate-300 transition">
                <div class="w-10 h-10 {{ $activeBranch ? 'bg-slate-100 text-slate-600' : 'bg-purple-100 text-purple-600' }} rounded-lg flex items-center justify-center">
                    <i class="ph-fill {{ $activeBranch ? 'ph-buildings' : 'ph-globe-hemisphere-west' }} text-[20px]"></i>
                </div>
                <div class="flex flex-col text-left">
                    <span class="text-[12.5px] font-bold text-slate-800 leading-tight">{{ $activeBranch->name ?? 'Vue Globale' }}</span>
                    <span class="text-[10px] font-medium text-slate-500">Changer de Branche</span>
                </div>
                <i class="ph-bold ph-caret-down text-[12px] text-slate-400"></i>
            </button>

            <div x-show="open" x-transition.opacity x-cloak class="absolute right-0 top-full mt-2 w-64 bg-white border border-slate-200 rounded-xl shadow-lg z-20 py-1.5" style="display: none;">
                @if($branches->count() > 1)
                <form action="{{ route('school.branches.switch') }}" method="POST">
                    @csrf
                    <input type="hidden" name="branch_id" value="all">
                    <button type="submit" class="w-full flex items-center justify-between gap-2 px-4 py-2 text-[13px] font-semibold text-left transition {{ !$activeBranch ? 'text-purple-700 bg-purple-50' : 'text-purple-600 hover:bg-purple-50' }}">
                        <span class="flex items-center gap-2"><i class="ph-bold ph-globe-hemisphere-west text-[14px]"></i> Vue Globale (toutes succursales)</span>
                        @if(!$activeBranch)
                            <i class="ph-bold ph-check text-[14px]"></i>
                        @endif
                    </button>
                </form>
                <div class="h-px bg-slate-100 my-1"></div>
                @endif
                @foreach($branches as $branch)
                    <form action="{{ route('school.branches.switch') }}" method="POST">
                        @csrf
                        <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                        <button type="submit" class="w-full flex items-center justify-between gap-2 px-4 py-2 text-[13px] font-semibold text-left transition {{ $activeBranch && $activeBranch->id === $branch->id ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-700 hover:bg-slate-50' }}">
                            <span>{{ $branch->name }}{{ $branch->type ? ' (' . $branch->type . ')' : '' }}</span>
                            @if($activeBranch && $activeBranch->id === $branch->id)
                                <i class="ph-bold ph-check text-[14px]"></i>
                            @endif
                        </button>
                    </form>
                @endforeach
                @if(auth()->user()->canAccess('branches.manage'))
                <div class="h-px bg-slate-100 my-1"></div>
                <a href="{{ route('school.branches') }}" class="flex items-center gap-2 px-4 py-2 text-[12.5px] font-semibold text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition">
                    <i class="ph-bold ph-gear text-[14px]"></i>
                    Gérer les succursales
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Stat 1 -->
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-700">
                    <i class="ph-fill ph-users text-[20px]"></i>
                </div>
                <span class="px-2 py-1 bg-green-50 text-green-600 text-[11px] font-bold rounded-md">+12%</span>
            </div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Total Élèves</h3>
            <p class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_students'], 0, ',', ' ') }}</p>
        </div>
        
        <!-- Stat 2 -->
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600">
                    <i class="ph-fill ph-chalkboard-teacher text-[20px]"></i>
                </div>
                <span class="px-2 py-1 bg-slate-100 text-slate-600 text-[11px] font-bold rounded-md">Stable</span>
            </div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Enseignants</h3>
            <p class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_teachers'], 0, ',', ' ') }}</p>
        </div>
        
        <!-- Stat 3 -->
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600">
                    <i class="ph-fill ph-identification-badge text-[20px]"></i>
                </div>
                <span class="px-2 py-1 bg-blue-50 text-blue-600 text-[11px] font-bold rounded-md">3 Nouveaux</span>
            </div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Personnel</h3>
            <p class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_staff'], 0, ',', ' ') }}</p>
        </div>
        
        <!-- Stat 4 -->
        <a href="{{ route('school.academic.presence.attendance') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative overflow-hidden hover:border-slate-200 transition">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                    <i class="ph-fill ph-user-check text-[20px]"></i>
                </div>
                <span class="px-2 py-1 bg-slate-100 text-slate-600 text-[11px] font-bold rounded-md">Aujourd'hui</span>
            </div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Présents Aujourd'hui</h3>
            <p class="text-2xl font-bold text-slate-900">{{ number_format($attendanceStats['present'], 0, ',', ' ') }}</p>
            <p class="text-[11px] text-slate-400 mt-1">{{ $attendanceStats['recorded'] > 0 ? 'sur ' . $attendanceStats['recorded'] . ' enregistré(s)' : 'Présence non prise aujourd\'hui' }}</p>
        </a>

        <!-- Stat 5: Absents -->
        <a href="{{ route('school.academic.presence.attendance') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative overflow-hidden hover:border-slate-200 transition">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center text-red-600">
                    <i class="ph-fill ph-user-minus text-[20px]"></i>
                </div>
                <span class="px-2 py-1 bg-slate-100 text-slate-600 text-[11px] font-bold rounded-md">Aujourd'hui</span>
            </div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Absents Aujourd'hui</h3>
            <p class="text-2xl font-bold text-slate-900">{{ number_format($attendanceStats['absent'], 0, ',', ' ') }}</p>
            <p class="text-[11px] text-slate-400 mt-1">{{ $attendanceStats['recorded'] > 0 ? 'sur ' . $attendanceStats['recorded'] . ' enregistré(s)' : 'Présence non prise aujourd\'hui' }}</p>
        </a>

        <!-- Stat 6: Retards -->
        <a href="{{ route('school.academic.presence.attendance') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative overflow-hidden hover:border-slate-200 transition">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                    <i class="ph-fill ph-clock text-[20px]"></i>
                </div>
                <span class="px-2 py-1 bg-slate-100 text-slate-600 text-[11px] font-bold rounded-md">Aujourd'hui</span>
            </div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Retards Aujourd'hui</h3>
            <p class="text-2xl font-bold text-slate-900">{{ number_format($attendanceStats['late'], 0, ',', ' ') }}</p>
            <p class="text-[11px] text-slate-400 mt-1">{{ $attendanceStats['recorded'] > 0 ? 'sur ' . $attendanceStats['recorded'] . ' enregistré(s)' : 'Présence non prise aujourd\'hui' }}</p>
        </a>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column (Activities & AI) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Activité Académique -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="ph ph-calendar text-slate-500 text-xl"></i>
                        Activité Académique
                    </h2>
                    <a href="{{ route('school.communication.events.calendar') }}" class="text-[12.5px] font-semibold text-slate-500 hover:text-slate-800">Voir le Calendrier</a>
                </div>

                @php
                    $eventTypeLabels = ['scolaire' => 'Scolaire', 'sportif' => 'Sportif', 'culturel' => 'Culturel', 'administratif' => 'Administratif'];
                    $eventTypeStyles = ['scolaire' => 'bg-amber-50 text-amber-700', 'sportif' => 'bg-green-50 text-green-700', 'culturel' => 'bg-purple-50 text-purple-700', 'administratif' => 'bg-blue-50 text-blue-700'];
                @endphp
                <div class="space-y-4">
                    @forelse($upcomingEvents as $event)
                        @if(!$loop->first)
                            <hr class="border-slate-100">
                        @endif
                        <div class="flex items-start gap-4">
                            <div class="w-14 shrink-0 flex flex-col items-center justify-center border border-slate-200 rounded-lg py-1.5">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">{{ $event->start_at->translatedFormat('M') }}</span>
                                <span class="text-lg font-bold text-slate-900 leading-tight">{{ $event->start_at->format('d') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="font-bold text-slate-800 text-[14.5px]">{{ $event->title }}</h4>
                                    <span class="px-2.5 py-1 {{ $eventTypeStyles[$event->type] ?? 'bg-slate-100 text-slate-600' }} text-[11px] font-bold rounded-full ml-2 shrink-0">{{ $eventTypeLabels[$event->type] ?? $event->type }}</span>
                                </div>
                                <div class="flex items-center gap-4 mt-1.5 text-[12px] font-medium text-slate-500">
                                    <span class="flex items-center gap-1.5"><i class="ph ph-clock"></i> {{ $event->start_at->format('H:i') }}@if($event->end_at) - {{ $event->end_at->format('H:i') }}@endif</span>
                                    <span class="flex items-center gap-1.5"><i class="ph ph-map-pin"></i> {{ $event->location_type === 'internal' ? ($event->room->name ?? '-') : ($event->external_address ?? '-') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-[13px] text-slate-400 text-center py-6">Aucun événement à venir.</p>
                    @endforelse
                </div>
            </div>

            <!-- AI Insights Assistant -->
            <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border border-slate-100 shadow-sm relative overflow-hidden">
                <!-- Floating purple icon bottom right -->
                <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-purple-600 rounded-full flex items-center justify-center shadow-lg shadow-purple-600/30 opacity-90">
                    <i class="ph-fill ph-robot text-white text-3xl mb-4 mr-4"></i>
                </div>
                
                <div class="flex items-start gap-4 mb-6">
                    <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center text-white shrink-0 shadow-md shadow-purple-600/30">
                        <i class="ph-fill ph-sparkle text-2xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="text-xl font-bold text-slate-900">Assistant d'Aperçus IA</h2>
                            <span class="px-2 py-0.5 bg-purple-600 text-white text-[9px] font-bold uppercase tracking-wider rounded-md">En Direct</span>
                        </div>
                        <p class="text-slate-600 text-[14px]">Analyse des données opérationnelles en temps réel pour Nairobi West International School.</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 relative z-10">
                    <!-- AI Insight 1 -->
                    <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-sm">
                        <h4 class="text-[10px] font-bold text-red-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i class="ph ph-trend-down text-sm"></i> Alerte Présence
                        </h4>
                        <p class="text-[13px] text-slate-700 leading-relaxed">La présence est en baisse de <span class="font-bold text-red-600">5%</span> en 10ème année par rapport au mois dernier. Envisagez de revoir le nouvel emploi du temps du matin.</p>
                    </div>
                    
                    <!-- AI Insight 2 -->
                    <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-sm">
                        <h4 class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i class="ph ph-lightning text-sm"></i> Optimisation des Ressources
                        </h4>
                        <p class="text-[13px] text-slate-700 leading-relaxed">Le Labo de Science 2 est sous-utilisé les mardis. L'IA suggère d'y déplacer les sessions de physique de 8ème année.</p>
                    </div>
                    
                    <!-- AI Insight 3 -->
                    <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-sm">
                        <h4 class="text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i class="ph ph-money text-sm"></i> Prédiction des Frais
                        </h4>
                        <p class="text-[13px] text-slate-700 leading-relaxed">Prévision d'un taux de recouvrement de 95% d'ici la fin du mois selon les modèles de paiement actuels. Forte liquidité attendue.</p>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Right Column (Finances) -->
        <div class="space-y-6">
            
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
                <div class="flex items-center gap-2 mb-6">
                    <i class="ph ph-money text-slate-500 text-xl"></i>
                    <h2 class="text-lg font-bold text-slate-900">Aperçu des Finances</h2>
                </div>
                
                <!-- Collected Fees -->
                <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-5 mb-4">
                    <div class="flex items-start justify-between mb-1">
                        <h3 class="text-[10.5px] font-bold text-emerald-700 uppercase tracking-wider">Frais Collectés ({{ ucfirst(now()->translatedFormat('M')) }}.)</h3>
                        <i class="ph ph-trend-up text-emerald-600 text-lg"></i>
                    </div>
                    <p class="text-2xl font-bold text-emerald-900 mb-4">{{ number_format($financeStats['totalCollected'], 0, ',', ' ') }} FCFA</p>

                    <div class="w-full bg-emerald-200/50 rounded-full h-1.5 mb-2">
                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ min($financeStats['collectionRate'], 100) }}%"></div>
                    </div>
                    <p class="text-[11px] font-medium text-emerald-700">{{ $financeStats['collectionRate'] }}% de l'objectif atteint</p>
                </div>

                <!-- Pending Payments -->
                <div class="bg-red-50/50 border border-red-100 rounded-xl p-5 mb-6">
                    <div class="flex items-start justify-between mb-1">
                        <h3 class="text-[10.5px] font-bold text-red-700 uppercase tracking-wider">Paiements en Attente</h3>
                        <i class="ph ph-warning text-red-500 text-lg"></i>
                    </div>
                    <p class="text-2xl font-bold text-red-700 mb-1">{{ number_format($overdueStats['amount'], 0, ',', ' ') }} FCFA</p>
                    <p class="text-[11.5px] font-medium text-red-600/80">{{ $overdueStats['count'] }} compte(s) en retard (&gt;30 jours)</p>
                </div>

                <!-- Action Button -->
                <a href="{{ route('school.finance.fees.overview') }}" class="w-full py-3 px-4 bg-white border border-slate-200 rounded-xl text-[13px] font-bold text-slate-700 hover:bg-slate-50 transition flex items-center justify-between group shadow-sm">
                    Voir le Détail des Finances
                    <i class="ph ph-caret-right text-slate-400 group-hover:text-slate-600 transition"></i>
                </a>
            </div>
            
        </div>
        
    </div>

</div>
@endsection
