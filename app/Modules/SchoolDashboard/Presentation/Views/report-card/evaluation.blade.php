@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Grille d'Évaluation</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Sélectionnez une classe, une matière et une compétence pour saisir le niveau de maîtrise de chaque élève.</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    @if(!$currentSemester)
    <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-[14px] font-medium">
        Aucun semestre actif n'est défini pour cette école. Créez un semestre courant dans Académique &rarr; Semestre avant de pouvoir évaluer.
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <form action="{{ route('school.report-card.evaluation') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-[12.5px] font-bold text-slate-600 mb-1.5">Classe</label>
                <select name="class_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 text-[13.5px] font-medium rounded-xl px-3 py-2.5 outline-none focus:border-[#031C5B]">
                    <option value="">Choisir une classe...</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[12.5px] font-bold text-slate-600 mb-1.5">Matière (optionnel)</label>
                <select name="subject_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 text-[13.5px] font-medium rounded-xl px-3 py-2.5 outline-none focus:border-[#031C5B]" {{ $selectedClass ? '' : 'disabled' }}>
                    <option value="">Toutes matières</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ $subjectId == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[12.5px] font-bold text-slate-600 mb-1.5">Compétence</label>
                <select name="competency_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 text-[13.5px] font-medium rounded-xl px-3 py-2.5 outline-none focus:border-[#031C5B]">
                    <option value="">Choisir une compétence...</option>
                    @foreach($competencies as $competency)
                        <option value="{{ $competency->id }}" {{ $competencyId == $competency->id ? 'selected' : '' }}>
                            {{ $competency->subdomain->domain->name }} &rsaquo; {{ $competency->subdomain->name }} &rsaquo; {{ $competency->statement }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($selectedClass && $competencyId && $students->isNotEmpty())
    <form action="{{ route('school.report-card.evaluation.store') }}" method="POST">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
        <input type="hidden" name="subject_id" value="{{ $subjectId }}">
        <input type="hidden" name="competency_id" value="{{ $competencyId }}">

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F8FAFC] border-b border-slate-200">
                            <th class="px-5 py-3.5 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Élève</th>
                            <th class="px-4 py-3.5 text-[11px] font-extrabold text-emerald-700 uppercase tracking-wider text-center">Acquis</th>
                            <th class="px-4 py-3.5 text-[11px] font-extrabold text-amber-700 uppercase tracking-wider text-center">En cours</th>
                            <th class="px-4 py-3.5 text-[11px] font-extrabold text-red-700 uppercase tracking-wider text-center">Non acquis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $student)
                        <tr>
                            <td class="px-5 py-3.5 font-bold text-slate-800 text-[13.5px]">{{ $student->first_name }} {{ $student->last_name }}</td>
                            @foreach(['acquis' => 'emerald', 'en_cours' => 'amber', 'non_acquis' => 'red'] as $level => $color)
                            <td class="px-4 py-3.5 text-center">
                                <input type="radio" name="levels[{{ $student->id }}]" value="{{ $level }}"
                                    {{ ($existingLevels[$student->id] ?? null) === $level ? 'checked' : '' }}
                                    class="w-4 h-4 accent-{{ $color }}-600">
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-3 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all shadow-sm flex items-center gap-2">
                    <i class="ph-bold ph-check"></i> Enregistrer l'évaluation
                </button>
            </div>
        </div>
    </form>
    @elseif($selectedClass && $competencyId)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400 text-[13.5px]">
            Aucun élève actif dans cette classe.
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400 text-[13.5px]">
            Sélectionnez une classe puis une compétence pour afficher la grille de saisie.
        </div>
    @endif
</div>
@endsection
