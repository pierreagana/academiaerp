@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ openClassId: null, activeSemesterId: {{ $semesters->first()->id ?? 'null' }}, search: '' }">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Programme de cours (Syllabus)</h1>
            <p class="text-[13.5px] text-slate-500 mt-1">Choisissez une classe pour consulter ses matières par trimestre et gérer les leçons.</p>
        </div>
        <a href="{{ route('school.academic.syllabuses.create') }}" class="bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] px-5 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
            <i class="ph-bold ph-plus"></i>
            Assigner des matières
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if($classes->isEmpty())
        @include('SchoolDashboard::components.empty-state', [
            'title' => 'Aucune classe trouvée',
            'description' => 'Créez d\'abord des classes pour pouvoir leur assigner des matières et un programme.',
            'icon' => 'ph-fill ph-chalkboard-teacher'
        ])
    @else
        <!-- Search -->
        <div class="relative max-w-sm">
            <i class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" x-model="search" placeholder="Rechercher une classe..."
                class="w-full bg-white border border-slate-200 text-slate-900 text-[13.5px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm">
        </div>

        <!-- Classes as cards, 3 per row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($classes as $class)
                @php
                    $classSyllabuses = $syllabuses->where('academic_class_id', $class->id);
                    $subjectCount = $classSyllabuses->pluck('subject_id')->unique()->count();
                @endphp
                <button type="button" @click="openClassId = {{ $class->id }}"
                    x-show="search === '' || {{ \Illuminate\Support\Js::from(strtolower($class->name)) }}.includes(search.toLowerCase())"
                    class="text-left bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-[#2F5F76] hover:shadow-md transition p-5 flex flex-col gap-3 cursor-pointer">
                    <div class="flex items-start justify-between">
                        <span class="w-11 h-11 rounded-xl bg-blue-50 text-[#2F5F76] flex items-center justify-center shrink-0">
                            <i class="ph-fill ph-chalkboard-teacher text-xl"></i>
                        </span>
                        @if($class->cycle)
                        <span class="text-[10.5px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500">{{ $class->cycle }}</span>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-[16px] font-extrabold text-slate-900">{{ $class->name }}</h3>
                        <p class="text-[12px] text-slate-500 mt-0.5">{{ $class->students_count }} élève{{ $class->students_count > 1 ? 's' : '' }}</p>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100 text-[12.5px] font-bold text-slate-600">
                        <i class="ph-bold ph-books text-[#2F5F76]"></i>
                        {{ $subjectCount }} matière{{ $subjectCount > 1 ? 's' : '' }} assignée{{ $subjectCount > 1 ? 's' : '' }}
                    </div>
                </button>
            @endforeach
        </div>
    @endif

    <!-- ================= CLASS DETAIL MODAL ================= -->
    @foreach($classes as $class)
    @php
        $classSyllabuses = $syllabuses->where('academic_class_id', $class->id);
    @endphp
    <div x-show="openClassId === {{ $class->id }}" style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        @click.self="openClassId = null" @keydown.escape.window="openClassId = null">
        <div class="relative w-full max-w-4xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden max-h-[90vh] flex flex-col">
            <div class="px-8 py-6 bg-primary-dynamic text-white flex items-center justify-between shrink-0">
                <div>
                    <h3 class="text-lg font-bold">{{ $class->name }}</h3>
                    <p class="text-xs text-blue-100 mt-0.5">{{ $class->students_count }} élève{{ $class->students_count > 1 ? 's' : '' }} @if($class->cycle) &middot; {{ $class->cycle }} @endif</p>
                </div>
                <button @click="openClassId = null" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            @if($semesters->isEmpty())
            <div class="p-8">
                @include('SchoolDashboard::components.empty-state', [
                    'title' => 'Aucun semestre configuré',
                    'description' => 'Créez d\'abord vos trimestres pour organiser le programme par période.',
                    'icon' => 'ph-fill ph-calendar-x'
                ])
                <div class="text-center mt-4">
                    <a href="{{ route('school.academic.semesters') }}" class="text-[#2F5F76] text-[13px] font-bold hover:underline">Configurer les semestres</a>
                </div>
            </div>
            @else
            <!-- Semester tabs -->
            <div class="flex border-b border-slate-200 px-8 overflow-x-auto shrink-0">
                @foreach($semesters as $semester)
                <button type="button" @click="activeSemesterId = {{ $semester->id }}"
                    :class="{'border-[#031C5B] text-[#031C5B]': activeSemesterId === {{ $semester->id }}, 'border-transparent text-slate-500 hover:text-slate-700': activeSemesterId !== {{ $semester->id }}}"
                    class="whitespace-nowrap py-3.5 px-4 border-b-2 font-bold text-[13px] transition">
                    {{ $semester->name }}
                </button>
                @endforeach
            </div>

            <div class="p-6 overflow-y-auto flex-1">
                @foreach($semesters as $semester)
                @php $semesterSyllabuses = $classSyllabuses->where('semester_id', $semester->id)->values(); @endphp
                <div x-show="activeSemesterId === {{ $semester->id }}" {!! $loop->first ? '' : 'style="display: none;"' !!} class="space-y-3">
                    @forelse($semesterSyllabuses as $syllabus)
                    <div class="flex items-center justify-between gap-4 p-4 rounded-xl border border-slate-200 bg-slate-50/50">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($syllabus->subject && $syllabus->subject->color)
                            <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $syllabus->subject->color }}"></span>
                            @endif
                            <div class="min-w-0">
                                <p class="text-[14px] font-bold text-slate-800 truncate">{{ $syllabus->subject->name ?? '-' }}</p>
                                <p class="text-[11.5px] text-slate-500">Code: {{ $syllabus->subject->code ?? '-' }} &middot; Coef: {{ $syllabus->subject->coefficient ?? '-' }} &middot; {{ $syllabus->lessons_count }} leçon{{ $syllabus->lessons_count > 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('school.academic.lessons.create', $syllabus->id) }}" class="h-8 px-3 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition inline-flex items-center gap-1.5" title="Ajouter une leçon">
                                <i class="ph-bold ph-plus text-[13px]"></i>
                                <span class="text-[12px] font-bold">Leçon</span>
                            </a>
                            <a href="{{ route('school.academic.lessons.index', $syllabus->id) }}" class="h-8 px-3 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition inline-flex items-center gap-1.5" title="Gérer les leçons">
                                <i class="ph-bold ph-list-dashes text-[13px]"></i>
                                <span class="text-[12px] font-bold">Gérer</span>
                            </a>
                            <form action="{{ route('school.academic.syllabuses.destroy', $syllabus->id) }}" method="POST" onsubmit="return confirm('Retirer cette matière du programme de {{ $class->name }} ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition inline-flex items-center justify-center" title="Retirer du programme">
                                    <i class="ph ph-trash text-[15px]"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <i class="ph-fill ph-books text-3xl text-slate-300"></i>
                        <p class="text-[13px] text-slate-500 mt-2">Aucune matière assignée pour {{ $semester->name }}.</p>
                        <a href="{{ route('school.academic.syllabuses.create') }}?class_id={{ $class->id }}&semester_id={{ $semester->id }}" class="text-[#2F5F76] text-[12.5px] font-bold hover:underline mt-1 inline-block">Assigner des matières</a>
                    </div>
                    @endforelse
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endforeach

</div>
@endsection
