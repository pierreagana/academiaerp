@extends('ParentPortal::layout')

@section('title', 'Espace Académique')

@section('content')

<!-- HEADER & CHILD SWITCHER -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Espace Académique</h1>
        <p class="text-sm font-medium text-slate-500 mt-1">Suivi détaillé des performances et compétences.</p>
    </div>

    <!-- CHILD SWITCHER PILLS (Top Right) -->
    @if($children->isNotEmpty())
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        @foreach($children as $kid)
        @php
            $isSelected = ($selectedChild && $selectedChild->id === $kid->id);
        @endphp
        <a href="{{ route('parent.academic', ['student' => $kid->id]) }}" 
           class="inline-flex items-center gap-2.5 px-4 py-2 rounded-2xl transition border font-bold text-xs shadow-xs {{ $isSelected ? 'bg-white border-slate-300 text-slate-900 ring-2 ring-blue-900/10' : 'bg-white/80 border-slate-200/80 text-slate-500 hover:text-slate-800 hover:bg-white' }}">
            <div class="w-6 h-6 rounded-full overflow-hidden shrink-0 bg-slate-800 text-white flex items-center justify-center text-[10px]">
                @if($kid->photo_path)
                    <img src="{{ asset('storage/' . $kid->photo_path) }}" alt="{{ $kid->first_name }}" class="w-full h-full object-cover">
                @else
                    {{ substr($kid->first_name, 0, 1) }}
                @endif
            </div>
            <span>{{ $kid->first_name }} {{ $kid->last_name }}</span>
        </a>
        @endforeach
    </div>
    @endif
</div>

@if(!$selectedChild)
<div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-12 text-center">
    <p class="text-sm text-slate-500">Aucun élève rattaché à votre compte.</p>
</div>
@else

<!-- TOP 4 KPI CARDS -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    
    <!-- CARD 1: MOYENNE GÉNÉRALE -->
    <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Moyenne Générale</span>
            <div class="w-7 h-7 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-[16px]">show_chart</span>
            </div>
        </div>
        <div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">
                {{ $kpis['average'] !== null ? number_format($kpis['average'], 1) : '—' }}<span class="text-sm font-bold text-slate-400">/20</span>
            </div>
            <div class="mt-2.5 flex items-center gap-1 text-[11px] font-bold">
                @if($kpis['averageTrend'])
                <span @class(['inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full', 'text-emerald-700 bg-emerald-50' => !str_starts_with($kpis['averageTrend'], '-'), 'text-rose-700 bg-rose-50' => str_starts_with($kpis['averageTrend'], '-')])>
                    <span class="material-symbols-outlined text-[13px]">{{ str_starts_with($kpis['averageTrend'], '-') ? 'arrow_downward' : 'arrow_upward' }}</span>
                    <span>{{ $kpis['averageTrend'] }}</span>
                </span>
                <span class="text-slate-400 font-medium">vs trimestre précédent</span>
                @else
                <span class="text-slate-400 font-medium">Pas de comparaison disponible</span>
                @endif
            </div>
        </div>
    </div>

    <!-- CARD 2: RANG DANS LA CLASSE -->
    <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Rang dans la classe</span>
            <div class="w-7 h-7 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-[16px]">emoji_events</span>
            </div>
        </div>
        <div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">
                @if($kpis['rank'])
                    {{ $kpis['rank'] }}<span class="text-sm font-extrabold text-slate-700">ème</span>
                @else
                    —
                @endif
            </div>
            <p class="text-xs font-semibold text-slate-400 mt-2.5">{{ $kpis['classSize'] ? "Sur {$kpis['classSize']} élèves" : 'Bulletin non publié' }}</p>
        </div>
    </div>

    <!-- CARD 3: ASSIDUITÉ -->
    <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Assiduité</span>
            <div class="w-7 h-7 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-[16px]">calendar_today</span>
            </div>
        </div>
        <div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">
                {{ $kpis['attendanceRate'] !== null ? $kpis['attendanceRate'] . '%' : '—' }}
            </div>
            <p class="text-xs font-semibold text-slate-400 mt-2.5">{{ $kpis['justifiedAbsences'] }} Absences justifiées</p>
        </div>
    </div>

    <!-- CARD 4: IA INSIGHT -->
    <div class="bg-blue-50/70 rounded-3xl p-5 border border-blue-100/80 shadow-xs flex flex-col justify-between">
        <div class="flex items-center gap-1.5 mb-2">
            <span class="material-symbols-outlined text-[16px] text-blue-700">auto_awesome</span>
            <span class="text-[11px] font-black uppercase tracking-wider text-blue-900">IA Insight</span>
        </div>
        <p class="text-[11.5px] font-medium text-slate-700 leading-relaxed">
            {!! preg_replace('/\*\*(.*?)\*\*/', '<strong class="text-slate-900 font-bold">$1</strong>', $aiAcademicInsight) !!}
        </p>
    </div>

</div>

<!-- MIDDLE ROW: PROGRESSION PAR MATIÈRE + BULLETINS SCOLAIRES -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
    
    <!-- PROGRESSION PAR MATIÈRE CHART (Col 8) -->
    <div class="lg:col-span-8 bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-blue-600">trending_up</span>
                <h2 class="text-sm font-extrabold text-slate-900">Progression par Matière</h2>
            </div>
            @if($currentSemesterName)
            <span class="text-xs font-bold bg-blue-50 text-[#061536] px-3 py-1 rounded-xl">{{ $currentSemesterName }}</span>
            @endif
        </div>

        <!-- BAR CHART GRAPH -->
        <div class="relative pt-4 pb-2 px-2">
            <!-- Y-Axis Grid Lines -->
            <div class="space-y-6 text-[10px] font-bold text-slate-300">
                <div class="flex items-center gap-3"><span class="w-4 text-right">20</span><div class="flex-1 border-b border-dashed border-slate-100"></div></div>
                <div class="flex items-center gap-3"><span class="w-4 text-right">15</span><div class="flex-1 border-b border-dashed border-slate-100"></div></div>
                <div class="flex items-center gap-3"><span class="w-4 text-right">10</span><div class="flex-1 border-b border-dashed border-slate-100"></div></div>
                <div class="flex items-center gap-3"><span class="w-4 text-right">5</span><div class="flex-1 border-b border-dashed border-slate-100"></div></div>
                <div class="flex items-center gap-3"><span class="w-4 text-right">0</span><div class="flex-1 border-b border-slate-200"></div></div>
            </div>

            <!-- Bars Column Overlay -->
            <div class="absolute inset-x-8 bottom-8 top-4 flex items-end justify-around px-4">
                @forelse($subjectProgress as $sp)
                @php
                    $heightPct = min(100, max(5, ($sp['score'] / 20) * 100));
                    $isAbove = $sp['aboveClassAvg'];
                    $barColor = $isAbove === null ? 'bg-slate-400' : ($isAbove ? 'bg-[#061536]' : 'bg-rose-500');
                @endphp
                <div class="flex flex-col items-center gap-2 w-12 group">
                    <!-- Tooltip Score -->
                    <span class="text-[10px] font-black text-slate-700 opacity-0 group-hover:opacity-100 transition mb-1 bg-slate-100 px-1.5 py-0.5 rounded-md">
                        {{ number_format($sp['score'], 1) }}/20
                    </span>

                    <!-- Bar -->
                    <div class="w-7 rounded-t-xl transition-all duration-300 {{ $barColor }}"
                         style="height: {{ $heightPct }}%;"></div>

                    <!-- Label -->
                    <span class="text-[11px] font-bold text-slate-600 mt-2 truncate max-w-[60px] text-center block">
                        {{ $sp['subject'] }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400 self-center">Aucune note publiée ce trimestre.</p>
                @endforelse
            </div>
        </div>

        <!-- CHART LEGEND -->
        <div class="mt-8 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-center gap-6 text-xs font-semibold text-slate-500">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#061536]"></span>
                <span>Au-dessus de la moyenne (Classe)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <span>En-dessous de la moyenne (Classe)</span>
            </div>
        </div>
    </div>

    <!-- BULLETINS SCOLAIRES (Col 4) -->
    <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-blue-600">description</span>
                    <h2 class="text-sm font-extrabold text-slate-900">Bulletins Scolaires</h2>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($bulletins as $b)
                <a href="{{ $b['url'] }}"
                   class="p-4 rounded-2xl border border-slate-100 hover:border-blue-100 hover:bg-slate-50/50 transition flex items-center justify-between gap-3 group">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[20px]">picture_as_pdf</span>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-xs font-extrabold text-slate-900 group-hover:text-blue-700 transition truncate">{{ $b['title'] }}</h3>
                            <p class="text-[11px] font-medium text-slate-400 mt-0.5">{{ $b['period'] }}</p>
                        </div>
                    </div>

                    <div class="shrink-0">
                        <span class="w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">
                            <span class="material-symbols-outlined text-[15px]">check_circle</span>
                        </span>
                    </div>
                </a>
                @empty
                <p class="text-xs text-slate-400 text-center py-4">Aucun bulletin publié pour l'instant.</p>
                @endforelse
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100">
            <a href="{{ route('parent.bulletin', $selectedChild->id) }}"
               class="w-full inline-flex items-center justify-center gap-2 bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs py-3 rounded-2xl transition shadow-md shadow-blue-950/20">
                <span class="material-symbols-outlined text-[16px]">description</span>
                <span>Consulter le bulletin</span>
            </a>
        </div>
    </div>

</div>

<!-- BOTTOM ROW: SUIVI DES COMPÉTENCES + RESSOURCES ENSEIGNANTS -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    
    <!-- SUIVI DES COMPÉTENCES (Col 7) -->
    <div class="lg:col-span-7 bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
        <div class="flex items-center gap-2 mb-5">
            <span class="material-symbols-outlined text-[20px] text-blue-600">psychology</span>
            <h2 class="text-sm font-extrabold text-slate-900">Suivi des Compétences</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">
                        <th class="py-2.5">Domaine de Compétence</th>
                        <th class="py-2.5 text-right w-44">Acquisition</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($competencies as $comp)
                    <tr>
                        <td class="py-4 font-bold text-slate-900 pr-4">
                            {{ $comp['domain'] }}
                        </td>
                        <td class="py-4 text-right">
                            <div class="w-32 ml-auto">
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden mb-1">
                                    <div class="{{ $comp['color'] }} h-full rounded-full" style="width: {{ $comp['percentage'] }}%;"></div>
                                </div>
                                <span class="text-[11px] font-bold text-slate-500">{{ $comp['level'] }}</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="py-6 text-center text-slate-400 text-xs">Aucune évaluation de compétences publiée ce trimestre.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- RESSOURCES ENSEIGNANTS (Col 5) -->
    <div class="lg:col-span-5 bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-blue-600">folder</span>
                    <h2 class="text-sm font-extrabold text-slate-900">Ressources Enseignants</h2>
                </div>
                <a href="{{ route('parent.homework', $selectedChild->id) }}" class="text-xs font-bold text-blue-600 hover:underline">Voir tout</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                @forelse($teacherResources as $res)
                <div class="p-4 rounded-2xl border border-slate-100 hover:border-blue-100 hover:bg-slate-50/40 transition flex flex-col justify-between">
                    <div>
                        <div class="w-9 h-9 rounded-2xl {{ $res['icon_bg'] }} text-white flex items-center justify-center font-bold mb-3 shadow-xs">
                            <span class="material-symbols-outlined text-[18px]">{{ $res['type'] === 'PDF' ? 'article' : 'tab' }}</span>
                        </div>
                        <h3 class="text-xs font-extrabold text-slate-900 leading-tight">{{ $res['title'] }}</h3>
                        <p class="text-[11px] font-medium text-slate-400 mt-1">{{ $res['author'] }} &bull; {{ $res['posted_at'] }}</p>
                    </div>

                    <div class="mt-4 flex items-center gap-1.5">
                        <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                            {{ $res['type'] }}
                        </span>
                        @if($res['priority'])
                            <span class="inline-flex items-center text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-rose-50 text-rose-700">
                                {{ $res['priority'] }}
                            </span>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 sm:col-span-2 text-center py-4">Aucune ressource partagée pour l'instant.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>

@endif

@endsection
