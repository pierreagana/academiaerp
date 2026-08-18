@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Bonjour, {{ $teacher->first_name }} {{ $teacher->last_name }}</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">
                @if($gradesToEnter > 0)
                    Vous avez {{ $gradesToEnter }} note(s) à saisir.
                @else
                    Toutes les notes de vos classes sont à jour.
                @endif
            </p>
        </div>
        <div class="text-right">
            <p class="text-[15px] font-bold text-slate-700">{{ now()->translatedFormat('l j F Y') }}</p>
            <p class="text-[12.5px] text-slate-400">{{ $currentSemester?->name ?? 'Aucun semestre actif' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <span class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="ph-bold ph-chart-bar text-lg"></i></span>
                @if($classAverageTrend)
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-1 rounded-full {{ str_starts_with($classAverageTrend, '+') ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">{{ $classAverageTrend }}</span>
                @endif
            </div>
            <p class="text-[13px] font-bold text-slate-500 mb-1">Moyenne de mes classes</p>
            <p class="text-[30px] font-extrabold text-[#031C5B] leading-none">{{ $classAverage !== null ? $classAverage . '/20' : '—' }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <span class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="ph-bold ph-calendar-check text-lg"></i></span>
                <span class="inline-flex items-center text-[11px] font-bold px-2 py-1 rounded-full bg-slate-100 text-slate-500">30 jours</span>
            </div>
            <p class="text-[13px] font-bold text-slate-500 mb-1">Taux de présence</p>
            <p class="text-[30px] font-extrabold text-[#031C5B] leading-none">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <span class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center"><i class="ph-bold ph-pencil-simple-line text-lg"></i></span>
                @if($gradesToEnter > 0)
                    <a href="{{ route('school.academic.bulletins.grades') }}" class="text-[11px] font-bold text-[#031C5B] hover:underline">Saisir &rarr;</a>
                @endif
            </div>
            <p class="text-[13px] font-bold text-slate-500 mb-1">Notes à Saisir</p>
            <p class="text-[30px] font-extrabold {{ $gradesToEnter > 0 ? 'text-amber-600' : 'text-[#031C5B]' }} leading-none">{{ $gradesToEnter }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-1">
                <span class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center"><i class="ph-bold ph-warning text-lg"></i></span>
                <h3 class="text-[16px] font-extrabold text-slate-900">Alertes Élèves</h3>
            </div>
            <p class="text-[12.5px] text-slate-500 mb-4">Signaux réels calculés à partir des notes et des présences de vos classes.</p>

            <div class="space-y-3">
                @foreach($averageDropAlerts as $alert)
                    <div class="flex items-center justify-between p-3.5 rounded-xl border border-amber-100 bg-amber-50/50">
                        <div>
                            <p class="text-[13.5px] font-bold text-slate-800">{{ $alert['student']->first_name }} {{ $alert['student']->last_name }}</p>
                            <p class="text-[12px] text-slate-500">Baisse de {{ $alert['drop'] }} pts en {{ $alert['subject']->name }} par rapport au semestre précédent</p>
                        </div>
                        <a href="{{ route('school.report-card.student', $alert['student']->id) }}" class="text-[11.5px] font-bold text-[#031C5B] hover:underline whitespace-nowrap ml-3">Voir le profil</a>
                    </div>
                @endforeach

                @foreach($repeatedAbsenceAlerts as $alert)
                    <div class="flex items-center justify-between p-3.5 rounded-xl border border-red-100 bg-red-50/50">
                        <div>
                            <p class="text-[13.5px] font-bold text-slate-800">{{ $alert['student']->first_name }} {{ $alert['student']->last_name }}</p>
                            <p class="text-[12px] text-slate-500">{{ $alert['count'] }} absences sur les 30 derniers jours</p>
                        </div>
                        <a href="{{ route('school.report-card.student', $alert['student']->id) }}" class="text-[11.5px] font-bold text-[#031C5B] hover:underline whitespace-nowrap ml-3">Voir l'assiduité</a>
                    </div>
                @endforeach

                @if(empty($averageDropAlerts) && empty($repeatedAbsenceAlerts))
                    <p class="text-[13px] text-slate-400 py-4 text-center">Aucune alerte pour le moment.</p>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="w-8 h-8 rounded-lg bg-[#031C5B]/10 text-[#031C5B] flex items-center justify-center"><i class="ph-bold ph-lightning text-lg"></i></span>
                <h3 class="text-[15px] font-extrabold text-slate-900">Actions Rapides</h3>
            </div>
            <div class="space-y-2">
                <a href="{{ route('school.academic.bulletins.grades') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                    <div>
                        <p class="text-[13px] font-bold text-slate-800">Saisir les Notes</p>
                        <p class="text-[11.5px] text-slate-400">Bulletins de mes classes</p>
                    </div>
                    <i class="ph-bold ph-caret-right text-slate-300"></i>
                </a>
                <a href="{{ route('school.academic.presence.attendance.take') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                    <div>
                        <p class="text-[13px] font-bold text-slate-800">Prendre la Présence</p>
                        <p class="text-[11.5px] text-slate-400">Marquer présent/absent/retard</p>
                    </div>
                    <i class="ph-bold ph-caret-right text-slate-300"></i>
                </a>
                <a href="{{ route('school.academic.syllabuses') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                    <div>
                        <p class="text-[13px] font-bold text-slate-800">Programmes de Cours</p>
                        <p class="text-[11.5px] text-slate-400">Planifier une leçon</p>
                    </div>
                    <i class="ph-bold ph-caret-right text-slate-300"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-[16px] font-extrabold text-slate-900 mb-4">Mon Emploi du Temps — Aujourd'hui</h3>
            <div class="space-y-2">
                @forelse($todaySchedule as $slot)
                    <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100">
                        <span class="text-[12px] font-bold text-slate-500 w-14">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}</span>
                        <div class="flex-1">
                            <p class="text-[13px] font-bold text-slate-800">{{ $slot->subject->name ?? '—' }} — {{ $slot->academicClass->name ?? '—' }}</p>
                            @if($slot->room)
                                <p class="text-[11.5px] text-slate-400 flex items-center gap-1"><i class="ph ph-map-pin"></i> {{ $slot->room->name }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-[13px] text-slate-400 py-4 text-center">Aucun cours prévu aujourd'hui.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-[16px] font-extrabold text-slate-900 mb-4">Dernières Saisies de Notes</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[13px]">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10.5px] font-extrabold text-slate-400 uppercase tracking-wider">
                            <th class="py-2">Matière</th>
                            <th class="py-2">Classe</th>
                            <th class="py-2">Date</th>
                            <th class="py-2 text-right">Réussite</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($recentGradeEntries as $entry)
                            <tr>
                                <td class="py-2.5 font-bold text-slate-700">{{ $entry['subject'] }}</td>
                                <td class="py-2.5 text-slate-500">{{ $entry['class'] }}</td>
                                <td class="py-2.5 text-slate-400">{{ \Carbon\Carbon::parse($entry['date'])->format('d M') }}</td>
                                <td class="py-2.5 text-right font-bold {{ $entry['pass_rate'] >= 50 ? 'text-emerald-600' : 'text-red-600' }}">{{ $entry['pass_rate'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-slate-400">Aucune note saisie ce semestre.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
