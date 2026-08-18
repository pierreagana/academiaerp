@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::library._tabs')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Tableau de Bord</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Bienvenue. Voici ce qui se passe dans votre bibliothèque aujourd'hui.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-700 uppercase tracking-wider shrink-0">
            <i class="ph-fill ph-sparkle"></i> IA Active
        </span>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Livres</p>
                <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center text-blue-600"><i class="ph-fill ph-books text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ number_format($stats['total_books'], 0, ',', ' ') }}</h3>
            <p class="text-[12px] text-slate-400 font-semibold mt-1">Catalogue complet</p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sorties</p>
                <div class="w-9 h-9 rounded-full bg-purple-50 flex items-center justify-center text-purple-600"><i class="ph-fill ph-arrows-left-right text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ number_format($stats['books_out'], 0, ',', ' ') }}</h3>
            <p class="text-[12px] text-slate-400 font-semibold mt-1">{{ $stats['books_out_percent'] }}% de l'inventaire total</p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border-2 {{ $stats['overdue_returns'] > 0 ? 'border-red-200' : 'border-slate-100' }} relative overflow-hidden">
            @if($stats['overdue_returns'] > 0)
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-purple-500"></div>
            @endif
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-red-500 uppercase tracking-wider flex items-center gap-1"><i class="ph-fill ph-warning"></i> Retours en Retard</p>
                <div class="w-9 h-9 rounded-full bg-red-50 flex items-center justify-center text-red-600"><i class="ph-fill ph-warning-circle text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $stats['overdue_returns'] }}</h3>
            @if($stats['overdue_returns'] > 0)
                <p class="text-[12px] text-red-500 font-bold mt-1">Alerte IA : volume élevé attendu cette semaine</p>
            @else
                <p class="text-[12px] text-slate-400 font-semibold mt-1">Aucun retard actuellement</p>
            @endif
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Membres Actifs</p>
                <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600"><i class="ph-fill ph-users text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ number_format($stats['active_members'], 0, ',', ' ') }}</h3>
            <p class="text-[12px] text-slate-400 font-semibold mt-1">Ont emprunté un livre</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Borrowing Trends -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-900">Tendances d'Emprunt</h3>
                <span class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-[12px] font-bold">6 derniers mois</span>
            </div>

            @php
                $counts = collect($trend)->pluck('count');
                $maxCount = max(1, $counts->max());
                $chartWidth = 600;
                $chartHeight = 200;
                $stepX = count($trend) > 1 ? $chartWidth / (count($trend) - 1) : 0;
                $points = collect($trend)->values()->map(function ($item, $i) use ($stepX, $chartHeight, $maxCount) {
                    $x = round($i * $stepX, 1);
                    $y = round($chartHeight - (($item['count'] / $maxCount) * ($chartHeight - 20)) - 5, 1);
                    return "{$x},{$y}";
                })->implode(' ');
            @endphp
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2.5 h-2.5 rounded-full bg-[#1E3A8A]"></span>
                <span class="text-[12.5px] font-semibold text-slate-600">Emprunts (livres physiques)</span>
            </div>
            <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="w-full h-52" preserveAspectRatio="none">
                <polyline points="{{ $points }}" fill="none" stroke="#1E3A8A" stroke-width="2.5" vector-effect="non-scaling-stroke" />
                @foreach($trend as $i => $item)
                    @php
                        $x = round($i * $stepX, 1);
                        $y = round($chartHeight - (($item['count'] / $maxCount) * ($chartHeight - 20)) - 5, 1);
                    @endphp
                    <circle cx="{{ $x }}" cy="{{ $y }}" r="4" fill="#1E3A8A" />
                @endforeach
            </svg>
            <div class="flex justify-between mt-2 px-1">
                @foreach($trend as $item)
                    <span class="text-[11px] font-semibold text-slate-400">{{ $item['label'] }}</span>
                @endforeach
            </div>
        </div>

        <!-- AI Overdue Alerts -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-[15px] font-bold text-slate-900 flex items-center gap-2"><i class="ph-fill ph-robot text-purple-500"></i> Alertes IA Retards</h3>
                <a href="{{ route('school.library.circulation') }}" class="text-[#031C5B] font-bold text-[12.5px] hover:underline">Tout voir</a>
            </div>
            <div class="divide-y divide-slate-50 flex-1">
                @forelse($overdueAlerts as $loan)
                    @php
                        $borrowerName = $loan->borrower ? ($loan->borrower->first_name . ' ' . $loan->borrower->last_name) : 'Emprunteur inconnu';
                        $daysOverdue = (int) ceil($loan->due_at->diffInDays(today(), true));
                    @endphp
                    <div class="p-4 flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-red-50 text-red-600 flex items-center justify-center font-bold text-[11px] shrink-0">
                            {{ substr($borrowerName, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-bold text-slate-800 truncate">{{ $borrowerName }}</p>
                            <p class="text-[12px] text-slate-500 truncate">{{ $loan->book->title ?? 'Livre supprimé' }}</p>
                            <p class="text-[11px] text-red-500 font-bold mt-0.5">{{ $daysOverdue }} jour(s) de retard{{ $loan->reminded_at ? ' · Rappel enregistré' : '' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 text-[13px] text-center py-10">Aucun retard actuellement.</p>
                @endforelse
            </div>
            @if($overdueAlerts->isNotEmpty())
            <div class="p-4 border-t border-slate-100">
                <form action="{{ route('school.library.overdue.remind') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-center px-4 py-2.5 border border-slate-200 rounded-xl text-[13px] font-bold text-[#031C5B] hover:bg-slate-50 transition">
                        Enregistrer les rappels
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- New Arrivals -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Nouvelles Acquisitions</h3>
            <a href="{{ route('school.library.catalog') }}" class="text-[#031C5B] font-bold text-[13px] hover:underline">Voir le catalogue</a>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @forelse($newArrivals as $book)
                <div class="flex items-center gap-3">
                    <div class="w-12 h-16 rounded-lg bg-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                        @if($book->cover_path)
                            <img src="{{ asset('storage/' . $book->cover_path) }}" class="w-full h-full object-cover">
                        @else
                            <i class="ph-fill ph-book text-slate-400 text-xl"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-[13px] font-bold text-slate-800 truncate">{{ $book->title }}</p>
                        <p class="text-[11.5px] text-slate-500 truncate">{{ $book->author ?? '-' }}</p>
                        @if($book->category)
                            <span class="inline-block mt-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[10px] font-bold">{{ $book->category->name }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-slate-400 text-[13px] col-span-full text-center py-6">Aucun livre enregistré pour le moment.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
