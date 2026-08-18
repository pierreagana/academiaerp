@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-[1400px] w-full mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-[26px] font-bold text-[#0F172A] tracking-tight">Présence Classe</h1>
            <p class="text-[14px] text-slate-500 mt-1">Aperçu de l'assiduité des classes, basé sur les présences réellement enregistrées.</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('school.academic.presence.attendance') }}" method="GET" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                    class="bg-white border border-slate-200 text-slate-700 text-[13px] font-semibold rounded-xl px-3 py-2.5 outline-none focus:border-[#2F5F76]">
            </form>
            <a href="{{ route('school.academic.presence.attendance.take') }}?date={{ $date }}" class="bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-5 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="ph-bold ph-clipboard-text"></i>
                Prendre la présence
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mb-3">
                <i class="ph-fill ph-user-check text-[20px]"></i>
            </div>
            <p class="text-[13px] text-slate-500 font-semibold mb-1">Taux de présence</p>
            <h3 class="text-[30px] font-extrabold text-[#0F172A]">{{ $dashboard['rate'] !== null ? $dashboard['rate'] . '%' : '—' }}</h3>
            <p class="text-[12px] text-slate-400 mt-1">{{ $dashboard['recorded'] }} élève(s) enregistré(s) ce jour</p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 mb-3">
                <i class="ph-fill ph-user-minus text-[20px]"></i>
            </div>
            <p class="text-[13px] text-slate-500 font-semibold mb-1">Élèves Absents</p>
            <h3 class="text-[30px] font-extrabold text-[#0F172A]">{{ $dashboard['absent'] }}</h3>
            <p class="text-[12px] text-slate-400 mt-1">{{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 mb-3">
                <i class="ph-fill ph-clock text-[20px]"></i>
            </div>
            <p class="text-[13px] text-slate-500 font-semibold mb-1">Retards Signalés</p>
            <h3 class="text-[30px] font-extrabold text-[#0F172A]">{{ $dashboard['late'] }}</h3>
            <p class="text-[12px] text-slate-400 mt-1">{{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-amber-200">
            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 mb-3">
                <i class="ph-fill ph-warning text-[20px]"></i>
            </div>
            <p class="text-[13px] text-slate-500 font-semibold mb-1">Absences Répétées</p>
            <h3 class="text-[30px] font-extrabold text-[#0F172A]">{{ $repeatedAbsences->count() }}</h3>
            <p class="text-[12px] text-slate-400 mt-1">≥ 3 absences sur 7 jours</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Weekly trend -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <h3 class="text-[15px] font-bold text-slate-900 mb-6">Tendance Hebdomadaire</h3>
            <div class="flex items-end justify-between gap-3 h-48">
                @forelse($trend as $day)
                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                        <span class="text-[11px] font-bold text-slate-500">{{ $day['rate'] !== null ? $day['rate'] . '%' : '—' }}</span>
                        <div class="w-full bg-slate-100 rounded-lg relative" style="height: 100%;">
                            <div class="absolute bottom-0 left-0 right-0 bg-[#2F5F76] rounded-lg transition-all" style="height: {{ $day['rate'] ?? 0 }}%; min-height: {{ $day['rate'] !== null ? '4px' : '0' }};"></div>
                        </div>
                        <span class="text-[12px] font-semibold text-slate-500">{{ ucfirst($day['label']) }}</span>
                    </div>
                @empty
                    <p class="text-slate-400 text-[13px] text-center w-full">Aucune donnée disponible.</p>
                @endforelse
            </div>
        </div>

        <!-- Repeated absences panel -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-amber-200">
            <h3 class="text-[15px] font-bold text-slate-900 flex items-center gap-2 mb-4">
                <i class="ph-fill ph-warning text-amber-500"></i>
                Absences Répétées
            </h3>
            <div class="space-y-3">
                @forelse($repeatedAbsences->take(5) as $entry)
                    <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
                        <div class="w-9 h-9 rounded-full bg-[#031C5B] text-white flex items-center justify-center font-bold text-[12px] flex-shrink-0">
                            {{ substr($entry['student']->first_name ?? '?', 0, 1) }}{{ substr($entry['student']->last_name ?? '', 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-[13px] font-bold text-slate-800 truncate">{{ $entry['student']->first_name ?? '?' }} {{ $entry['student']->last_name ?? '' }}</p>
                            <p class="text-[11.5px] text-slate-500 truncate">{{ $entry['student']->academicClass->name ?? '-' }} • {{ $entry['count'] }} absences (7j)</p>
                        </div>
                    </div>
                @empty
                    <p class="text-[13px] text-slate-400 text-center py-6">Aucun élève avec des absences répétées.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Class overview -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-[15px] font-bold text-slate-800">Aperçu par Classe</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Classe</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Professeur Principal</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Taux de Présence</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($classOverview as $row)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-[14px] font-bold text-slate-800">{{ $row['class']->name }}</td>
                        <td class="px-5 py-4 text-[13px] text-slate-600">{{ $row['class']->headTeacher ? $row['class']->headTeacher->first_name . ' ' . $row['class']->headTeacher->last_name : '-' }}</td>
                        <td class="px-5 py-4 text-[13px] font-bold text-slate-700">{{ $row['rate'] !== null ? $row['rate'] . '%' : '—' }}</td>
                        <td class="px-5 py-4">
                            @if($row['recorded'] === 0)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-orange-50 text-orange-600 border border-orange-100">Non renseigné</span>
                            @elseif($row['recorded'] < $row['totalStudents'])
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100">Partiel ({{ $row['recorded'] }}/{{ $row['totalStudents'] }})</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-50 text-green-600 border border-green-100">À jour</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('school.academic.presence.attendance.take') }}?class_id={{ $row['class']->id }}&date={{ $date }}" class="text-[#031C5B] font-bold text-[12.5px] hover:underline">
                                {{ $row['recorded'] > 0 ? 'Modifier' : 'Prendre la présence' }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-500 text-[13px]">Aucune classe configurée.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
