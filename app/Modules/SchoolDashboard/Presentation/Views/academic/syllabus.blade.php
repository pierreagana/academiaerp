@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Programme de cours (Syllabus)</h1>
            <p class="text-[13.5px] text-slate-500 mt-1">Consultez les matières assignées à chaque classe et semestre.</p>
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

    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden p-5">
        <form action="{{ route('school.academic.syllabuses') }}" method="GET" class="flex flex-col md:flex-row md:items-end gap-4">
            
            <div class="flex-1">
                <label for="class_id" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Classe</label>
                <select id="class_id" name="class_id" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                    <option value="">Toutes les classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ (isset($classId) && $classId == $class->id) ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1">
                <label for="semester_id" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Semestre</label>
                <select id="semester_id" name="semester_id" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                    <option value="">Tous les semestres</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" {{ (isset($semesterId) && $semesterId == $semester->id) ? 'selected' : '' }}>{{ $semester->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold text-[13px] px-6 py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-2 h-[42px]">
                    <i class="ph-bold ph-funnel"></i>
                    Filtrer
                </button>
            </div>
            
            @if(isset($classId) || isset($semesterId))
            <div>
                <a href="{{ route('school.academic.syllabuses') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[13px] px-4 py-2.5 rounded-lg shadow-sm transition flex items-center justify-center h-[42px]" title="Réinitialiser les filtres">
                    <i class="ph-bold ph-x"></i>
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Syllabus List -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" x-data="{ isEmpty: {{ $syllabuses->isEmpty() ? 'true' : 'false' }} }">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill ph-book-open-text text-primary-dynamic"></i>
                Détail du programme
            </h2>
        </div>
        
        <!-- Empty State -->
        <div class="p-8" x-show="isEmpty">
            @include('SchoolDashboard::components.empty-state', [
                'title' => 'Aucune donnée trouvée',
                'description' => 'Aucune matière n\'a encore été assignée pour ces critères.',
                'icon' => 'ph-fill ph-books'
            ])
        </div>

        <div class="overflow-x-auto" x-show="!isEmpty" style="display: none;">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Classe</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Semestre</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Matière</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Code</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Coef.</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($syllabuses as $syllabus)
                    <tr class="hover:bg-slate-50/50 transition group">
                        <td class="px-5 py-4 text-[14px] font-bold text-slate-700">{{ $syllabus->academicClass ? $syllabus->academicClass->name : '-' }}</td>
                        <td class="px-5 py-4 text-[13px] font-medium text-slate-600">{{ $syllabus->semester ? $syllabus->semester->name : 'Non assigné' }}</td>
                        <td class="px-5 py-4 text-[14px] font-bold text-slate-700 flex items-center gap-2">
                            @if($syllabus->subject && $syllabus->subject->color)
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $syllabus->subject->color }}"></div>
                            @endif
                            {{ $syllabus->subject ? $syllabus->subject->name : '-' }}
                        </td>
                        <td class="px-5 py-4 text-[13px] font-bold text-[#2F5F76] uppercase">{{ $syllabus->subject ? $syllabus->subject->code : '-' }}</td>
                        <td class="px-5 py-4 text-[13px] font-bold text-slate-700">{{ $syllabus->subject ? $syllabus->subject->coefficient : '-' }}</td>
                        <td class="px-5 py-4 text-right flex justify-end gap-2">
                            <a href="{{ route('school.academic.lessons.index', $syllabus->id) }}" class="h-8 px-3 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition inline-flex items-center justify-center gap-1.5" title="Gérer les leçons">
                                <i class="ph-bold ph-list-dashes text-[14px]"></i>
                                <span class="text-[12px] font-bold">Leçons</span>
                            </a>
                            <form action="{{ route('school.academic.syllabuses.destroy', $syllabus->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir retirer cette matière du programme ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition inline-flex items-center justify-center" title="Retirer du programme">
                                    <i class="ph ph-trash text-[16px]"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between" x-show="!isEmpty" style="display: none;">
            <span class="text-[13px] font-medium text-slate-500">Affichage de {{ $syllabuses->count() }} ligne(s)</span>
        </div>
    </div>
</div>
@endsection
