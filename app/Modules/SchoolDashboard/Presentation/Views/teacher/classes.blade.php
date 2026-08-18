@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Mes Classes</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Vue d'ensemble de vos classes et de vos élèves.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center gap-2 mb-4">
            <i class="ph-bold ph-clock-user text-[18px] text-[#031C5B]"></i>
            <h3 class="text-[15px] font-extrabold text-slate-900">Mes Cours Aujourd'hui</h3>
        </div>

        @if($todaysCourses->isEmpty())
            <p class="text-[13px] text-slate-500">Aucun cours programmé aujourd'hui.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($todaysCourses as $item)
                    @php $checkin = $item['checkin']; $slot = $item['slot']; @endphp
                    <div class="flex items-center justify-between gap-3 bg-slate-50 rounded-xl px-4 py-3">
                        <div class="min-w-0">
                            <p class="text-[13px] font-extrabold text-[#031C5B]">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} &middot; {{ $slot->subject->name ?? '—' }}</p>
                            <p class="text-[11.5px] text-slate-500 truncate">{{ $slot->academicClass->name ?? '—' }}@if($slot->room) &middot; Salle {{ $slot->room->name }} @endif</p>
                        </div>
                        @if($checkin)
                            @if($checkin->late_minutes > 0)
                                <span class="shrink-0 text-[11px] font-bold px-2.5 py-1.5 rounded-full bg-amber-50 text-amber-700 whitespace-nowrap">Retard {{ $checkin->late_minutes }} min</span>
                            @else
                                <span class="shrink-0 text-[11px] font-bold px-2.5 py-1.5 rounded-full bg-emerald-50 text-emerald-700 whitespace-nowrap">Pointé</span>
                            @endif
                        @else
                            <form action="{{ route('school.teacher.checkin') }}" method="POST" class="shrink-0">
                                @csrf
                                <input type="hidden" name="timetable_id" value="{{ $slot->id }}">
                                <button type="submit" class="text-[11.5px] font-bold px-3 py-1.5 rounded-full bg-[#031C5B] hover:bg-[#031C5B]/90 text-white transition whitespace-nowrap">Pointer</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        @forelse($classCards as $card)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-start justify-between mb-1">
                    <h3 class="text-[19px] font-extrabold text-[#031C5B]">{{ $card['class']->name }}</h3>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0"><i class="ph-bold ph-flask text-[16px]"></i></span>
                </div>
                <p class="text-[12.5px] text-slate-500 mb-4">
                    {{ $card['subjectLabel'] ?? '—' }}
                    @if($card['room']) &middot; Salle {{ $card['room']->name }} @endif
                </p>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-slate-50 rounded-xl p-3">
                        <p class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider mb-1">Élèves</p>
                        <p class="text-[20px] font-extrabold text-[#031C5B]">{{ $card['studentCount'] }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3">
                        <p class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider mb-1">Moyenne</p>
                        <p class="text-[20px] font-extrabold text-[#031C5B]">{{ $card['average'] !== null ? $card['average'] . '/20' : '—' }}</p>
                    </div>
                </div>

                <div class="mb-5">
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider">Présence</p>
                        <p class="text-[12.5px] font-extrabold text-[#031C5B]">{{ $card['attendanceRate'] !== null ? $card['attendanceRate'] . '%' : '—' }}</p>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full" style="width: {{ $card['attendanceRate'] ?? 0 }}%"></div>
                    </div>
                </div>

                <div class="flex gap-2">
                    @if($card['teachesToday'] && !$card['checkedInToday'])
                        <span title="Pointez votre présence pour ce cours avant de faire l'appel." class="flex-1 text-center bg-slate-100 text-slate-400 font-bold text-[12.5px] px-4 py-2.5 rounded-lg flex items-center justify-center gap-1.5 cursor-not-allowed">
                            <i class="ph-bold ph-lock-simple"></i> Appel
                        </span>
                    @else
                        <a href="{{ route('school.academic.presence.attendance.take', ['class_id' => $card['class']->id]) }}" class="flex-1 text-center bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[12.5px] px-4 py-2.5 rounded-lg transition flex items-center justify-center gap-1.5">
                            <i class="ph-bold ph-user-check"></i> Appel
                        </a>
                    @endif
                    <a href="{{ route('school.teacher.classes.planning', $card['class']->id) }}" class="flex-1 text-center bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[12.5px] px-4 py-2.5 rounded-lg transition flex items-center justify-center gap-1.5">
                        <i class="ph-bold ph-calendar"></i> Planning
                    </a>
                </div>
                @if($card['teachesToday'] && !$card['checkedInToday'])
                    <p class="text-[11px] text-amber-600 font-semibold mt-2">Pointez votre présence ci-dessus avant de faire l'appel.</p>
                @endif
            </div>
        @empty
            <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm p-8">
                @include('SchoolDashboard::components.empty-state', [
                    'title' => 'Aucune classe assignée',
                    'description' => 'Vous n\'êtes actuellement affecté à aucune classe.',
                    'icon' => 'ph-fill ph-chalkboard-teacher'
                ])
            </div>
        @endforelse

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <i class="ph-bold ph-chart-line-up text-[18px] text-[#031C5B]"></i>
                <h3 class="text-[15px] font-extrabold text-slate-900">Aperçu Global</h3>
            </div>

            <p class="text-[13px] text-slate-500 mb-5 leading-relaxed">
                Vous encadrez {{ $classCards->count() }} classe(s) pour un total de {{ $totalStudents }} élève(s).
                @if($gradesToEnter > 0)
                    {{ $gradesToEnter }} note(s) restent à saisir.
                @else
                    Toutes vos notes sont à jour.
                @endif
            </p>

            <div class="space-y-3 text-[13px]">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-slate-500">Total Élèves</span>
                    <span class="font-extrabold text-[#031C5B]">{{ $totalStudents }}</span>
                </div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-slate-500">Prochain Cours</span>
                    <span class="font-extrabold text-[#031C5B]">
                        @if($nextCourse)
                            {{ \Carbon\Carbon::parse($nextCourse['start_time'])->format('H:i') }} &middot; {{ $nextCourse['academicClass']->name ?? '—' }}
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Notes à Saisir</span>
                    @if($gradesToEnter > 0)
                        <a href="{{ route('school.academic.bulletins.grades') }}" class="font-extrabold text-amber-600 hover:underline">{{ $gradesToEnter }}</a>
                    @else
                        <span class="font-extrabold text-[#031C5B]">0</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
