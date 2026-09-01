@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Assigner des matières</h1>
            <p class="text-[13.5px] text-slate-500 mt-1">Créez le programme de cours en associant des matières à une classe et un semestre.</p>
        </div>
        <a href="{{ route('school.academic.syllabuses') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-[13px] px-5 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
            <i class="ph-bold ph-arrow-left"></i>
            Retour à la liste
        </a>
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
                <i class="ph-fill ph-link text-primary-dynamic"></i>
                Détails de l'assignation
            </h2>
        </div>
        
        <form action="{{ route('school.academic.syllabuses.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Semestre -->
                <div>
                    <label for="semester_id" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Semestre <span class="text-red-500">*</span></label>
                    <select id="semester_id" name="semester_id" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                        <option value="">Sélectionner un semestre</option>
                        @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" {{ old('semester_id', request('semester_id')) == $semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Classes (Checkboxes) -->
            <div class="mb-8" x-data="{ allChecked: {{ (is_array(old('academic_class_ids')) && count(old('academic_class_ids')) === $classes->count() && $classes->isNotEmpty()) ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
                    <label class="text-[14px] font-bold text-slate-800">Sélectionnez les classes <span class="text-red-500">*</span></label>
                    @if($classes->isNotEmpty())
                    <button type="button"
                        @click="allChecked = !allChecked; $el.closest('div[x-data]').querySelectorAll('input[name=\'academic_class_ids[]\']').forEach(cb => cb.checked = allChecked)"
                        class="text-[12px] font-bold text-[#2F5F76] hover:underline shrink-0" x-text="allChecked ? 'Tout décocher' : 'Tout sélectionner'">
                    </button>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @php $selectedClassIds = old('academic_class_ids', request('class_id') ? [request('class_id')] : []); @endphp
                    @foreach($classes as $class)
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 hover:border-[#2F5F76] hover:bg-slate-50 transition cursor-pointer group">
                        <div class="pt-0.5">
                            <input type="checkbox" name="academic_class_ids[]" value="{{ $class->id }}" class="w-4 h-4 text-[#2F5F76] bg-slate-100 border-slate-300 rounded focus:ring-[#2F5F76]" {{ in_array($class->id, $selectedClassIds) ? 'checked' : '' }}>
                        </div>
                        <div class="flex-1">
                            <div class="text-[13px] font-bold text-slate-800 group-hover:text-[#2F5F76] transition">{{ $class->name }}</div>
                            @if($class->cycle)
                            <div class="text-[11.5px] font-medium text-slate-500 mt-0.5">{{ $class->cycle }}</div>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>

                @if($classes->isEmpty())
                <div class="text-center p-6 border border-dashed border-slate-300 rounded-xl bg-slate-50 mt-2">
                    <i class="ph-bold ph-warning-circle text-slate-400 text-3xl mb-2"></i>
                    <p class="text-[13px] text-slate-600 font-medium">Aucune classe n'est disponible.</p>
                    <a href="{{ route('school.academic.classes') }}" class="text-[#2F5F76] text-[12px] font-bold hover:underline mt-1 inline-block">Aller créer des classes</a>
                </div>
                @endif
            </div>

            <!-- Matières (Checkboxes) -->
            <div x-data="{ allChecked: {{ (is_array(old('subjects')) && count(old('subjects')) === $subjects->count() && $subjects->isNotEmpty()) ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
                    <label class="text-[14px] font-bold text-slate-800">Sélectionnez les matières à assigner <span class="text-red-500">*</span></label>
                    @if($subjects->isNotEmpty())
                    <button type="button"
                        @click="allChecked = !allChecked; $el.closest('div[x-data]').querySelectorAll('input[name=\'subjects[]\']').forEach(cb => cb.checked = allChecked)"
                        class="text-[12px] font-bold text-[#2F5F76] hover:underline shrink-0" x-text="allChecked ? 'Tout décocher' : 'Tout cocher'">
                    </button>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($subjects as $subject)
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 hover:border-[#2F5F76] hover:bg-slate-50 transition cursor-pointer group">
                        <div class="pt-0.5">
                            <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" class="w-4 h-4 text-[#2F5F76] bg-slate-100 border-slate-300 rounded focus:ring-[#2F5F76]" {{ is_array(old('subjects')) && in_array($subject->id, old('subjects')) ? 'checked' : '' }}>
                        </div>
                        <div class="flex-1">
                            <div class="text-[13px] font-bold text-slate-800 group-hover:text-[#2F5F76] transition flex items-center gap-2">
                                @if($subject->color)
                                <div class="w-2 h-2 rounded-full" style="background-color: {{ $subject->color }}"></div>
                                @endif
                                {{ $subject->name }}
                            </div>
                            <div class="text-[11.5px] font-medium text-slate-500 mt-0.5">Code: {{ $subject->code }} | Coef: {{ $subject->coefficient }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
                
                @if($subjects->isEmpty())
                <div class="text-center p-6 border border-dashed border-slate-300 rounded-xl bg-slate-50 mt-2">
                    <i class="ph-bold ph-warning-circle text-slate-400 text-3xl mb-2"></i>
                    <p class="text-[13px] text-slate-600 font-medium">Aucune matière n'est disponible dans la base de données.</p>
                    <a href="{{ route('school.academic.subjects') }}" class="text-[#2F5F76] text-[12px] font-bold hover:underline mt-1 inline-block">Aller créer des matières</a>
                </div>
                @endif
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] px-8 py-3 rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                    <i class="ph-bold ph-floppy-disk"></i>
                    Enregistrer le programme
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
