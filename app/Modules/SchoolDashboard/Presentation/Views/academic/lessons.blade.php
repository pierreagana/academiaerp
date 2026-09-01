@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('school.academic.syllabuses') }}" class="w-10 h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition shadow-sm">
                <i class="ph-bold ph-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Programme & Leçons : {{ $syllabus->subject->name }}</h1>
                <p class="text-[13.5px] text-slate-500 mt-1">
                    Classe : <span class="font-bold text-slate-700">{{ $syllabus->academicClass->name }}</span> | 
                    Semestre : <span class="font-bold text-slate-700">{{ $syllabus->semester ? $syllabus->semester->name : 'Non assigné' }}</span>
                </p>
            </div>
        </div>
        <a href="{{ route('school.academic.lessons.create', $syllabus->id) }}" class="bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-5 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
            <i class="ph-bold ph-plus"></i>
            Ajouter un chapitre / leçons
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Chapters & Lessons Cards -->
    @if($lessons->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
            @include('SchoolDashboard::components.empty-state', [
                'title' => 'Aucun chapitre trouvé',
                'description' => 'Vous n\'avez pas encore ajouté de chapitre ou de leçon pour cette matière.',
                'icon' => 'ph-fill ph-books'
            ])
        </div>
    @else
        <div class="space-y-6">
            @foreach($lessons as $lesson)
                @php
                    $subLessons = $lesson->sub_lessons;
                    $progressPercentage = $lesson->progress_percentage;
                    $status = $lesson->progress_status;
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" 
                     x-data="chapterProgressCard({
                        lessonId: {{ $lesson->id }},
                        updateUrl: '{{ route('school.academic.lessons.sub-lesson-progress', ['syllabus' => $syllabus->id, 'lesson' => $lesson->id]) }}',
                        csrfToken: '{{ csrf_token() }}',
                        initialChapterStatus: '{{ $status }}',
                        initialPercentage: {{ $progressPercentage }},
                        initialStartedAt: '{{ $lesson->started_at ? $lesson->started_at->translatedFormat('d M Y, H:i') : '' }}',
                        initialCompletedAt: '{{ $lesson->completed_at ? $lesson->completed_at->translatedFormat('d M Y, H:i') : '' }}',
                        subLessons: @js($subLessons)
                     })">
                    
                    <!-- Chapter Header Card -->
                    <div class="p-5 border-b border-slate-100 bg-[#FAFBFC] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="space-y-1.5 flex-1">
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-0.5 rounded-md bg-[#031C5B]/10 text-[#031C5B] font-bold text-[12px]">
                                    Chapitre #{{ $lesson->order }}
                                </span>
                                <h2 class="text-[17px] font-bold text-slate-800 tracking-tight">{{ $lesson->title }}</h2>
                            </div>

                            <!-- Chapter Dates & Info -->
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[12px] text-slate-500">
                                <template x-if="chapterStartedAt">
                                    <span class="inline-flex items-center gap-1 text-slate-600">
                                        <i class="ph-bold ph-calendar-blank text-blue-600"></i>
                                        <span>Débuté le : <strong class="text-slate-800" x-text="chapterStartedAt"></strong></span>
                                    </span>
                                </template>
                                <template x-if="!chapterStartedAt">
                                    <span class="inline-flex items-center gap-1 text-slate-400">
                                        <i class="ph-bold ph-calendar-blank"></i>
                                        <span>Non débuté</span>
                                    </span>
                                </template>

                                <template x-if="chapterCompletedAt">
                                    <span class="inline-flex items-center gap-1 text-emerald-700">
                                        <i class="ph-bold ph-check-circle text-emerald-600"></i>
                                        <span>Terminé le : <strong class="text-emerald-800" x-text="chapterCompletedAt"></strong></span>
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Status Badge & Progress Bar -->
                        <div class="flex flex-wrap items-center gap-4">
                            <!-- Progress Bar -->
                            <div class="w-36 space-y-1">
                                <div class="flex justify-between text-[11px] font-bold">
                                    <span class="text-slate-400">Progression</span>
                                    <span class="text-[#031C5B]" x-text="percentage + '%'"></span>
                                </div>
                                <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#031C5B] transition-all duration-500" :style="'width: ' + percentage + '%'"></div>
                                </div>
                            </div>

                            <!-- Chapter Status Badge -->
                            <div>
                                <template x-if="chapterStatus === 'completed'">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="ph-bold ph-check-circle"></i> Chapitre Terminé
                                    </span>
                                </template>
                                <template x-if="chapterStatus === 'in_progress'">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="ph-bold ph-clock"></i> En cours
                                    </span>
                                </template>
                                <template x-if="chapterStatus === 'not_started'">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        <i class="ph-bold ph-circle"></i> Non débuté
                                    </span>
                                </template>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                                <a href="{{ route('school.academic.lessons.edit', ['syllabus' => $syllabus->id, 'lesson' => $lesson->id]) }}" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition" title="Modifier le chapitre">
                                    <i class="ph-bold ph-pencil-simple text-[15px]"></i>
                                </a>
                                <form action="{{ route('school.academic.lessons.destroy', ['syllabus' => $syllabus->id, 'lesson' => $lesson->id]) }}" method="POST" onsubmit="return confirm('Supprimer ce chapitre et toutes ses leçons ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 flex items-center justify-center transition" title="Supprimer">
                                        <i class="ph-bold ph-trash text-[15px]"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Sub-lessons List with Status Controls -->
                    <div class="p-6 divide-y divide-slate-100">
                        @if(empty($subLessons))
                            <p class="text-[13px] text-slate-400 italic">Aucune leçon détaillée dans ce chapitre.</p>
                        @else
                            <div class="space-y-3">
                                <template x-for="(sub, index) in subLessons" :key="index">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between p-4 rounded-xl border transition-all duration-300 gap-4"
                                         :class="{
                                            'bg-emerald-50/40 border-emerald-200': sub.status === 'completed',
                                            'bg-amber-50/40 border-amber-200': sub.status === 'in_progress',
                                            'bg-slate-50/60 border-slate-200': sub.status === 'not_started' || !sub.status
                                         }">
                                        
                                        <!-- Lesson Title & Dates -->
                                        <div class="flex items-start gap-3.5 flex-1">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-[12px] font-bold shrink-0 mt-0.5"
                                                 :class="{
                                                    'bg-emerald-500 text-white': sub.status === 'completed',
                                                    'bg-amber-500 text-white': sub.status === 'in_progress',
                                                    'bg-slate-200 text-slate-600': sub.status === 'not_started' || !sub.status
                                                 }">
                                                <template x-if="sub.status === 'completed'">
                                                    <i class="ph-bold ph-check text-[13px]"></i>
                                                </template>
                                                <template x-if="sub.status !== 'completed'">
                                                    <span x-text="index + 1"></span>
                                                </template>
                                            </div>

                                            <div class="space-y-1">
                                                <p class="text-[14px] font-bold" 
                                                   :class="sub.status === 'completed' ? 'text-slate-800 line-through decoration-slate-400 decoration-1' : 'text-slate-800'"
                                                   x-text="sub.title"></p>
                                                
                                                <!-- Dates info for this lesson -->
                                                <div class="flex flex-wrap items-center gap-x-3 text-[11.5px] text-slate-500">
                                                    <template x-if="sub.started_at">
                                                        <span class="inline-flex items-center gap-1">
                                                            <i class="ph-bold ph-play text-blue-500"></i>
                                                            <span>Début : <span class="font-semibold text-slate-700" x-text="formatDate(sub.started_at)"></span></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="sub.completed_at">
                                                        <span class="inline-flex items-center gap-1 text-emerald-700">
                                                            <i class="ph-bold ph-check text-emerald-600"></i>
                                                            <span>Fin : <span class="font-semibold text-emerald-800" x-text="formatDate(sub.completed_at)"></span></span>
                                                        </span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Interactive Status Buttons for Teacher -->
                                        <div class="flex items-center gap-1.5 shrink-0 bg-white border border-slate-200 rounded-lg p-1 shadow-sm">
                                            <!-- Non débuté -->
                                            <button type="button" 
                                                    @click="changeStatus(index, 'not_started')"
                                                    :disabled="isUpdating"
                                                    class="px-3 py-1.5 rounded-md text-[12px] font-bold transition flex items-center gap-1.5"
                                                    :class="sub.status === 'not_started' || !sub.status ? 'bg-slate-600 text-white shadow-xs' : 'text-slate-500 hover:bg-slate-100'">
                                                <i class="ph-bold ph-circle text-[10px]"></i>
                                                <span>À faire</span>
                                            </button>

                                            <!-- En cours -->
                                            <button type="button" 
                                                    @click="changeStatus(index, 'in_progress')"
                                                    :disabled="isUpdating"
                                                    class="px-3 py-1.5 rounded-md text-[12px] font-bold transition flex items-center gap-1.5"
                                                    :class="sub.status === 'in_progress' ? 'bg-amber-500 text-white shadow-xs' : 'text-amber-700 hover:bg-amber-50'">
                                                <i class="ph-bold ph-clock text-[12px]"></i>
                                                <span>En cours</span>
                                            </button>

                                            <!-- Terminé -->
                                            <button type="button" 
                                                    @click="changeStatus(index, 'completed')"
                                                    :disabled="isUpdating"
                                                    class="px-3 py-1.5 rounded-md text-[12px] font-bold transition flex items-center gap-1.5"
                                                    :class="sub.status === 'completed' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-700 hover:bg-emerald-50'">
                                                <i class="ph-bold ph-check text-[12px]"></i>
                                                <span>Terminé</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        @endif

                        <!-- Lesson Resources / Attachments -->
                        @if($lesson->file_path || $lesson->video_url)
                            <div class="pt-4 flex items-center gap-3">
                                <span class="text-[12px] font-bold text-slate-500">Ressources :</span>
                                @if($lesson->file_path)
                                    <a href="{{ asset('storage/' . $lesson->file_path) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 font-bold text-[12px] rounded-lg border border-red-200 transition">
                                        <i class="ph-fill ph-file-pdf"></i> Document attaché
                                    </a>
                                @endif
                                @if($lesson->video_url)
                                    <a href="{{ $lesson->video_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[12px] rounded-lg border border-blue-200 transition">
                                        <i class="ph-fill ph-video"></i> Vidéo du cours
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

<script>
function chapterProgressCard(config) {
    return {
        lessonId: config.lessonId,
        updateUrl: config.updateUrl,
        csrfToken: config.csrfToken,
        chapterStatus: config.initialChapterStatus,
        percentage: config.initialPercentage,
        chapterStartedAt: config.initialStartedAt,
        chapterCompletedAt: config.initialCompletedAt,
        subLessons: config.subLessons,
        isUpdating: false,

        formatDate(dateString) {
            if (!dateString) return '';
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return dateString;
                return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            } catch (e) {
                return dateString;
            }
        },

        async changeStatus(index, newStatus) {
            if (this.isUpdating) return;
            this.isUpdating = true;

            try {
                const response = await fetch(this.updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({
                        index: index,
                        status: newStatus
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        // Update the reactive sub-lesson item
                        this.subLessons[index] = data.sub_lesson;
                        this.chapterStatus = data.chapter_status;
                        this.chapterStartedAt = data.chapter_started_at;
                        this.chapterCompletedAt = data.chapter_completed_at;
                        this.percentage = data.progress_percentage;
                    }
                } else {
                    alert('Erreur lors de la mise à jour du statut.');
                }
            } catch (err) {
                console.error(err);
                alert('Erreur de communication avec le serveur.');
            } finally {
                this.isUpdating = false;
            }
        }
    };
}
</script>
@endsection
