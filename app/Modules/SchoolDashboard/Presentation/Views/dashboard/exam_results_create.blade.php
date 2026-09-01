@extends('SchoolDashboard::layouts.app')

@section('title', 'Résultats — ' . $label)

@section('content')
<div class="max-w-4xl mx-auto space-y-6"
    x-data="{
        classes: {{ \Illuminate\Support\Js::from($classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'count' => $c->students_count])) }},
        students: {{ \Illuminate\Support\Js::from($students->map(fn($s) => ['id' => $s->id, 'name' => trim($s->first_name . ' ' . $s->last_name), 'roll' => $s->roll_number, 'classId' => $s->academic_class_id])) }},
        selectedClasses: {{ \Illuminate\Support\Js::from($selectedClassIds) }},
        admitted: {{ \Illuminate\Support\Js::from($admittedStudentIds) }},
        get visibleStudents() {
            return this.students.filter(s => this.selectedClasses.includes(s.classId));
        },
        get presentedCount() {
            return this.visibleStudents.length;
        },
        get admittedCount() {
            const visibleIds = this.visibleStudents.map(s => s.id);
            return this.admitted.filter(id => visibleIds.includes(id)).length;
        },
        markAllAdmitted() {
            this.admitted = this.visibleStudents.map(s => s.id);
        },
        markNoneAdmitted() {
            const visibleIds = this.visibleStudents.map(s => s.id);
            this.admitted = this.admitted.filter(id => !visibleIds.includes(id));
        },
    }"
>
    <div class="flex items-center gap-3">
        <a href="{{ route('school.exam-results.index') }}" class="text-slate-400 hover:text-slate-700">
            <i class="ph ph-arrow-left text-xl"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800">{{ $isEditing ? 'Modifier les résultats' : 'Nouvelle session' }} — {{ $label }}</h2>
            <p class="text-sm text-slate-500">Année scolaire {{ $year }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('school.exam-results.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="exam_type" value="{{ $type }}">

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h3 class="text-sm font-bold text-slate-700 mb-1">1. Classes concernées</h3>
            <p class="text-xs text-slate-500 mb-4">Sélectionnez la ou les classes qui ont passé cet examen.</p>

            @if($classes->isEmpty())
                <p class="text-sm text-slate-400 italic">Aucune classe disponible.</p>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($classes as $class)
                        <label class="flex items-center justify-between gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer has-[:checked]:bg-blue-50 has-[:checked]:border-blue-400">
                            <span class="flex items-center gap-2">
                                <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" x-model.number="selectedClasses" class="rounded text-blue-600">
                                <span class="text-sm font-medium text-slate-700">{{ $class->name }}</span>
                            </span>
                            <span class="text-xs text-slate-400">{{ $class->students_count }} élève(s)</span>
                        </label>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 text-sm font-semibold text-slate-700">
                Total élèves concernés : <span x-text="presentedCount"></span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200" x-show="presentedCount > 0" x-cloak>
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-bold text-slate-700">2. Élèves admis</h3>
                <div class="flex gap-3">
                    <button type="button" @click="markAllAdmitted()" class="text-xs font-bold text-blue-700 hover:underline">Tout cocher</button>
                    <button type="button" @click="markNoneAdmitted()" class="text-xs font-bold text-slate-500 hover:underline">Tout décocher</button>
                </div>
            </div>
            <p class="text-xs text-slate-500 mb-4">Cochez les élèves admis à l'examen — les autres seront comptés comme échoués.</p>

            <div class="max-h-96 overflow-y-auto divide-y divide-slate-100 border border-slate-200 rounded-xl">
                <template x-for="student in visibleStudents" :key="student.id">
                    <label class="flex items-center justify-between gap-3 px-4 py-2.5 cursor-pointer hover:bg-slate-50">
                        <span class="flex items-center gap-3">
                            <input type="checkbox" name="admitted_student_ids[]" :value="student.id" x-model.number="admitted" class="rounded text-emerald-600">
                            <span class="text-sm font-medium text-slate-700" x-text="student.name"></span>
                        </span>
                        <span class="text-xs text-slate-400" x-text="student.roll"></span>
                    </label>
                </template>
            </div>

            <div class="mt-4 flex items-center gap-6 text-sm">
                <span class="font-semibold text-emerald-700"><span x-text="admittedCount"></span> admis</span>
                <span class="font-semibold text-red-600"><span x-text="presentedCount - admittedCount"></span> échoués</span>
                <span class="font-semibold text-slate-700" x-show="presentedCount > 0">
                    Taux : <span x-text="Math.round((admittedCount / presentedCount) * 100)"></span>%
                </span>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('school.exam-results.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold hover:bg-slate-50">Annuler</a>
            <button type="submit" :disabled="presentedCount === 0" :class="presentedCount === 0 ? 'opacity-50 cursor-not-allowed' : ''" class="px-5 py-2.5 rounded-xl bg-[#031C5B] text-white font-bold hover:bg-blue-900 transition">
                Valider les résultats
            </button>
        </div>
    </form>
</div>
@endsection
