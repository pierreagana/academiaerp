@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Ma Présence</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Votre historique de pointages personnel — n'inclut pas l'appel de vos élèves.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('school.teacher.attendance-history', ['month' => $month->copy()->subMonth()->toDateString()]) }}" class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:text-[#031C5B] hover:border-slate-300 transition">
                <i class="ph-bold ph-caret-left"></i>
            </a>
            <span class="text-[14px] font-bold text-[#031C5B] w-36 text-center">{{ $month->translatedFormat('F Y') }}</span>
            <a href="{{ route('school.teacher.attendance-history', ['month' => $month->copy()->addMonth()->toDateString()]) }}" class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:text-[#031C5B] hover:border-slate-300 transition">
                <i class="ph-bold ph-caret-right"></i>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-3"><i class="ph-bold ph-timer text-[16px]"></i></div>
            <p class="text-[12px] font-bold text-slate-500 mb-1">Taux de ponctualité</p>
            <p class="text-[26px] font-extrabold text-[#031C5B] leading-none">{{ $stats['punctuality_rate'] !== null ? $stats['punctuality_rate'] . '%' : '—' }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3"><i class="ph-bold ph-briefcase text-[16px]"></i></div>
            <p class="text-[12px] font-bold text-slate-500 mb-1">Jours travaillés</p>
            <p class="text-[26px] font-extrabold text-[#031C5B] leading-none">{{ $stats['days_worked'] }} <span class="text-[14px] text-slate-400 font-bold">/ {{ $stats['days_scheduled'] }}</span></p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center mb-3"><i class="ph-bold ph-clock-counter-clockwise text-[16px]"></i></div>
            <p class="text-[12px] font-bold text-slate-500 mb-1">Retards</p>
            <p class="text-[26px] font-extrabold text-[#031C5B] leading-none">{{ $stats['late_count'] }} <span class="text-[14px] text-slate-400 font-bold">incident(s)</span></p>
            <p class="text-[11px] text-slate-400 mt-1">Total : {{ $stats['late_minutes_total'] }} minutes</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="w-9 h-9 rounded-lg bg-red-50 text-red-600 flex items-center justify-center mb-3"><i class="ph-bold ph-calendar-x text-[16px]"></i></div>
            <p class="text-[12px] font-bold text-slate-500 mb-1">Absences</p>
            <p class="text-[26px] font-extrabold text-[#031C5B] leading-none">{{ $stats['absent_count'] }} <span class="text-[14px] text-slate-400 font-bold">jour(s)</span></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-[15px] font-extrabold text-slate-900 mb-4">{{ $month->translatedFormat('F Y') }}</h3>

            @php
                $statusColor = ['present' => 'bg-emerald-100 text-emerald-700', 'late' => 'bg-amber-100 text-amber-700', 'absent' => 'bg-red-100 text-red-700'];
                $leadingBlanks = $month->copy()->startOfMonth()->dayOfWeekIso - 1;
                $daysInMonth = $month->daysInMonth;
            @endphp

            <div class="grid grid-cols-7 gap-1.5 text-center mb-2">
                @foreach(['Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa', 'Di'] as $d)
                    <span class="text-[10px] font-bold text-slate-400">{{ $d }}</span>
                @endforeach
            </div>
            <div class="grid grid-cols-7 gap-1.5">
                @for($i = 0; $i < $leadingBlanks; $i++)
                    <div></div>
                @endfor
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php $status = $calendar[$day] ?? null; @endphp
                    <div class="aspect-square rounded-lg flex items-center justify-center text-[12px] font-bold {{ $status ? $statusColor[$status] : 'text-slate-300' }}">
                        {{ $day }}
                    </div>
                @endfor
            </div>

            <div class="flex items-center gap-4 mt-4 pt-4 border-t border-slate-100">
                <span class="flex items-center gap-1.5 text-[11px] text-slate-500"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Présent</span>
                <span class="flex items-center gap-1.5 text-[11px] text-slate-500"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>Retard</span>
                <span class="flex items-center gap-1.5 text-[11px] text-slate-500"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>Absent</span>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[15px] font-extrabold text-slate-900">Historique des pointages</h3>
                <a href="{{ route('school.teacher.attendance-history.export', ['month' => $month->toDateString()]) }}" class="text-[12px] font-bold text-[#031C5B] hover:underline flex items-center gap-1.5">
                    <i class="ph-bold ph-download-simple"></i> Exporter
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[13px]">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10.5px] font-extrabold text-slate-400 uppercase tracking-wider">
                            <th class="py-2">Date</th>
                            <th class="py-2">Arrivée</th>
                            <th class="py-2">Départ</th>
                            <th class="py-2">Durée</th>
                            <th class="py-2 text-right">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @php $applicableRows = $daily->reject(fn($row) => $row['status'] === 'not_applicable'); @endphp
                        @forelse($applicableRows as $date => $row)
                            <tr>
                                <td class="py-2.5 font-bold text-slate-700">{{ \Carbon\Carbon::parse($date)->translatedFormat('D j M') }}</td>
                                <td class="py-2.5 text-slate-500">{{ $row['arrival']?->format('H:i') ?? '-' }}</td>
                                <td class="py-2.5 text-slate-500">{{ $row['departure']?->format('H:i') ?? '-' }}</td>
                                <td class="py-2.5 text-slate-500">{{ $row['duration_minutes'] ? intdiv($row['duration_minutes'], 60) . 'h' . str_pad($row['duration_minutes'] % 60, 2, '0', STR_PAD_LEFT) : '-' }}</td>
                                <td class="py-2.5 text-right">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $statusColor[$row['status']] }}">
                                        @if($row['status'] === 'late') Retard ({{ $row['late_minutes'] }}m) @elseif($row['status'] === 'present') Présent @else Absent @endif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-slate-400">Aucun jour prévu ce mois-ci.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
