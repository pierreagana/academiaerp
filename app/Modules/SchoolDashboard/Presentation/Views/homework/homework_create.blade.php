@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('school.academic.homework.homework') }}" class="text-[12.5px] font-bold text-slate-500 hover:text-[#031C5B] inline-flex items-center gap-1 mb-2">
            <i class="ph-bold ph-arrow-left"></i> Devoirs Maison
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Nouveau Devoir</h1>
        <p class="text-[13.5px] text-slate-500 mt-1">Créez un devoir maison pour une de vos classes.</p>
    </div>

    @if($errors->any())
    <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form action="{{ route('school.academic.homework.homework.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Classe <span class="text-red-500">*</span></label>
                    <select name="academic_class_id" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                        <option value="">Sélectionner une classe</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('academic_class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Matière <span class="text-red-500">*</span></label>
                    <select name="subject_id" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                        <option value="">Sélectionner une matière</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Titre <span class="text-red-500">*</span></label>
                <input type="text" name="title" required value="{{ old('title') }}" placeholder="Ex: Théorème de Thalès - Exercices d'application" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
            </div>

            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Description</label>
                <textarea name="description" rows="4" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Date limite <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="scheduled_at" required value="{{ old('scheduled_at') }}" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Noté sur <span class="text-red-500">*</span></label>
                    <input type="number" name="max_score" min="1" max="1000" step="0.5" required value="{{ old('max_score', 20) }}" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
            </div>
            <p class="text-[11.5px] text-slate-400 -mt-3">Le barème utilisé pour ce devoir. La note sera automatiquement convertie sur /20 dans le Bulletin.</p>

            <div class="flex justify-end pt-2">
                <button type="submit" class="bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-8 py-3 rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="ph-bold ph-check-circle"></i> Créer le devoir
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
