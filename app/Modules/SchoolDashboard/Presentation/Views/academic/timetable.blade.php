@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-[1400px] mx-auto space-y-6" x-data="{
    isEditing: false,
    isGenerating: false,
    optimizerLoading: false,
    optimizerChecked: false,
    optimizerData: null,
    checkOptimizer() {
        this.optimizerLoading = true;
        this.optimizerChecked = false;
        fetch('{{ route('school.academic.timetable.ai-optimizer-check') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            this.optimizerData = data;
            this.optimizerChecked = true;
        })
        .catch(() => {
            this.optimizerData = { has_issues: false, error: true };
            this.optimizerChecked = true;
        })
        .finally(() => { this.optimizerLoading = false; });
    },
    weekOffset: 0,
    initialDate: '{{ isset($initialDate) ? $initialDate->format('Y-m-d') : (isset($targetDate) ? $targetDate->format('Y-m-d') : now()->format('Y-m-d')) }}',
    getWeekDates() {
        let d = new Date(this.initialDate + 'T00:00:00');
        const day = d.getDay() || 7; // Convert Sunday(0) to 7
        if (day !== 1) d.setDate(d.getDate() - (day - 1)); // Align to Monday
        d.setDate(d.getDate() + (this.weekOffset * 7));
        
        let dates = [];
        for(let i=0; i<5; i++) {
            let cur = new Date(d);
            cur.setDate(cur.getDate() + i);
            dates.push(cur.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }).replace('.', ''));
        }
        return dates;
    },
    getWeekRange() {
        const dates = this.getWeekDates();
        if(dates.length === 0) return '';
        let d = new Date(this.initialDate + 'T00:00:00');
        const day = d.getDay() || 7;
        if (day !== 1) d.setDate(d.getDate() - (day - 1));
        d.setDate(d.getDate() + (this.weekOffset * 7));
        let endD = new Date(d);
        endD.setDate(endD.getDate() + 4);
        return `${dates[0]} – ${dates[4]} ${endD.getFullYear()}`;
    }
}">

    @if(!$classId)
        <!-- Classes Grid View -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Emplois du temps</h1>
                <p class="text-slate-500 mt-1 text-[14px]">Sélectionnez une classe pour afficher son emploi du temps détaillé.</p>
            </div>
            <a href="{{ route('school.academic.timetable.create') }}" class="bg-[#1E40AF] hover:bg-[#1E3A8A] text-white font-bold text-[13.5px] px-4 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="ph-bold ph-plus"></i>
                Nouveau
            </a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($classes as $c)
            <a href="{{ route('school.academic.timetable', ['class_id' => $c->id]) }}" class="group bg-white border border-slate-200 rounded-2xl p-6 flex flex-col hover:shadow-xl hover:border-indigo-200 hover:-translate-y-1 transition-all duration-300">
                <div class="w-14 h-14 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-5 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                    <i class="ph-fill ph-users-three text-[28px]"></i>
                </div>
                <h3 class="text-[17px] font-bold text-slate-800 mb-1">{{ $c->name }}</h3>
                <div class="flex items-center gap-1.5 mt-auto pt-4 text-[13px] font-bold text-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Consulter</span>
                    <i class="ph-bold ph-arrow-right"></i>
                </div>
            </a>
            @endforeach
        </div>
    @else
    <!-- Top Filters & Actions Bar -->
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        
        <!-- Filters (Left) -->
        <div class="flex flex-wrap items-end gap-4">
            
            <div class="flex items-center gap-3">
                <a href="{{ route('school.academic.timetable') }}" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition shadow-sm" title="Retour aux classes">
                    <i class="ph-bold ph-arrow-left text-[16px]"></i>
                </a>
                <div>
                    <label class="block text-[12px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Classe / Filière</label>
                    @php $currentClass = $classes->firstWhere('id', $classId); @endphp
                    <div class="text-[16px] font-bold text-slate-800 flex items-center gap-2">
                        <i class="ph-fill ph-users-three text-indigo-500"></i>
                        {{ $currentClass ? $currentClass->name : 'Classe inconnue' }}
                    </div>
                </div>
            </div>

            <div class="w-px h-10 bg-slate-200 mx-2 hidden sm:block"></div>

            <!-- Semestre / Période -->
            <div>
                <label class="block text-[12px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Période Académique</label>
                <form action="{{ route('school.academic.timetable') }}" method="GET" id="filter-semester-form" class="m-0">
                    <input type="hidden" name="class_id" value="{{ $classId }}">
                    <select name="semester_id" onchange="document.getElementById('filter-semester-form').submit()"
                        class="appearance-none bg-white border border-slate-200 text-slate-700 text-[13.5px] font-semibold rounded-xl px-3.5 py-2 outline-none focus:border-indigo-500 min-w-[170px] shadow-sm cursor-pointer">
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ ($selectedSemester && $selectedSemester->id == $sem->id) ? 'selected' : '' }}>
                                {{ $sem->name }} {{ $sem->is_current ? '(En cours)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="w-px h-10 bg-slate-200 mx-2 hidden sm:block"></div>

            <!-- Semaine -->
            <div>
                <label class="block text-[12px] font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Semaine</label>
                <div class="flex items-center bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden h-[42px]">
                    <button @click="weekOffset--" class="px-3 hover:bg-slate-50 text-slate-500 transition h-full flex items-center justify-center border-r border-slate-200">
                        <i class="ph-bold ph-caret-left text-[14px]"></i>
                    </button>
                    <div class="px-4 text-[13.5px] font-bold text-slate-800 whitespace-nowrap" x-text="getWeekRange()">
                    </div>
                    <button @click="weekOffset++" class="px-3 hover:bg-slate-50 text-slate-500 transition h-full flex items-center justify-center border-l border-slate-200">
                        <i class="ph-bold ph-caret-right text-[14px]"></i>
                    </button>
                </div>
            </div>
            
        </div>

        <!-- Actions (Right) -->
        <div class="flex items-center gap-3">
            @php
                $activeMonthObj = collect($months)->firstWhere('value', $selectedMonth);
                $activeMonthLabel = $activeMonthObj['label'] ?? $selectedMonth;
            @endphp
            <a href="{{ route('school.academic.timetable.create', ['class_id' => $classId, 'semester_id' => $selectedSemester?->id, 'month' => $selectedMonth]) }}" 
               class="bg-[#1E40AF] hover:bg-[#1E3A8A] text-white font-bold text-[13.5px] px-4 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2"
               title="Modifier l'emploi du temps à partir de {{ $activeMonthLabel }}">
                <i class="ph-bold ph-pencil-simple"></i>
                <span>Modifier ({{ $activeMonthObj['short'] ?? 'Mois' }})</span>
            </a>
            <button @click="window.print()" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-[13.5px] px-4 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="ph-bold ph-printer"></i>
                Imprimer
            </button>
            <a href="{{ route('school.academic.timetable.breaks', ['class_id' => $classId]) }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-[13.5px] px-4 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="ph-bold ph-coffee"></i>
                Pauses
            </a>

            <button @click="isGenerating = true; setTimeout(() => isGenerating = false, 2000)" class="bg-[#7C3AED] hover:bg-[#6D28D9] text-white font-bold text-[13.5px] px-5 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2" :class="isGenerating ? 'opacity-75 cursor-wait' : ''" :disabled="isGenerating">
                <i class="ph-bold ph-magic-wand" x-show="!isGenerating"></i>
                <i class="ph-bold ph-spinner animate-spin" x-show="isGenerating" style="display: none;"></i>
                <span x-text="isGenerating ? 'Génération...' : 'Générer via IA'">Générer via IA</span>
            </button>
        </div>
    </div>

    <!-- Mois Navigation Pills -->
    @if(!empty($months))
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2.5">
            <span class="text-[12px] font-bold text-slate-500 uppercase tracking-wider mr-1 shrink-0 flex items-center gap-1.5">
                <i class="ph-bold ph-calendar text-[16px] text-indigo-600"></i> Mois du {{ $selectedSemester?->name ?? 'Semestre' }} :
            </span>
            @foreach($months as $m)
                @php $isSelected = ($selectedMonth === $m['value']); @endphp
                <a href="{{ route('school.academic.timetable', ['class_id' => $classId, 'semester_id' => $selectedSemester?->id, 'month' => $m['value']]) }}"
                   class="px-4 py-2 rounded-xl text-[13px] font-bold transition flex items-center gap-2 {{ $isSelected ? 'bg-[#1E40AF] text-white shadow-md shadow-blue-500/20' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200' }}">
                    <i class="ph-bold ph-calendar-blank text-[14px]"></i>
                    <span>{{ $m['label'] }}</span>
                    @if($m['value'] === now()->format('Y-m'))
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold {{ $isSelected ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">En cours</span>
                    @endif
                </a>
            @endforeach
        </div>
        
        <div class="text-[12px] text-slate-600 font-medium flex items-center gap-2 bg-blue-50/80 border border-blue-100 px-3.5 py-2 rounded-xl">
            <i class="ph-fill ph-info text-blue-600 text-[16px] shrink-0"></i>
            <span>Affichage effectif pour <strong>{{ $activeMonthLabel }}</strong>. Les modifications s'appliquent dès ce mois sans impacter le passé.</span>
        </div>
    </div>
    @endif

    <!-- Main Content: Timetable + Sidebar -->
    <div class="flex flex-col lg:flex-row gap-6">
        
        <!-- Left Column: Timetable Grid -->
        <div class="flex-1 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <div class="min-w-[800px]">
                    <!-- Header Row (Days) -->
                    <div class="grid grid-cols-[60px_1fr_1fr_1fr_1fr_1fr] border-b border-slate-100 bg-[#F4F7FB]">
                        <!-- Time Column Header -->
                        <div class="p-4 flex items-center justify-center border-r border-slate-100/50">
                            <i class="ph ph-clock text-[22px] text-slate-400"></i>
                        </div>
                        <!-- Days -->
                        <div class="p-3 text-center border-r border-slate-100/50">
                            <div class="text-[13px] font-bold text-[#1E40AF]">LUN.</div>
                            <div class="text-[11px] font-medium text-slate-500 mt-0.5" x-text="getWeekDates()[0]"></div>
                        </div>
                        <div class="p-3 text-center border-r border-slate-100/50">
                            <div class="text-[13px] font-bold text-[#1E40AF]">MAR.</div>
                            <div class="text-[11px] font-medium text-slate-500 mt-0.5" x-text="getWeekDates()[1]"></div>
                        </div>
                        <div class="p-3 text-center border-r border-slate-100/50">
                            <div class="text-[13px] font-bold text-[#1E40AF]">MER.</div>
                            <div class="text-[11px] font-medium text-slate-500 mt-0.5" x-text="getWeekDates()[2]"></div>
                        </div>
                        <div class="p-3 text-center border-r border-slate-100/50">
                            <div class="text-[13px] font-bold text-[#1E40AF]">JEU.</div>
                            <div class="text-[11px] font-medium text-slate-500 mt-0.5" x-text="getWeekDates()[3]"></div>
                        </div>
                        <div class="p-3 text-center">
                            <div class="text-[13px] font-bold text-[#1E40AF]">VEN.</div>
                            <div class="text-[11px] font-medium text-slate-500 mt-0.5" x-text="getWeekDates()[4]"></div>
                        </div>
                    </div>

                    <!-- Timetable Body -->
                    <div class="divide-y divide-slate-100">
                        
                        @php
                            $days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
                            // Base grid rows (breaks shown as their own row) plus every real
                            // start time actually used by a published block — without this,
                            // a block starting between two listed rows (e.g. 13:00 when the
                            // grid only had 12:30 then 14:00) gets anchored to the row before
                            // it and visually overflows into the next row's block.
                            $times = collect(['08:00', '09:00', '10:00', '10:30', '11:30', '12:30', '14:00', '15:00', '16:00', '17:00'])
                                ->merge($timetables->pluck('start_time')->map(fn ($t) => substr($t, 0, 5)))
                                ->merge($breaks->pluck('start_time')->map(fn ($t) => substr($t, 0, 5)))
                                ->unique()
                                ->sort()
                                ->values()
                                ->all();
                            
                            $blocksLookup = [];
                            foreach($timetables as $t) {
                                $timeParts = explode(':', $t->start_time);
                                $timeH = intval($timeParts[0]) + (intval($timeParts[1]) / 60);
                                
                                $closestTime = $times[0];
                                foreach($times as $gridTimeStr) {
                                    $gridParts = explode(':', $gridTimeStr);
                                    $gridH = intval($gridParts[0]) + (intval($gridParts[1]) / 60);
                                    if ($gridH <= $timeH) {
                                        $closestTime = $gridTimeStr;
                                    } else {
                                        break;
                                    }
                                }
                                $blocksLookup[$t->day_of_week][$closestTime][] = $t;
                            }
                        @endphp

                        @foreach($times as $time)
                            @php $breaksByDay = $breaks->filter(fn($b) => substr($b->start_time, 0, 5) === $time)->keyBy('day_of_week'); @endphp

                            <div class="grid grid-cols-[60px_1fr_1fr_1fr_1fr_1fr] border-b border-slate-100 min-h-[100px] relative">
                                <div class="p-2 border-r border-slate-200 flex justify-center pt-3 text-[11px] font-bold text-slate-500 bg-[#FAFBFC] z-10">{{ $time }}</div>

                                @foreach($days as $day)
                                    @php $dayBreak = $breaksByDay->get($day); @endphp
                                    <div class="p-1.5 border-r border-slate-100 relative">
                                        @if($dayBreak)
                                        <div class="absolute inset-1.5 rounded-lg bg-{{ $dayBreak->color }}-50 border border-{{ $dayBreak->color }}-200 flex flex-col items-center justify-center gap-1" title="{{ $dayBreak->name }} ({{ substr($dayBreak->start_time, 0, 5) }}-{{ substr($dayBreak->end_time, 0, 5) }})">
                                            <i class="ph-fill ph-coffee text-{{ $dayBreak->color }}-500 text-[16px]"></i>
                                            <div class="text-[10.5px] font-bold text-{{ $dayBreak->color }}-700 text-center leading-tight px-1">{{ $dayBreak->name }}</div>
                                        </div>
                                        @elseif(isset($blocksLookup[$day][$time]))
                                            @foreach($blocksLookup[$day][$time] as $block)
                                                @php
                                                    $startParts = explode(':', $block->start_time);
                                                    $startH = intval($startParts[0]) + (intval($startParts[1]) / 60);
                                                    
                                                    $endParts = explode(':', $block->end_time);
                                                    $endH = intval($endParts[0]) + (intval($endParts[1]) / 60);
                                                    
                                                    $duration = $endH - $startH;
                                                    
                                                    $gridParts = explode(':', $time);
                                                    $baseZoneTimeH = intval($gridParts[0]) + (intval($gridParts[1]) / 60);
                                                    $offset = $startH - $baseZoneTimeH;
                                                    
                                                    $rowHeight = 100;
                                                    $topPx = ($offset * $rowHeight) + 6;
                                                    $heightPx = ($duration * $rowHeight) - 12;
                                                    
                                                    $color = $block->subject->color ?? 'blue';
                                                    if (strpos($color, '#') === 0) {
                                                        $color = 'blue';
                                                    }
                                                @endphp
                                                <div class="absolute bg-{{ $color }}-50/90 border border-{{ $color }}-200 rounded-lg p-2 z-20 border-t-4 border-t-{{ $color }}-500 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow" style="top: {{ $topPx }}px; height: {{ $heightPx }}px; left: 6px; right: 6px;">
                                                    <div>
                                                        <div class="text-[11.5px] font-bold text-{{ $color }}-900 leading-tight">{{ $block->subject->name ?? '' }}</div>
                                                        <div class="text-[10px] font-medium text-{{ $color }}-600 mt-1">{{ $block->teacher ? $block->teacher->first_name . ' ' . $block->teacher->last_name : 'Aucun prof.' }}</div>
                                                    </div>
                                                    <div>
                                                        <div class="text-[10px] font-bold text-{{ $color }}-700 bg-white/50 px-1.5 py-0.5 rounded inline-block w-fit">
                                                            {{ substr($block->start_time, 0, 5) }} - {{ substr($block->end_time, 0, 5) }}
                                                        </div>
                                                        @if($block->room)
                                                        <div class="text-[10px] font-bold text-{{ $color }}-600 mt-1">{{ $block->room->name }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
            
        </div>

        <!-- Right Column: AI Insights & Stats -->
        <div class="w-full lg:w-[320px] space-y-6">
            
            <!-- Optimiseur IA Card -->
            <div class="bg-white rounded-2xl shadow-[0_4px_20px_-4px_rgba(124,58,237,0.1)] border border-[#7C3AED]/20 p-5 relative overflow-hidden">
                <!-- Decorative background glow -->
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-[#7C3AED]/5 rounded-full blur-2xl pointer-events-none"></div>

                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-[#7C3AED]/10 text-[#7C3AED] flex items-center justify-center">
                        <i class="ph-fill ph-brain text-[20px]"></i>
                    </div>
                    <h3 class="text-[15px] font-bold text-slate-800">Optimiseur IA</h3>
                </div>
                
                <template x-if="!optimizerChecked && !optimizerLoading">
                    <div class="relative z-10">
                        <p class="text-[13px] text-slate-600 font-medium mb-5 leading-relaxed">
                            Vérifiez les conflits réels de salles et les surcharges d'enseignants sur l'ensemble des emplois du temps publiés de l'établissement.
                        </p>
                        <button @click="checkOptimizer()" class="w-full bg-[#7C3AED] hover:bg-[#6D28D9] text-white font-bold text-[13.5px] py-3 rounded-xl shadow-[0_4px_12px_rgba(124,58,237,0.3)] transition">
                            Vérifier les conflits
                        </button>
                    </div>
                </template>

                <template x-if="optimizerLoading">
                    <div class="py-6 text-center relative z-10">
                        <i class="ph-bold ph-spinner animate-spin text-2xl text-[#7C3AED]"></i>
                        <p class="text-[12px] text-slate-500 mt-2">Analyse de l'emploi du temps réel...</p>
                    </div>
                </template>

                <template x-if="optimizerChecked && !optimizerLoading && optimizerData && optimizerData.has_issues">
                    <div class="relative z-10">
                        <p class="text-[13px] text-slate-600 font-medium mb-4 leading-relaxed" x-text="optimizerData.summary || 'Des conflits ont été détectés.'"></p>
                        <div class="space-y-3 mb-4">
                            <template x-for="conflict in optimizerData.room_conflicts" :key="conflict.salle + conflict.jour + conflict.horaires.join('')">
                                <div class="bg-red-50 border border-red-100 rounded-xl p-3 flex gap-3">
                                    <i class="ph-fill ph-warning-circle text-red-500 mt-0.5"></i>
                                    <div class="min-w-0">
                                        <div class="text-[12px] font-bold text-red-800" x-text="conflict.salle + ' occupée — ' + conflict.jour"></div>
                                        <div class="text-[11px] font-medium text-red-600/80 mt-0.5 leading-tight" x-text="conflict.classes.join(' vs ') + ' (' + conflict.horaires.join(' / ') + ')'"></div>
                                        <template x-if="conflict.suggestion">
                                            <div class="text-[11px] font-bold text-emerald-700 mt-1.5 flex items-start gap-1">
                                                <i class="ph-fill ph-lightbulb mt-0.5 shrink-0"></i>
                                                <span x-text="conflict.suggestion"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            <template x-for="overload in optimizerData.teacher_overload" :key="overload.enseignant + overload.jour">
                                <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 flex gap-3">
                                    <i class="ph-fill ph-clock text-amber-500 mt-0.5"></i>
                                    <div>
                                        <div class="text-[12px] font-bold text-amber-800" x-text="'Surcharge ' + overload.enseignant"></div>
                                        <div class="text-[11px] font-medium text-amber-600/80 mt-0.5 leading-tight" x-text="overload.heures + 'h le ' + overload.jour"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <button @click="checkOptimizer()" class="w-full bg-white border border-[#7C3AED]/30 text-[#7C3AED] font-bold text-[13px] py-2.5 rounded-xl transition">
                            Revérifier
                        </button>
                    </div>
                </template>

                <template x-if="optimizerChecked && !optimizerLoading && optimizerData && !optimizerData.has_issues">
                    <div class="py-4 text-center relative z-10">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                            <i class="ph-bold ph-check text-[24px]"></i>
                        </div>
                        <h4 class="text-[14px] font-bold text-slate-800">Tout est optimisé</h4>
                        <p class="text-[12px] text-slate-500 mt-1">Aucun conflit réel détecté dans les emplois du temps publiés.</p>
                    </div>
                </template>
            </div>

            <!-- Statistiques Hebdo Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <h3 class="text-[14px] font-bold text-slate-800 mb-5">Statistiques Hebdo</h3>
                
                @php
                    $totalHours = 0;
                    $uniqueSubjects = [];
                    foreach($timetables as $t) {
                        $startParts = explode(':', $t->start_time);
                        $startH = intval($startParts[0]) + (intval($startParts[1]) / 60);
                        $endParts = explode(':', $t->end_time);
                        $endH = intval($endParts[0]) + (intval($endParts[1]) / 60);
                        $totalHours += ($endH - $startH);
                        $uniqueSubjects[$t->subject_id] = true;
                    }
                    $totalSubjectsCount = count($uniqueSubjects);
                    $maxHours = 40; // Base: 8h/jour * 5 jours = 40h
                    $occupancyRate = $maxHours > 0 ? min(100, round(($totalHours / $maxHours) * 100)) : 0;
                @endphp

                <!-- Progress bar -->
                <div class="mb-6">
                    <div class="flex justify-between items-end mb-2">
                        <span class="text-[12px] font-medium text-slate-500 uppercase tracking-wider">Taux d'occupation</span>
                        <span class="text-[20px] font-bold text-[#1E40AF]">{{ $occupancyRate }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-[#1E40AF] h-2 rounded-full" style="width: {{ $occupancyRate }}%"></div>
                    </div>
                </div>
                
                <!-- Stat Boxes -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-[#F8FAFC] rounded-xl p-3 border border-slate-100">
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Heures Total</div>
                        <div class="text-[18px] font-bold text-[#1E40AF]">{{ $totalHours }}h</div>
                    </div>
                    <div class="bg-[#F8FAFC] rounded-xl p-3 border border-slate-100">
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Matières</div>
                        <div class="text-[18px] font-bold text-[#7C3AED]">{{ $totalSubjectsCount }}</div>
                    </div>
                </div>
            </div>

            <!-- Prochaines Salles Libres Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <h3 class="text-[14px] font-bold text-slate-800 mb-4">Prochaines Salles Libres</h3>
                
                <div class="space-y-4">
                    @forelse($freeRooms as $room)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg {{ $room->is_free_now ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100' }} flex items-center justify-center border">
                                <i class="ph-fill ph-door"></i>
                            </div>
                            <span class="text-[13px] font-bold text-slate-700">{{ $room->name }}</span>
                        </div>
                        <span class="px-2 py-1 rounded {{ $room->is_free_now ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} text-[10px] font-bold uppercase tracking-wider">{{ $room->status }}</span>
                    </div>
                    @empty
                    <div class="text-center text-slate-500 text-[13px] py-4">
                        Aucune salle libre actuellement.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
    @endif
</div>
@endsection
