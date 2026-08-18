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
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Leçons : {{ $syllabus->subject->name }}</h1>
                <p class="text-[13.5px] text-slate-500 mt-1">
                    Classe : <span class="font-bold text-slate-700">{{ $syllabus->academicClass->name }}</span> | 
                    Semestre : <span class="font-bold text-slate-700">{{ $syllabus->semester ? $syllabus->semester->name : 'Non assigné' }}</span>
                </p>
            </div>
        </div>
        <a href="{{ route('school.academic.lessons.create', $syllabus->id) }}" class="bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] px-5 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
            <i class="ph-bold ph-plus"></i>
            Ajouter une leçon
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Lessons List -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" x-data="{ isEmpty: {{ $lessons->isEmpty() ? 'true' : 'false' }} }">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill ph-list-numbers text-primary-dynamic"></i>
                Liste des leçons / chapitres
            </h2>
        </div>
        
        <!-- Empty State -->
        <div class="p-8" x-show="isEmpty">
            @include('SchoolDashboard::components.empty-state', [
                'title' => 'Aucun chapitre trouvé',
                'description' => 'Vous n\'avez pas encore ajouté de chapitre ou de leçon pour cette matière.',
                'icon' => 'ph-fill ph-books'
            ])
        </div>

        <div class="overflow-x-auto" x-show="!isEmpty" style="display: none;">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 w-16 whitespace-nowrap">Ordre</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Chapitre & Leçons</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 whitespace-nowrap">Statut</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 whitespace-nowrap">Ressources</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($lessons as $lesson)
                    <tr class="hover:bg-slate-50/50 transition group align-top">
                        <td class="px-5 py-4 text-[14px] font-bold text-slate-700">#{{ $lesson->order }}</td>
                        <td class="px-5 py-4">
                            <div class="text-[14px] font-bold text-slate-800 mb-2">{{ $lesson->title }}</div>
                            
                            @if($lesson->lesson_titles && is_array($lesson->lesson_titles) && count($lesson->lesson_titles) > 0)
                                <ul class="space-y-1.5 mt-2">
                                    @foreach($lesson->lesson_titles as $subLesson)
                                        @if(!empty(trim($subLesson)))
                                            <li class="flex items-start gap-2 text-[13px] text-slate-600">
                                                <i class="ph-fill ph-caret-right text-[#2F5F76] mt-0.5 text-[10px]"></i>
                                                {{ $subLesson }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($lesson->status == 'published')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-green-50 text-green-700 border border-green-100">Publié</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">Brouillon</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 flex items-center gap-2">
                            @if($lesson->file_path)
                                <a href="{{ asset('storage/' . $lesson->file_path) }}" target="_blank" class="w-7 h-7 rounded bg-red-50 text-red-600 flex items-center justify-center" title="Document attaché">
                                    <i class="ph-fill ph-file-pdf"></i>
                                </a>
                            @endif
                            @if($lesson->video_url)
                                <a href="{{ $lesson->video_url }}" target="_blank" class="w-7 h-7 rounded bg-blue-50 text-blue-600 flex items-center justify-center" title="Lien vidéo">
                                    <i class="ph-fill ph-video"></i>
                                </a>
                            @endif
                            @if(!$lesson->file_path && !$lesson->video_url)
                                <span class="text-[12px] text-slate-400 font-medium">Texte uniquement</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('school.academic.lessons.edit', ['syllabus' => $syllabus->id, 'lesson' => $lesson->id]) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition inline-flex items-center justify-center" title="Éditer">
                                    <i class="ph ph-pencil-simple text-[16px]"></i>
                                </a>
                                <form action="{{ route('school.academic.lessons.destroy', ['syllabus' => $syllabus->id, 'lesson' => $lesson->id]) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette leçon ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition inline-flex items-center justify-center" title="Supprimer">
                                        <i class="ph ph-trash text-[16px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
    </div>
</div>
@endsection
