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
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Ajouter des leçons</h1>
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

    <form action="{{ route('school.academic.lessons.store', $syllabus->id) }}" method="POST" enctype="multipart/form-data"
          x-data="{ lessons: [{ id: Date.now(), order: 1, lesson_titles: [''] }] }">
        @csrf

        <div class="space-y-6">
            @if($otherClassSyllabuses->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" x-data="{ allChecked: false }">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                            <i class="ph-fill ph-copy text-primary-dynamic"></i>
                            Dupliquer aussi pour d'autres classes
                        </h2>
                        <p class="text-[12px] text-slate-500 mt-0.5">Mêmes chapitres, même matière ({{ $syllabus->subject->name }}), même semestre ({{ $syllabus->semester->name }}) — pour les classes cochées ci-dessous.</p>
                    </div>
                    <button type="button"
                        @click="allChecked = !allChecked; $el.closest('div[x-data]').querySelectorAll('input[name=\'target_syllabus_ids[]\']').forEach(cb => cb.checked = allChecked)"
                        class="text-[12px] font-bold text-[#2F5F76] hover:underline shrink-0">
                        <span x-text="allChecked ? 'Tout décocher' : 'Tout sélectionner'"></span>
                    </button>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($otherClassSyllabuses as $otherSyllabus)
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-[#2F5F76] hover:bg-slate-50 transition cursor-pointer">
                        <input type="checkbox" name="target_syllabus_ids[]" value="{{ $otherSyllabus->id }}" class="w-4 h-4 text-[#2F5F76] bg-slate-100 border-slate-300 rounded focus:ring-[#2F5F76]">
                        <span class="text-[13px] font-bold text-slate-700">{{ $otherSyllabus->academicClass->name ?? '-' }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Dynamic Chapters loop -->
            <template x-for="(lesson, index) in lessons" :key="lesson.id">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative">
                    
                    <!-- Remove button -->
                    <button type="button" @click="lessons.length > 1 ? lessons.splice(index, 1) : null" 
                            x-show="lessons.length > 1"
                            class="absolute top-4 right-4 text-slate-400 hover:text-red-500 transition" title="Supprimer ce bloc">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                    
                    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                            <i class="ph-fill ph-book-open text-primary-dynamic"></i>
                            Détails du Chapitre <span x-text="index + 1"></span>
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Titre -->
                            <div class="md:col-span-2">
                                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Nom du Chapitre <span class="text-red-500">*</span></label>
                                <input type="text" :name="'lessons[' + index + '][title]'" required
                                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                    placeholder="Ex: Chapitre 1 - Introduction aux équations">
                            </div>

                            <!-- Leçons -->
                            <div class="md:col-span-2 bg-slate-50 rounded-lg p-4 border border-slate-200">
                                <label class="block text-[13px] font-semibold text-slate-700 mb-3">Leçons contenues dans ce chapitre <span class="text-red-500">*</span></label>
                                <div class="space-y-3">
                                    <template x-for="(title, tIndex) in lesson.lesson_titles" :key="tIndex">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 text-[12px] font-bold" x-text="tIndex + 1"></div>
                                            <input type="text" :name="'lessons[' + index + '][lesson_titles][]'" x-model="lesson.lesson_titles[tIndex]" required
                                                class="flex-1 bg-white border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                                placeholder="Titre de la leçon (Ex: 1.1 Définitions)">
                                            
                                            <button type="button" @click="lesson.lesson_titles.length > 1 ? lesson.lesson_titles.splice(tIndex, 1) : null" 
                                                    x-show="lesson.lesson_titles.length > 1"
                                                    class="w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 flex items-center justify-center transition" title="Retirer">
                                                <i class="ph-bold ph-minus"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" @click="lesson.lesson_titles.push('')" 
                                        class="mt-4 text-[13px] font-bold text-[#2F5F76] hover:text-[#1E4357] flex items-center gap-1.5">
                                    <i class="ph-bold ph-plus-circle"></i> Ajouter une leçon
                                </button>
                            </div>

                            <!-- Ordre & Statut -->
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Ordre du chapitre <span class="text-red-500">*</span></label>
                                <input type="number" :name="'lessons[' + index + '][order]'" required min="1" x-model="lesson.order"
                                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                    placeholder="Ex: 1">
                            </div>

                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Statut <span class="text-red-500">*</span></label>
                                <select :name="'lessons[' + index + '][status]'" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                                    <option value="published">Publié (Visible aux étudiants)</option>
                                    <option value="draft">Brouillon (Non visible)</option>
                                </select>
                            </div>
                            
                            <!-- Document attaché -->
                            <div class="md:col-span-2">
                                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Document attaché (Optionnel)</label>
                                <input type="file" :name="'lessons[' + index + '][file]'" accept=".pdf,.doc,.docx,.ppt,.pptx"
                                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2 outline-none focus:border-[#2F5F76] transition shadow-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="text-[12px] text-slate-500 mt-1">Formats acceptés : PDF, Word, PowerPoint. Max 5MB.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            
            <!-- Button to add more lessons -->
            <button type="button" @click="lessons.push({ id: Date.now(), order: lessons.length > 0 ? parseInt(lessons[lessons.length - 1].order) + 1 : 1, lesson_titles: [''] })" 
                    class="w-full py-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 text-slate-600 font-bold hover:bg-slate-100 hover:text-slate-900 transition flex items-center justify-center gap-2">
                <i class="ph-bold ph-plus-circle text-lg"></i>
                Ajouter un autre chapitre
            </button>

            <!-- Submit form -->
            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] px-8 py-3 rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                    <i class="ph-bold ph-floppy-disk"></i>
                    Enregistrer toutes les leçons
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
