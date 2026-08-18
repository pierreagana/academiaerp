@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Saisie des Notes</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Une matière peut avoir plusieurs notes du même type (plusieurs interrogations, devoirs...). Chaque note compte individuellement dans la moyenne.</p>
        </div>
        <a href="{{ route('school.academic.bulletins.evaluation-types') }}" class="text-[12.5px] font-bold text-[#031C5B] hover:underline flex items-center gap-1.5 shrink-0">
            <i class="ph-bold ph-sliders"></i> Types d'évaluation & coefficients
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    @if(!$currentSemester)
    <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-[14px] font-medium">
        Aucun semestre actif n'est défini pour cette école. Créez un semestre courant avant de pouvoir saisir des notes.
    </div>
    @endif

    @if($evaluationTypes->isEmpty())
    <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-[14px] font-medium flex items-center justify-between gap-4">
        <span>Aucun type d'évaluation configuré pour cette école (Interrogation, Devoir surveillé, Composition...). Configurez-en au moins un avant de pouvoir saisir des notes.</span>
        <a href="{{ route('school.academic.bulletins.evaluation-types') }}" class="shrink-0 px-4 py-2 bg-amber-800 text-white font-bold text-[12.5px] rounded-lg hover:bg-amber-900 transition">Configurer</a>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <form action="{{ route('school.academic.bulletins.grades') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                <label class="block text-[12.5px] font-bold text-slate-600 mb-1.5">Matière</label>
                <select name="subject_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 text-[13.5px] font-medium rounded-xl px-3 py-2.5 outline-none focus:border-[#031C5B]" {{ $selectedClass ? '' : 'disabled' }}>
                    <option value="">Choisir une matière...</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ $subjectId == $subject->id ? 'selected' : '' }}>{{ $subject->name }} (coef. {{ $subject->coefficient }})</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($selectedClass && $subjectId && $students->isNotEmpty() && $evaluationTypes->isNotEmpty())
    @php
        $typesForJs = $columns->map(fn ($c) => ['id' => $c->key, 'coef' => (float) $c->type->coefficient, 'linked' => (bool) $c->linked])->values();
        $editableTypes = $evaluationTypes->whereNull('linked_homework_type');
        $primaryEditableTypeId = $editableTypes->sortByDesc('coefficient')->first()?->id;
        $isSubjectPublished = $subjectPublicationStatus === \App\Modules\Bulletin\Domain\Models\BulletinSubjectPublication::STATUS_PUBLISHED;
    @endphp

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between gap-4">
        <div class="flex items-center gap-2.5">
            @if($isSubjectPublished)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12.5px] font-bold bg-emerald-50 text-emerald-700">
                    <i class="ph-bold ph-check-circle"></i> Publié
                </span>
                <span class="text-[12.5px] text-slate-500">Ces notes sont visibles sur le bulletin de l'élève.</span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12.5px] font-bold bg-slate-100 text-slate-600">
                    <i class="ph-bold ph-eye-slash"></i> Non publié
                </span>
                <span class="text-[12.5px] text-slate-500">Cette matière reste vide sur le bulletin tant qu'elle n'est pas publiée.</span>
            @endif
        </div>
        <form action="{{ route($isSubjectPublished ? 'school.academic.bulletins.grades.unpublish' : 'school.academic.bulletins.grades.publish') }}" method="POST">
            @csrf
            <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
            <input type="hidden" name="subject_id" value="{{ $subjectId }}">
            @if($isSubjectPublished)
                <button type="submit" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 font-bold text-[12.5px] rounded-lg hover:bg-slate-50 transition">Dépublier</button>
            @else
                <button type="submit" class="px-4 py-2 bg-[#031C5B] text-white font-bold text-[12.5px] rounded-lg hover:bg-[#031C5B]/90 transition">Publier mes notes pour cette matière</button>
            @endif
        </form>
    </div>

    <form action="{{ route('school.academic.bulletins.grades.store') }}" method="POST">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
        <input type="hidden" name="subject_id" value="{{ $subjectId }}">

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F8FAFC] border-b border-slate-200">
                            <th class="px-5 py-3.5 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Élève</th>
                            @foreach($columns as $col)
                                <th class="px-4 py-3.5 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                                    {{ $col->label }} <span class="font-medium normal-case text-slate-400">(coef {{ rtrim(rtrim(number_format($col->type->coefficient, 2), '0'), '.') }})</span>
                                    @if($col->linked)
                                        <i class="ph-bold ph-link text-blue-500" title="Note automatique depuis le module Devoirs & Interrogations"></i>
                                    @endif
                                </th>
                            @endforeach
                            <th class="px-4 py-3.5 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Moyenne</th>
                            <th class="px-4 py-3.5 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Appréciation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $student)
                            @php
                                $existingForStudent = $existingGrades->get($student->id, collect());
                                $existingByType = $existingForStudent->map(fn ($rows) => $rows->pluck('score')->values());
                                $existingRemark = $existingForStudent->flatten(1)->sortByDesc(fn ($g) => (float) ($g->evaluationType->coefficient ?? 1))->first(fn ($g) => !empty($g->remark))?->remark;
                            @endphp
                            <tr x-data="gradeRow(@js($typesForJs), @js($existingByType))" class="align-top">
                                <td class="px-5 py-3 font-bold text-slate-800 text-[13.5px]">{{ $student->first_name }} {{ $student->last_name }}</td>
                                @foreach($columns as $col)
                                    <td class="px-4 py-3">
                                        @if($col->linked)
                                            @if($col->assignment)
                                                @php $entry = $existingForStudent->get($col->key, collect())->first(); @endphp
                                                @if($entry)
                                                    <span class="text-[12.5px] font-bold text-slate-700">{{ number_format($entry->score, 2) }}/20</span>
                                                    @if((float) ($entry->max_score ?? 20) != 20)
                                                        <span class="block text-[11px] text-slate-400">{{ number_format($entry->raw_score, 2) }}/{{ rtrim(rtrim(number_format($entry->max_score, 2), '0'), '.') }} sur Devoirs</span>
                                                    @endif
                                                @else
                                                    <span class="text-[12px] text-slate-400">Pas encore noté</span>
                                                @endif
                                                <a href="{{ route('school.academic.homework.submissions', $col->assignment->id) }}" class="block text-[11px] text-blue-600 hover:underline mt-0.5">Voir dans Devoirs →</a>
                                            @else
                                                <span class="text-[12px] text-slate-400">Aucun devoir créé</span>
                                                <a href="{{ route($col->type->linked_homework_type === 'devoir_maison' ? 'school.academic.homework.homework' : 'school.academic.homework.tests') }}" class="block text-[11px] text-blue-600 hover:underline mt-0.5">Voir dans Devoirs →</a>
                                            @endif
                                        @else
                                            <div class="space-y-1 mb-1.5">
                                                @foreach($existingForStudent->get($col->key, collect()) as $entry)
                                                    <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 w-fit">
                                                        <span class="text-[12px] font-bold text-slate-700">{{ number_format($entry->score, 2) }}</span>
                                                        <button type="submit" form="delete-grade-{{ $entry->id }}" class="text-slate-400 hover:text-red-500 transition" title="Supprimer cette note">
                                                            <i class="ph-bold ph-x text-[10px]"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <input type="number" name="entries[{{ $student->id }}][{{ $col->type->id }}][score]" step="0.25" min="0" max="20"
                                                x-model="newScores['{{ $col->key }}']"
                                                placeholder="+ note"
                                                class="w-20 bg-slate-50 border border-slate-200 text-[13px] font-bold rounded-lg px-2.5 py-1.5 outline-none focus:border-[#031C5B]">
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-4 py-3">
                                    <span class="font-extrabold text-[#031C5B]" x-text="moyenne() !== null ? moyenne().toFixed(2) : '—'"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2" x-data="{ remark: {{ json_encode($existingRemark) }} }">
                                        @if($primaryEditableTypeId)
                                        <input type="text" name="entries[{{ $student->id }}][{{ $primaryEditableTypeId }}][remark]" x-model="remark"
                                            class="flex-1 bg-slate-50 border border-slate-200 text-[12.5px] rounded-lg px-2.5 py-1.5 outline-none focus:border-[#031C5B]" placeholder="Appréciation...">
                                        <button type="button" title="Suggérer une appréciation selon le barème"
                                            @click="if (moyenne() !== null) { remark = suggestRemark(moyenne()) }"
                                            class="shrink-0 text-slate-400 hover:text-[#031C5B] transition p-1.5">
                                            <i class="ph-bold ph-magic-wand text-[15px]"></i>
                                        </button>
                                        @else
                                        <span class="text-[11.5px] text-slate-400">{{ $existingRemark ?? '—' }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <p class="text-[11.5px] text-slate-400">Chaque nouvelle note saisie s'ajoute aux précédentes (ne remplace rien) — revenez sur cette page pour en ajouter une autre.</p>
                <button type="submit" class="px-6 py-3 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all shadow-sm flex items-center gap-2 shrink-0">
                    <i class="ph-bold ph-check"></i> Enregistrer les Notes
                </button>
            </div>
        </div>
    </form>

    {{-- Standalone delete forms for existing entries (outside the main form — HTML forms can't nest) --}}
    @foreach($students as $student)
        @foreach($existingGrades->get($student->id, collect()) as $typeEntries)
            @foreach($typeEntries as $entry)
                @if(is_a($entry, \App\Modules\Bulletin\Domain\Models\BulletinGrade::class))
                <form id="delete-grade-{{ $entry->id }}" action="{{ route('school.academic.bulletins.grades.destroy', $entry->id) }}" method="POST" class="hidden" onsubmit="return confirm('Supprimer cette note ?');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                </form>
                @endif
            @endforeach
        @endforeach
    @endforeach
    @elseif($selectedClass && $subjectId && $evaluationTypes->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400 text-[13.5px]">
            Aucun élève actif dans cette classe.
        </div>
    @elseif($evaluationTypes->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400 text-[13.5px]">
            Sélectionnez une classe puis une matière pour afficher la grille de saisie.
        </div>
    @endif
</div>
<script>
    function suggestRemark(score) {
        if (score >= 16) return 'Excellent travail, continuez ainsi.';
        if (score >= 14) return 'Bon trimestre, bonne maîtrise des notions.';
        if (score >= 12) return 'Assez bien, des efforts à poursuivre.';
        if (score >= 10) return 'Résultat passable, doit approfondir le travail personnel.';
        return 'Résultat insuffisant, un accompagnement est nécessaire.';
    }

    function gradeRow(types, existingByType) {
        return {
            newScores: Object.fromEntries(types.filter(t => !t.linked).map(t => [t.id, ''])),
            moyenne() {
                let totalScore = 0, totalWeight = 0;
                for (const t of types) {
                    for (const score of (existingByType[t.id] || [])) {
                        totalScore += parseFloat(score) * t.coef;
                        totalWeight += t.coef;
                    }
                    const v = this.newScores[t.id];
                    if (v !== undefined && v !== '' && v !== null) {
                        totalScore += parseFloat(v) * t.coef;
                        totalWeight += t.coef;
                    }
                }
                return totalWeight > 0 ? totalScore / totalWeight : null;
            }
        };
    }
</script>
@endsection
