@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-[1600px] w-full mx-auto space-y-5" x-data="timetableEditor">

    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Éditeur d'Emploi du Temps</h1>
            <p class="text-[13.5px] text-slate-500 mt-1">Concevez, optimisez et publiez les plannings avec l'assistance IA.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <button @click="saveTimetable()" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-[13px] px-4 py-2 rounded-xl shadow-sm transition flex items-center gap-2" :class="isSaving ? 'opacity-75 cursor-wait' : ''" :disabled="isSaving">
                <span x-show="!isSaving">Enregistrer le brouillon</span>
                <span x-show="isSaving" style="display:none;" class="flex items-center gap-2"><i class="ph-bold ph-spinner animate-spin"></i> Enregistrement...</span>
            </button>
            <button @click="saveTimetable(true)" class="bg-[#7C3AED] hover:bg-[#6D28D9] text-white font-semibold text-[13px] px-4 py-2 rounded-xl shadow-sm transition flex items-center gap-2" :class="isPublishing ? 'opacity-75 cursor-wait' : ''" :disabled="isPublishing">
                <i class="ph-bold ph-upload-simple" x-show="!isPublishing"></i>
                <i class="ph-bold ph-spinner animate-spin" x-show="isPublishing" style="display: none;"></i>
                <span x-text="isPublishing ? 'Publication...' : 'Publier l\'emploi du temps'">Publier l'emploi du temps</span>
            </button>
            <button @click="isGenerating = true; setTimeout(() => isGenerating = false, 2500)" class="bg-[#1E1B4B] hover:bg-[#312E81] text-white font-bold text-[13px] px-4 py-2 rounded-xl shadow-sm transition flex items-center gap-2" :class="isGenerating ? 'opacity-75 cursor-wait' : ''" :disabled="isGenerating">
                <i class="ph-bold ph-magic-wand" x-show="!isGenerating"></i>
                <i class="ph-bold ph-spinner animate-spin" x-show="isGenerating" style="display: none;"></i>
                <span x-text="isGenerating ? 'Génération IA...' : 'Générer via IA'">Générer via IA</span>
            </button>
        </div>
    </div>

    <!-- Filters & Status Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-6">
            <form action="{{ route('school.academic.timetable.create') }}" method="GET" class="flex items-center gap-2 m-0">
                <span class="text-[13px] font-semibold text-slate-700">Classe:</span>
                <select name="class_id" onchange="this.form.submit()" class="appearance-none bg-white border border-slate-200 text-slate-700 text-[13px] font-medium rounded-lg px-3 py-1.5 outline-none focus:border-[#2F5F76] min-w-[140px] cursor-pointer">
                    <option value="">Sélectionner une classe</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ (isset($classId) && $classId == $class->id) ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            
            <div class="flex items-center gap-2">
                <span class="text-[13px] font-semibold text-slate-700">Période:</span>
                <select class="appearance-none bg-white border border-slate-200 text-slate-700 text-[13px] font-medium rounded-lg px-3 py-1.5 outline-none focus:border-[#2F5F76] min-w-[160px]">
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}">{{ $semester->name }} {{ $semester->is_current ? '(En cours)' : '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-[#7C3AED] animate-pulse"></div>
            <span class="text-[13px] font-semibold text-slate-700">Statut: Modification en cours</span>
        </div>
    </div>

    <!-- Main Editor Layout -->
    <div class="flex flex-col lg:flex-row gap-6">
        
        <!-- Left Panel: Resources -->
        <div class="hidden xl:flex w-[260px] bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex-col h-[calc(100vh-230px)] min-h-[500px]">
            <div class="p-5 border-b border-slate-100">
                <h2 class="text-[18px] font-bold text-slate-800">Ressources</h2>
            </div>
            
            <!-- Tabs -->
            <div class="flex border-b border-slate-100 px-2">
                <button @click="activeTab = 'matieres'" class="flex-1 py-3 text-[12px] font-bold text-center border-b-2 transition" :class="activeTab === 'matieres' ? 'border-[#1E40AF] text-[#1E40AF]' : 'border-transparent text-slate-500 hover:text-slate-700'">
                    Matières
                </button>
                <button @click="activeTab = 'salles'" class="flex-1 py-3 text-[12px] font-bold text-center border-b-2 transition" :class="activeTab === 'salles' ? 'border-[#1E40AF] text-[#1E40AF]' : 'border-transparent text-slate-500 hover:text-slate-700'">
                    Salles
                </button>
            </div>

            <!-- Tab Content -->
            <div class="p-4 flex-1 overflow-y-auto" id="resources-panel">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Glisser-déposer sur la grille</p>
                <p class="text-[11px] text-slate-500 mb-4 bg-blue-50 p-2 rounded-lg border border-blue-100">Workflow: 1. Glissez la matière, 2. Glissez le prof dessus, 3. Glissez la salle dessus.</p>

                <!-- MATIERES -->
                <div x-show="activeTab === 'matieres'" class="space-y-3">
                    @forelse($subjects as $subject)
                    <div draggable="true" data-type="matiere" data-id="{{ $subject->id }}" data-name="{{ $subject->name }}" data-color="{{ $subject->color ?? '#3B82F6' }}" data-prof="{{ $subject->teachers->count() > 0 ? $subject->teachers->map(function($t) { return $t->first_name . ' ' . $t->last_name; })->implode(', ') : '' }}" data-teacher-id="{{ $subject->teachers->first()->id ?? '' }}" class="resource-card bg-white border border-slate-200 rounded-xl p-3 flex gap-3 cursor-move hover:shadow-md transition" style="border-left: 4px solid {{ $subject->color ?? '#3B82F6' }};">
                        <div class="flex-1 flex flex-col justify-center">
                            <div class="text-[14px] font-bold text-slate-800">{{ $subject->name }}</div>
                            <div class="text-[11.5px] font-medium text-slate-500 mt-0.5">
                                @if($subject->teachers->count() > 0)
                                    {{ $subject->teachers->map(function($t) { return $t->first_name . ' ' . $t->last_name; })->implode(', ') }}
                                @else
                                    <span class="italic text-slate-400">Aucun prof. assigné</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center p-4 border border-dashed border-slate-300 rounded-xl bg-slate-50">
                        <i class="ph-bold ph-warning-circle text-slate-400 text-2xl mb-2"></i>
                        <p class="text-[12px] text-slate-500 font-medium">Aucune matière trouvée.<br>Veuillez sélectionner une classe.</p>
                    </div>
                    @endforelse
                    
                    <div draggable="true" data-type="pause" data-name="Récréation" data-color="slate" class="resource-card bg-slate-50 border border-slate-200 rounded-xl p-3 flex gap-3 cursor-move hover:border-slate-300 hover:shadow-md transition mt-4">
                        <div class="w-2 h-full min-h-[40px] rounded-full bg-slate-400"></div>
                        <div class="flex-1 flex flex-col justify-center">
                            <div class="text-[14px] font-bold text-slate-700">Récréation</div>
                            <div class="text-[11px] text-slate-500">Temps de pause</div>
                        </div>
                    </div>
                    
                    <div draggable="true" data-type="pause" data-name="Pause Déjeuner" data-color="slate" class="resource-card bg-slate-50 border border-slate-200 rounded-xl p-3 flex gap-3 cursor-move hover:border-slate-300 hover:shadow-md transition">
                        <div class="w-2 h-full min-h-[40px] rounded-full bg-slate-400"></div>
                        <div class="flex-1 flex flex-col justify-center">
                            <div class="text-[14px] font-bold text-slate-700">Pause Déjeuner</div>
                            <div class="text-[11px] text-slate-500">Temps de pause</div>
                        </div>
                    </div>
                </div>
                
                <!-- SALLES -->
                <div x-show="activeTab === 'salles'" style="display: none;" class="space-y-3">
                    @forelse($rooms as $room)
                    <div draggable="true" data-type="salle" data-id="{{ $room->id }}" data-name="{{ $room->name }}" class="resource-card bg-white border border-slate-200 rounded-xl p-3 flex gap-3 cursor-move hover:border-slate-300 hover:shadow-md transition">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-500 pointer-events-none"><i class="ph-fill ph-door text-[20px]"></i></div>
                        <div class="flex-1 flex flex-col justify-center pointer-events-none">
                            <div class="text-[14px] font-bold text-slate-800">{{ $room->name }}</div>
                            <div class="text-[11.5px] font-medium text-emerald-600 mt-0.5">{{ $room->building->name ?? 'Sans bâtiment' }} ({{ $room->capacity }} pl.)</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-sm text-slate-500 italic p-3 text-center">Aucune salle disponible</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Middle Column: Grid -->
        <div class="flex-1 bg-white rounded-xl shadow-sm border border-slate-200 overflow-x-auto h-[calc(100vh-230px)] min-h-[500px]">
            <div class="min-w-[700px] h-full flex flex-col" id="timetable-grid">
                
                <!-- Grid Header -->
                <div class="grid grid-cols-[60px_1fr_1fr_1fr_1fr_1fr] border-b border-slate-200 bg-[#FAFBFC] sticky top-0 z-10">
                    <div class="p-3 flex items-center justify-center border-r border-slate-200 text-slate-400">
                        <i class="ph ph-clock text-[18px]"></i>
                    </div>
                    <div class="p-3 text-center border-r border-slate-200 text-[13px] font-bold text-slate-700">Lundi</div>
                    <div class="p-3 text-center border-r border-slate-200 text-[13px] font-bold text-slate-700">Mardi</div>
                    <div class="p-3 text-center border-r border-slate-200 text-[13px] font-bold text-slate-700">Mercredi</div>
                    <div class="p-3 text-center border-r border-slate-200 text-[13px] font-bold text-slate-700">Jeudi</div>
                    <div class="p-3 text-center text-[13px] font-bold text-slate-700">Vendredi</div>
                </div>

                <!-- Grid Body -->
                <div class="flex-1 overflow-y-auto">
                    
                    @php
                        $times = ['08:00', '09:00', '10:00', '10:30', '11:30', '12:30', '14:00', '15:00', '16:00', '17:00'];
                    @endphp

                    @foreach($times as $index => $time)
                        
                        <div class="grid grid-cols-[60px_1fr_1fr_1fr_1fr_1fr] border-b border-slate-100 min-h-[100px] relative {{ in_array($time, ['10:00', '12:30']) ? 'bg-[#FAFBFC]' : '' }}">
                            <div class="p-2 border-r border-slate-200 flex justify-center pt-3 text-[11px] font-bold text-slate-500 bg-[#FAFBFC]">{{ $time }}</div>
                            <div class="p-1.5 border-r border-slate-100 border-dashed dropzone hover:bg-slate-50 transition" data-day="lundi" data-time="{{ $time }}"></div>
                            <div class="p-1.5 border-r border-slate-100 border-dashed dropzone hover:bg-slate-50 transition" data-day="mardi" data-time="{{ $time }}"></div>
                            <div class="p-1.5 border-r border-slate-100 border-dashed dropzone hover:bg-slate-50 transition" data-day="mercredi" data-time="{{ $time }}"></div>
                            <div class="p-1.5 border-r border-slate-100 border-dashed dropzone hover:bg-slate-50 transition" data-day="jeudi" data-time="{{ $time }}"></div>
                            <div class="p-1.5 dropzone hover:bg-slate-50 transition" data-day="vendredi" data-time="{{ $time }}"></div>
                        </div>

                    @endforeach

                </div>
            </div>
        </div>

        <!-- Right Panel: AI & Conflicts -->
        <div class="hidden lg:block w-[300px] space-y-5">
            <!-- Assistant IA Card -->
            <div class="bg-[#F8F5FF] rounded-2xl shadow-sm border border-[#E9D5FF] p-5">
                <div class="flex items-center gap-3 mb-4">
                    <i class="ph-bold ph-sparkle text-[24px] text-[#9333EA]"></i>
                    <h3 class="text-[18px] font-bold text-[#9333EA]">Assistant IA</h3>
                </div>
                
                <p class="text-[13.5px] text-slate-600 font-medium mb-5 leading-relaxed">
                    L'IA peut optimiser ce brouillon pour réduire les temps morts et équilibrer la charge.
                </p>

                <div class="space-y-3">
                    <button @click="isOptimizingBreaks = true; setTimeout(() => isOptimizingBreaks = false, 2000)" class="w-full bg-white hover:bg-slate-50 border border-[#E9D5FF] text-[#9333EA] font-semibold text-[13px] py-2.5 px-4 rounded-xl shadow-sm transition flex items-center justify-between" :class="isOptimizingBreaks ? 'opacity-75 cursor-wait bg-slate-50' : ''" :disabled="isOptimizingBreaks">
                        <span x-text="isOptimizingBreaks ? 'Optimisation...' : 'Optimiser les pauses'">Optimiser les pauses</span>
                        <i class="ph-bold ph-lightning text-[16px]" x-show="!isOptimizingBreaks"></i>
                        <i class="ph-bold ph-spinner animate-spin text-[16px]" x-show="isOptimizingBreaks" style="display: none;"></i>
                    </button>
                    <button @click="isBalancing = true; setTimeout(() => isBalancing = false, 2000)" class="w-full bg-white hover:bg-slate-50 border border-[#E9D5FF] text-[#9333EA] font-semibold text-[13px] py-2.5 px-4 rounded-xl shadow-sm transition flex items-center justify-between" :class="isBalancing ? 'opacity-75 cursor-wait bg-slate-50' : ''" :disabled="isBalancing">
                        <span x-text="isBalancing ? 'Équilibrage...' : 'Équilibrer heures profs'">Équilibrer heures profs</span>
                        <i class="ph-bold ph-scales text-[16px]" x-show="!isBalancing"></i>
                        <i class="ph-bold ph-spinner animate-spin text-[16px]" x-show="isBalancing" style="display: none;"></i>
                    </button>
                </div>
            </div>

            <!-- Conflits Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-red-100 overflow-hidden" id="conflicts-container">
                <div class="p-4 border-b border-red-50 bg-red-50/30 flex items-center gap-3">
                    <i class="ph-bold ph-warning text-[20px] text-red-500"></i>
                    <h3 class="text-[16px] font-bold text-red-600">Conflits (<span id="conflicts-count">0</span>)</h3>
                </div>
                
                <div class="p-4 flex flex-col gap-2" id="conflicts-list">
                    <p class="text-[13px] text-slate-500 text-center empty-msg">Aucun conflit détecté.</p>
                </div>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<style>
    .drag-over {
        background-color: #f1f5f9; /* slate-100 */
        border: 2px dashed #94a3b8 !important; /* slate-400 */
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        
        let draggedItem = null;

        // Helper functions for time calculations
        const timeToHours = (t) => {
            if (!t) return 0;
            const parts = t.split(':');
            return parseInt(parts[0]) + (parseInt(parts[1] || 0) / 60);
        };
        
        const formatTime = (hours) => {
            const h = Math.floor(hours);
            const m = Math.round((hours - h) * 60);
            return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}`;
        };

        window.checkConflicts = () => {
            const conflicts = [];
            
            // Get all current blocks in the UI
            const currentBlocks = [];
            document.querySelectorAll('.course-block[data-subject-id]').forEach(block => {
                currentBlocks.push({
                    teacher_id: block.getAttribute('data-teacher-id') || null,
                    room_id: block.getAttribute('data-room-id') || null,
                    day: block.getAttribute('data-day'),
                    start_time: block.querySelector('.start-time').value,
                    end_time: block.querySelector('.end-time').value,
                    teacher_name: block.querySelector('.prof-placeholder').innerText,
                    room_name: block.querySelector('.salle-placeholder').innerText
                });
            });

            currentBlocks.forEach(block => {
                const blockStartH = timeToHours(block.start_time);
                const blockEndH = timeToHours(block.end_time);

                otherTimetables.forEach(other => {
                    if (other.day_of_week === block.day) {
                        const otherStartH = timeToHours(other.start_time);
                        const otherEndH = timeToHours(other.end_time);

                        // Check time overlap
                        if (blockStartH < otherEndH && otherStartH < blockEndH) {
                            const otherClassName = other.academic_class ? other.academic_class.name : 'une autre classe';
                            
                            // Room conflict
                            if (block.room_id && other.room_id && block.room_id == other.room_id) {
                                conflicts.push(`La <b>${block.room_name}</b> est occupée par <b>${otherClassName}</b> (${other.start_time.substring(0,5)}-${other.end_time.substring(0,5)}).`);
                            }

                            // Teacher conflict
                            if (block.teacher_id && other.teacher_id && block.teacher_id == other.teacher_id) {
                                conflicts.push(`Le prof <b>${block.teacher_name}</b> est occupé(e) avec <b>${otherClassName}</b> (${other.start_time.substring(0,5)}-${other.end_time.substring(0,5)}).`);
                            }
                        }
                    }
                });
            });

            const conflictsCount = document.getElementById('conflicts-count');
            const conflictsList = document.getElementById('conflicts-list');
            
            // Unique conflicts
            const uniqueConflicts = [...new Set(conflicts)];
            
            conflictsCount.innerText = uniqueConflicts.length;
            
            if (uniqueConflicts.length === 0) {
                conflictsList.innerHTML = '<p class="text-[13px] text-slate-500 text-center empty-msg">Aucun conflit détecté.</p>';
            } else {
                conflictsList.innerHTML = uniqueConflicts.map(c => `
                    <div class="bg-red-50 text-red-600 text-[11.5px] p-2 rounded border border-red-100 flex gap-2 items-start leading-tight">
                        <i class="ph-bold ph-warning mt-0.5 shrink-0"></i>
                        <div>${c}</div>
                    </div>
                `).join('');
            }
        };

        // Make resource cards draggable
        const cards = document.querySelectorAll('.resource-card');
        cards.forEach(card => {
            card.addEventListener('dragstart', (e) => {
                draggedItem = {
                    type: card.getAttribute('data-type'),
                    id: card.getAttribute('data-id'),
                    name: card.getAttribute('data-name'),
                    color: card.getAttribute('data-color') || 'slate',
                    prof: card.getAttribute('data-prof') || '',
                    teacher_id: card.getAttribute('data-teacher-id') || ''
                };
                // Make it look dragged
                setTimeout(() => card.classList.add('opacity-50'), 0);
            });
            card.addEventListener('dragend', () => {
                card.classList.remove('opacity-50');
                draggedItem = null;
            });
        });

        // Make grid cells droppable
        const dropzones = document.querySelectorAll('.dropzone');
        dropzones.forEach(zone => {
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('drag-over');
            });
            zone.addEventListener('dragleave', () => {
                zone.classList.remove('drag-over');
            });
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('drag-over');
                
                if (!draggedItem) return;

                if (draggedItem.type === 'matiere') {
                    const zoneTimeStr = zone.getAttribute('data-time') || '08:00';
                    const zoneTime = timeToHours(zoneTimeStr);
                    const startTimeStr = zoneTimeStr;
                    const endTimeStr = formatTime(zoneTime + 1); // 1 hour default
                    
                    const profText = draggedItem.prof ? draggedItem.prof : "Glissez un prof...";
                    const profClass = draggedItem.prof ? "" : "italic";
                    
                    // Create a new block in this cell
                    const blockHtml = `
                        <div class="course-block absolute top-1.5 left-1.5 right-1.5 bottom-1.5 bg-${draggedItem.color}-50/90 border border-${draggedItem.color}-200 rounded-lg p-2 z-20 border-t-4 border-t-${draggedItem.color}-500 flex flex-col justify-between shadow-sm transition-all duration-200" data-subject-id="${draggedItem.id}" data-teacher-id="${draggedItem.teacher_id}" data-day="${zone.getAttribute('data-day')}">
                            <div>
                                <div class="text-[11.5px] font-bold text-${draggedItem.color}-900 leading-tight">${draggedItem.name}</div>
                                <div class="text-[10px] font-medium text-${draggedItem.color}-600 mt-1 prof-placeholder ${profClass}">${profText}</div>
                            </div>
                            
                            <div class="flex flex-col gap-1.5 mt-2">
                                <div class="flex flex-col shrink-0">
                                    <div class="time-editor flex items-center bg-white border border-${draggedItem.color}-200 rounded p-0.5 text-${draggedItem.color}-700 shadow-sm w-fit" title="Heure début - fin">
                                        <input type="time" class="start-time text-[9px] font-medium outline-none w-[42px] bg-transparent text-center" value="${startTimeStr}">
                                        <span class="text-[9px] font-bold text-slate-400 mx-0.5">-</span>
                                        <input type="time" class="end-time text-[9px] font-medium outline-none w-[42px] bg-transparent text-center" value="${endTimeStr}">
                                        <button class="save-time-btn ml-0.5 text-emerald-600 hover:text-emerald-700 transition"><i class="ph-bold ph-check text-[11px]"></i></button>
                                    </div>
                                    <div class="time-display hidden text-[10px] font-bold text-${draggedItem.color}-700 bg-white/70 border border-white/50 px-1.5 py-0.5 rounded cursor-pointer hover:bg-white hover:shadow-sm transition w-fit" title="Modifier l'heure">
                                        <span class="display-start">${startTimeStr}</span> - <span class="display-end">${endTimeStr}</span>
                                    </div>
                                </div>
                                <div class="text-[11px] font-bold text-${draggedItem.color}-600 salle-placeholder italic">Glissez une salle...</div>
                            </div>
                            
                            <!-- delete button -->
                            <button class="delete-btn absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full w-5 h-5 flex items-center justify-center hover:bg-red-500 hover:text-white shadow-sm transition" title="Supprimer">
                                <i class="ph-bold ph-x text-[10px]"></i>
                            </button>
                        </div>
                    `;
                    zone.innerHTML = blockHtml;
                    zone.classList.add('relative');
                    
                    // Add delete listener
                    const delBtn = zone.querySelector('.delete-btn');
                    delBtn.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        zone.innerHTML = '';
                        zone.classList.remove('relative');
                        window.checkConflicts();
                    });
                    
                    // Add time listener
                    const startInput = zone.querySelector('.start-time');
                    const endInput = zone.querySelector('.end-time');
                    const block = zone.querySelector('.course-block');
                    const zoneTimeStrValue = zone.getAttribute('data-time') || '08:00';
                    const baseZoneTime = timeToHours(zoneTimeStrValue);
                    
                    const updateTimeLayout = () => {
                        let startH = timeToHours(startInput.value);
                        let endH = timeToHours(endInput.value);
                        
                        if (endH <= startH) {
                            endH = startH + 0.25; // min 15 minutes
                            endInput.value = formatTime(endH);
                        }
                        
                        const duration = endH - startH;
                        const offset = startH - baseZoneTime; // How far down from the cell's top
                        
                        const rowHeight = zone.offsetHeight || 100;
                        
                        // offset mapping: 1 hour = rowHeight.
                        const topPx = (offset * rowHeight) + 6; // +6px for default inset (1.5 top)
                        const heightPx = (duration * rowHeight) - 12; // -12px for padding/inset
                        
                        block.style.top = topPx + 'px';
                        block.style.bottom = 'auto';
                        block.style.height = heightPx + 'px';
                        
                        window.checkConflicts();
                    };

                    startInput.addEventListener('change', (ev) => { ev.stopPropagation(); updateTimeLayout(); });
                    endInput.addEventListener('change', (ev) => { ev.stopPropagation(); updateTimeLayout(); });
                    
                    // Toggle visibility
                    const timeEditor = zone.querySelector('.time-editor');
                    const timeDisplay = zone.querySelector('.time-display');
                    const saveBtn = zone.querySelector('.save-time-btn');
                    const displayStart = zone.querySelector('.display-start');
                    const displayEnd = zone.querySelector('.display-end');
                    
                    saveBtn.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        displayStart.innerText = startInput.value;
                        displayEnd.innerText = endInput.value;
                        timeEditor.classList.add('hidden');
                        timeEditor.classList.remove('flex');
                        timeDisplay.classList.remove('hidden');
                    });
                    
                    timeDisplay.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        timeDisplay.classList.add('hidden');
                        timeEditor.classList.remove('hidden');
                        timeEditor.classList.add('flex');
                    });
                    
                    window.checkConflicts();

                } else if (draggedItem.type === 'prof') {
                    // Update prof in existing block
                    const profPlaceholder = zone.querySelector('.prof-placeholder');
                    if (profPlaceholder) {
                        profPlaceholder.innerText = draggedItem.name;
                        profPlaceholder.classList.remove('italic');
                    } else {
                        alert("Veuillez d'abord glisser une matière dans cette case.");
                    }
                } else if (draggedItem.type === 'salle') {
                    // Update salle in existing block
                    const sallePlaceholder = zone.querySelector('.salle-placeholder');
                    const courseBlock = zone.querySelector('.course-block');
                    if (sallePlaceholder && courseBlock) {
                        sallePlaceholder.innerText = draggedItem.name;
                        sallePlaceholder.classList.remove('italic');
                        courseBlock.setAttribute('data-room-id', draggedItem.id);
                        window.checkConflicts();
                    } else {
                        alert("Veuillez d'abord glisser une matière dans cette case.");
                    }
                } else if (draggedItem.type === 'pause') {
                    // Create a simple break block
                    const blockHtml = `
                        <div class="course-block absolute inset-1.5 bg-${draggedItem.color}-50 border border-${draggedItem.color}-200 rounded-lg p-2 z-10 flex items-center justify-center flex-col">
                            <i class="ph-fill ph-coffee text-${draggedItem.color}-400 text-[20px] mb-1"></i>
                            <div class="text-[11.5px] font-bold text-${draggedItem.color}-700 uppercase tracking-widest text-center">${draggedItem.name}</div>
                            
                            <!-- delete button -->
                            <button class="delete-btn absolute top-1 right-1 text-${draggedItem.color}-400 hover:text-${draggedItem.color}-700 transition" title="Supprimer">
                                <i class="ph-bold ph-x"></i>
                            </button>
                        </div>
                    `;
                    zone.innerHTML = blockHtml;
                    zone.classList.add('relative');
                    
                    // Add delete listener
                    const delBtn = zone.querySelector('.delete-btn');
                    delBtn.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        zone.innerHTML = '';
                    });
                }
            });
        });

        // Initialize existing timetables
        const gridTimes = ['08:00', '09:00', '10:00', '10:30', '11:30', '12:30', '14:00', '15:00', '16:00', '17:00'];
        const existingTimetables = @json($existingTimetables ?? []);
        const otherTimetables = @json($otherTimetables ?? []);
        
        const getDropzoneForTime = (day, timeStr) => {
            const timeH = timeToHours(timeStr);
            let closestTime = gridTimes[0];
            for (let i = 0; i < gridTimes.length; i++) {
                if (timeToHours(gridTimes[i]) <= timeH) {
                    closestTime = gridTimes[i];
                } else {
                    break;
                }
            }
            return document.querySelector(`.dropzone[data-day="${day}"][data-time="${closestTime}"]`);
        };

        existingTimetables.forEach(item => {
            const zone = getDropzoneForTime(item.day_of_week, item.start_time);
            if (!zone) return;
            
            const startH = timeToHours(item.start_time);
            const endH = timeToHours(item.end_time);
            const duration = endH - startH;
            
            const baseZoneTimeStr = zone.getAttribute('data-time');
            const baseZoneTime = timeToHours(baseZoneTimeStr);
            const offset = startH - baseZoneTime;
            
            const rowHeight = 100; // default height in css
            const topPx = (offset * rowHeight) + 6;
            const heightPx = (duration * rowHeight) - 12;
            
            const subjectName = item.subject ? item.subject.name : '';
            const subjectColor = item.subject && item.subject.color ? item.subject.color : 'blue';
            const teacherName = item.teacher ? (item.teacher.first_name + ' ' + item.teacher.last_name) : 'Glissez un prof...';
            const teacherClass = item.teacher ? '' : 'italic';
            const teacherId = item.teacher ? item.teacher.id : '';
            const roomId = item.room ? item.room.id : '';
            const roomName = item.room ? item.room.name : 'Glissez une salle...';
            const roomClass = item.room ? '' : 'italic';
            const subjectId = item.subject ? item.subject.id : '';
            const startTimeStr = item.start_time.substring(0, 5); 
            const endTimeStr = item.end_time.substring(0, 5);
            
            const blockHtml = `
                <div class="course-block absolute bg-${subjectColor}-50/90 border border-${subjectColor}-200 rounded-lg p-2 z-20 border-t-4 border-t-${subjectColor}-500 flex flex-col justify-between shadow-sm transition-all duration-200" data-subject-id="${subjectId}" data-teacher-id="${teacherId}" data-room-id="${roomId}" data-day="${item.day_of_week}" style="top: ${topPx}px; height: ${heightPx}px; left: 6px; right: 6px;">
                    <div>
                        <div class="text-[11.5px] font-bold text-${subjectColor}-900 leading-tight">${subjectName}</div>
                        <div class="text-[10px] font-medium text-${subjectColor}-600 mt-1 prof-placeholder ${teacherClass}">${teacherName}</div>
                    </div>
                    
                    <div class="flex flex-col gap-1.5 mt-2">
                        <div class="flex flex-col shrink-0">
                            <div class="time-editor hidden items-center bg-white border border-${subjectColor}-200 rounded p-0.5 text-${subjectColor}-700 shadow-sm w-fit" title="Heure début - fin">
                                <input type="time" class="start-time text-[9px] font-medium outline-none w-[42px] bg-transparent text-center" value="${startTimeStr}">
                                <span class="text-[9px] font-bold text-slate-400 mx-0.5">-</span>
                                <input type="time" class="end-time text-[9px] font-medium outline-none w-[42px] bg-transparent text-center" value="${endTimeStr}">
                                <button class="save-time-btn ml-0.5 text-emerald-600 hover:text-emerald-700 transition"><i class="ph-bold ph-check text-[11px]"></i></button>
                            </div>
                            <div class="time-display text-[10px] font-bold text-${subjectColor}-700 bg-white/70 border border-white/50 px-1.5 py-0.5 rounded cursor-pointer hover:bg-white hover:shadow-sm transition w-fit" title="Modifier l'heure">
                                <span class="display-start">${startTimeStr}</span> - <span class="display-end">${endTimeStr}</span>
                            </div>
                        </div>
                        <div class="text-[11px] font-bold text-${subjectColor}-600 salle-placeholder ${roomClass}">${roomName}</div>
                    </div>
                    
                    <!-- delete button -->
                    <button class="delete-btn absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full w-5 h-5 flex items-center justify-center hover:bg-red-500 hover:text-white shadow-sm transition" title="Supprimer">
                        <i class="ph-bold ph-x text-[10px]"></i>
                    </button>
                </div>
            `;
            
            zone.innerHTML = blockHtml;
            zone.classList.add('relative');
            
            const block = zone.querySelector('.course-block');
            const delBtn = zone.querySelector('.delete-btn');
            delBtn.addEventListener('click', (ev) => {
                ev.stopPropagation();
                zone.innerHTML = '';
                zone.classList.remove('relative');
                window.checkConflicts();
            });
            
            const startInput = zone.querySelector('.start-time');
            const endInput = zone.querySelector('.end-time');
            
            const updateTimeLayout = () => {
                let currentStartH = timeToHours(startInput.value);
                let currentEndH = timeToHours(endInput.value);
                
                if (currentEndH <= currentStartH) {
                    currentEndH = currentStartH + 0.25;
                    endInput.value = formatTime(currentEndH);
                }
                
                const currentDuration = currentEndH - currentStartH;
                const currentOffset = currentStartH - baseZoneTime;
                
                const rHeight = zone.offsetHeight || 100;
                
                const tPx = (currentOffset * rHeight) + 6;
                const hPx = (currentDuration * rHeight) - 12;
                
                block.style.top = tPx + 'px';
                block.style.bottom = 'auto';
                block.style.height = hPx + 'px';
            };

            startInput.addEventListener('change', (ev) => { ev.stopPropagation(); updateTimeLayout(); });
            endInput.addEventListener('change', (ev) => { ev.stopPropagation(); updateTimeLayout(); });
            
            const timeEditor = zone.querySelector('.time-editor');
            const timeDisplay = zone.querySelector('.time-display');
            const saveBtn = zone.querySelector('.save-time-btn');
            const displayStart = zone.querySelector('.display-start');
            const displayEnd = zone.querySelector('.display-end');
            
            saveBtn.addEventListener('click', (ev) => {
                ev.stopPropagation();
                displayStart.innerText = startInput.value;
                displayEnd.innerText = endInput.value;
                timeEditor.classList.add('hidden');
                timeEditor.classList.remove('flex');
                timeDisplay.classList.remove('hidden');
                window.checkConflicts();
            });
            
            timeDisplay.addEventListener('click', (ev) => {
                ev.stopPropagation();
                timeDisplay.classList.add('hidden');
                timeEditor.classList.remove('hidden');
                timeEditor.classList.add('flex');
            });
        });

        // Initial check on load
        window.checkConflicts();

    });
</script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('timetableEditor', () => ({
            activeTab: 'matieres', 
            isGenerating: false, 
            isSaving: false, 
            isPublishing: false, 
            isOptimizingBreaks: false, 
            isBalancing: false,
            
            saveTimetable(publish = false) {
                const classId = document.querySelector('select[name="class_id"]').value;
                if (!classId) {
                    alert('Veuillez sélectionner une classe.');
                    return;
                }
                
                if (publish) {
                    this.isPublishing = true;
                } else {
                    this.isSaving = true;
                }
                
                const blocks = [];
                document.querySelectorAll('.course-block[data-subject-id]').forEach(block => {
                    blocks.push({
                        subject_id: block.getAttribute('data-subject-id'),
                        teacher_id: block.getAttribute('data-teacher-id') || null,
                        room_id: block.getAttribute('data-room-id') || null,
                        day: block.getAttribute('data-day'),
                        start_time: block.querySelector('.start-time').value,
                        end_time: block.querySelector('.end-time').value
                    });
                });
                
                fetch('{{ route("school.academic.timetable.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        class_id: classId,
                        status: publish ? 'published' : 'draft',
                        blocks: blocks
                    })
                })
                .then(response => response.json())
                .then(data => {
                    this.isSaving = false;
                    this.isPublishing = false;
                    if (data.success) {
                        alert(publish ? 'Emploi du temps publié avec succès !' : 'Brouillon enregistré avec succès !');
                    } else {
                        alert('Une erreur est survenue.');
                    }
                })
                .catch(err => {
                    this.isSaving = false;
                    this.isPublishing = false;
                    console.error(err);
                    alert('Erreur de connexion au serveur.');
                });
            }
        }));
    });
</script>
@endpush
@endsection
