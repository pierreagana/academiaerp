@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('school.academic.lessons.index', $syllabus->id) }}" class="w-10 h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition shadow-sm">
                <i class="ph-bold ph-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Éditer la leçon : {{ $lesson->title }}</h1>
                <p class="text-[13.5px] text-slate-500 mt-1">
                    {{ $syllabus->subject->name }} | {{ $syllabus->academicClass->name }}
                </p>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <div class="flex items-center gap-2 mb-2">
            <i class="ph-fill ph-warning-circle text-lg"></i>
            <span class="font-bold">Veuillez corriger les erreurs suivantes :</span>
        </div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill ph-pencil-simple text-primary-dynamic"></i>
                Détails de la leçon
            </h2>
        </div>
        
        <form action="{{ route('school.academic.lessons.update', ['syllabus' => $syllabus->id, 'lesson' => $lesson->id]) }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Titre -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Nom du Chapitre <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" required value="{{ old('title', $lesson->title) }}"
                        class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm">
                </div>

                <!-- Leçons -->
                @php
                    $rawTitles = array_map(fn($item) => is_array($item) ? ($item['title'] ?? '') : (is_string($item) ? $item : ''), $lesson->sub_lessons);
                    if (empty($rawTitles)) {
                        $rawTitles = [''];
                    }
                @endphp
                <div class="md:col-span-2 bg-slate-50 rounded-lg p-4 border border-slate-200" x-data="{ lesson_titles: {{ json_encode(old('lesson_titles', $rawTitles)) }} }">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-3">Leçons contenues dans ce chapitre <span class="text-red-500">*</span></label>
                    <div class="space-y-3">
                        <template x-for="(title, tIndex) in lesson_titles" :key="tIndex">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 text-[12px] font-bold" x-text="tIndex + 1"></div>
                                <input type="text" name="lesson_titles[]" x-model="lesson_titles[tIndex]" required
                                    class="flex-1 bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                    placeholder="Titre de la leçon (Ex: 1.1 Définitions)">
                                
                                <button type="button" @click="lesson_titles.length > 1 ? lesson_titles.splice(tIndex, 1) : null" 
                                        x-show="lesson_titles.length > 1"
                                        class="w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 flex items-center justify-center transition" title="Retirer">
                                    <i class="ph-bold ph-minus"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="lesson_titles.push('')" 
                            class="mt-4 text-[13px] font-bold text-[#2F5F76] hover:text-[#1E4357] flex items-center gap-1.5">
                        <i class="ph-bold ph-plus-circle"></i> Ajouter une leçon
                    </button>
                </div>

                <!-- Ordre & Statut -->
                <div>
                    <label for="order" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Ordre du chapitre <span class="text-red-500">*</span></label>
                    <input type="number" id="order" name="order" required min="1" value="{{ old('order', $lesson->order) }}"
                        class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm">
                </div>

                <div>
                    <label for="status" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Statut <span class="text-red-500">*</span></label>
                    <select id="status" name="status" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                        <option value="published" {{ old('status', $lesson->status) == 'published' ? 'selected' : '' }}>Publié (Visible aux étudiants)</option>
                        <option value="draft" {{ old('status', $lesson->status) == 'draft' ? 'selected' : '' }}>Brouillon (Non visible)</option>
                    </select>
                </div>
                
                <!-- Document attaché -->
                <div class="md:col-span-2">
                    <label for="file" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Document attaché (Optionnel)</label>
                    
                    @if($lesson->file_path)
                    <div class="mb-2 p-3 bg-blue-50 border border-blue-100 rounded-lg flex items-center justify-between">
                        <div class="flex items-center gap-2 text-blue-700 text-[13px] font-medium">
                            <i class="ph-fill ph-file-text text-lg"></i>
                            Document actuel : {{ basename($lesson->file_path) }}
                        </div>
                        <a href="{{ asset('storage/' . $lesson->file_path) }}" target="_blank" class="text-blue-600 hover:underline text-[12px] font-bold">Voir</a>
                    </div>
                    @endif
                    
                    <input type="file" id="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx"
                        class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2 outline-none focus:border-[#2F5F76] transition shadow-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-[12px] text-slate-500 mt-1">Laissez vide si vous ne souhaitez pas modifier le document actuel. Formats acceptés : PDF, Word, PowerPoint. Max 5MB.</p>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] px-8 py-3 rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                    <i class="ph-bold ph-floppy-disk"></i>
                    Mettre à jour le chapitre
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
