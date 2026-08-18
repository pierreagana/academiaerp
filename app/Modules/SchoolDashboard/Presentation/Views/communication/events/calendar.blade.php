@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6" x-data="{ selectedId: null }">
    @include('SchoolDashboard::communication.events._tabs')

    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
        <div>
            <h2 class="text-[26px] font-bold text-[#031C5B] tracking-tight">Calendrier Interactif</h2>
            <p class="text-slate-600 text-[14px] font-medium mt-1 max-w-xl">Visualisez, filtrez et planifiez les activités scolaires.</p>
        </div>
        <a href="{{ route('school.communication.events.create') }}" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm shrink-0">
            <i class="ph-bold ph-plus text-lg"></i>
            Nouvel Événement
        </a>
    </div>

    <!-- Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('school.communication.events.calendar', array_filter(array_merge($filters, ['month' => $prevMonth]))) }}" class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
                <i class="ph-bold ph-caret-left"></i>
            </a>
            <span class="text-[16px] font-extrabold text-slate-800 w-40 text-center capitalize">{{ $current->translatedFormat('F Y') }}</span>
            <a href="{{ route('school.communication.events.calendar', array_filter(array_merge($filters, ['month' => $nextMonth]))) }}" class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
                <i class="ph-bold ph-caret-right"></i>
            </a>
        </div>

        <form method="GET" action="{{ route('school.communication.events.calendar') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="month" value="{{ $current->format('Y-m') }}">
            <select name="academic_class_id" onchange="this.form.submit()" class="bg-[#F8FAFC] border border-slate-200 text-slate-700 text-[13px] font-semibold rounded-lg px-3 py-2 outline-none">
                <option value="">Toutes les classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ (string) ($filters['academic_class_id'] ?? '') === (string) $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
            <select name="type" onchange="this.form.submit()" class="bg-[#F8FAFC] border border-slate-200 text-slate-700 text-[13px] font-semibold rounded-lg px-3 py-2 outline-none">
                <option value="">Type d'événement</option>
                <option value="scolaire" {{ ($filters['type'] ?? '') === 'scolaire' ? 'selected' : '' }}>Scolaire</option>
                <option value="sportif" {{ ($filters['type'] ?? '') === 'sportif' ? 'selected' : '' }}>Sportif</option>
                <option value="culturel" {{ ($filters['type'] ?? '') === 'culturel' ? 'selected' : '' }}>Culturel</option>
                <option value="administratif" {{ ($filters['type'] ?? '') === 'administratif' ? 'selected' : '' }}>Administratif</option>
            </select>
            <input type="text" name="organizer_name" value="{{ $filters['organizer_name'] ?? '' }}" placeholder="Organisateur" class="bg-[#F8FAFC] border border-slate-200 text-slate-700 text-[13px] font-semibold rounded-lg px-3 py-2 outline-none w-36">
            <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-[13px] font-bold transition">Filtrer</button>
            @if(array_filter($filters))
                <a href="{{ route('school.communication.events.calendar', ['month' => $current->format('Y-m')]) }}" class="px-3 py-2 text-slate-400 hover:text-slate-600 text-[13px] font-bold transition">Réinitialiser</a>
            @endif
        </form>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="flex flex-col xl:flex-row gap-6">
        <!-- Calendar Grid -->
        <div class="flex-1 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="grid grid-cols-7 border-b border-slate-100 bg-[#F8FAFC]">
                @foreach(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $dayLabel)
                    <div class="px-2 py-3 text-center text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">{{ $dayLabel }}</div>
                @endforeach
            </div>

            @php
                $typeColors = ['scolaire' => 'bg-[#1E3A8A]', 'sportif' => 'bg-[#7C3AED]', 'culturel' => 'bg-[#047857]', 'administratif' => 'bg-slate-400'];
                $startOfGrid = $current->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
                $endOfGrid = $current->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
                $weeks = [];
                $day = $startOfGrid->copy();
                while ($day->lte($endOfGrid)) {
                    $week = [];
                    for ($i = 0; $i < 7; $i++) {
                        $week[] = $day->copy();
                        $day->addDay();
                    }
                    $weeks[] = $week;
                }
            @endphp

            <div class="divide-y divide-slate-100">
                @foreach($weeks as $week)
                <div class="grid grid-cols-7 divide-x divide-slate-100">
                    @foreach($week as $cellDay)
                        @php $dayEvents = $eventsByDay->get($cellDay->format('Y-m-d'), collect()); @endphp
                        <div class="min-h-[110px] p-2 {{ $cellDay->month !== $current->month ? 'bg-slate-50/50' : '' }}">
                            <span class="text-[12px] font-bold {{ $cellDay->isToday() ? 'w-6 h-6 flex items-center justify-center rounded-full bg-[#031C5B] text-white' : ($cellDay->month !== $current->month ? 'text-slate-300' : 'text-slate-600') }}">
                                {{ $cellDay->day }}
                            </span>
                            <div class="mt-1.5 space-y-1">
                                @foreach($dayEvents->take(2) as $event)
                                    <button type="button" @click="selectedId = selectedId === {{ $event->id }} ? null : {{ $event->id }}" class="w-full text-left px-1.5 py-1 rounded {{ $typeColors[$event->type] ?? 'bg-slate-400' }} text-white text-[10.5px] font-bold truncate block">
                                        {{ $event->start_at->format('H:i') }} {{ $event->title }}
                                    </button>
                                @endforeach
                                @if($dayEvents->count() > 2)
                                    <span class="text-[10px] font-bold text-slate-400 pl-1.5">+{{ $dayEvents->count() - 2 }} autre(s)</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>

        <!-- Side Panel -->
        <div class="w-full xl:w-80 shrink-0 space-y-4">
            @if($events->isNotEmpty())
            <div x-show="selectedId === null" class="bg-white rounded-2xl border border-dashed border-slate-200 p-6 text-center text-slate-400 text-[13px] font-medium">
                <i class="ph-bold ph-cursor-click text-2xl block mb-2"></i>
                Cliquez sur un événement pour voir ses détails.
            </div>
            @endif
            @forelse($events as $event)
            <div x-show="selectedId === {{ $event->id }}" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider text-white {{ $typeColors[$event->type] ?? 'bg-slate-400' }}">
                        {{ ucfirst($event->type) }}
                    </span>
                    <button type="button" @click="selectedId = null" class="text-slate-400 hover:text-slate-600">
                        <i class="ph-bold ph-x"></i>
                    </button>
                </div>
                <h3 class="text-[17px] font-extrabold text-slate-800 leading-snug">{{ $event->title }}</h3>

                <div class="mt-4 space-y-3 text-[13px]">
                    <div class="flex items-center gap-2 text-slate-600 font-semibold">
                        <i class="ph-bold ph-clock text-slate-400"></i>
                        {{ $event->start_at->translatedFormat('l d F Y') }} · {{ $event->start_at->format('H:i') }}@if($event->end_at) - {{ $event->end_at->format('H:i') }}@endif
                    </div>
                    <div class="flex items-center gap-2 text-slate-600 font-semibold">
                        <i class="ph-bold ph-map-pin text-slate-400"></i>
                        {{ $event->location_type === 'internal' ? ($event->room->name ?? '-') : ($event->external_address ?? '-') }}
                    </div>
                    @if($event->organizer_name)
                    <div class="flex items-center gap-2 text-slate-600 font-semibold">
                        <i class="ph-bold ph-user text-slate-400"></i>
                        {{ $event->organizer_name }}
                    </div>
                    @endif
                </div>

                <div class="mt-5 flex items-center gap-2">
                    <a href="{{ route('school.communication.events.edit', $event->id) }}" class="flex-1 text-center px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[13px] font-bold transition">Modifier</a>
                    <a href="{{ route('school.communication.events.show', $event->id) }}" class="flex-1 text-center px-3 py-2 bg-[#031C5B] hover:bg-[#031C5B]/90 text-white rounded-lg text-[13px] font-bold transition">Voir détails</a>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 text-center text-slate-400 text-[13px] font-medium">
                Aucun événement ce mois-ci pour ces filtres.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
