@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Promouvoir les élèves</h1>
        <p class="text-[13.5px] text-slate-500 mt-1">Faites passer un ou plusieurs élèves d'un niveau à un autre (ex : 5ème → 4ème). Pour changer de section au même niveau, utilisez « Étudiant transféré ».</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('promotionErrors') && count(session('promotionErrors')))
    <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <div class="flex items-center gap-2 mb-2">
            <i class="ph-fill ph-warning-circle text-lg"></i>
            <span class="font-bold">Certains élèves n'ont pas pu être promus :</span>
        </div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach(session('promotionErrors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <div class="flex items-center gap-2">
            <i class="ph-fill ph-warning-circle text-lg"></i>
            <span class="font-bold">{{ $errors->first() }}</span>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5" x-data="studentPromotionForm()" x-init="init()">
        <form action="{{ route('school.academic.students.promote.store') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="from_class_id" :value="sourceClassId">

            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Classe source <span class="text-red-500">*</span></label>
                <select x-model="sourceClassId" @change="onSourceChange()" required
                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm">
                    <option value="">-- Sélectionner une classe --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}{{ $class->level ? ' (' . $class->level . ')' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div x-show="sourceClassId" x-cloak>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-[13px] font-semibold text-slate-700">Élèves à promouvoir <span class="text-red-500">*</span></label>
                    <label class="flex items-center gap-1.5 text-[12px] text-slate-500 font-medium cursor-pointer">
                        <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="w-3.5 h-3.5 rounded border-slate-300">
                        Tout sélectionner
                    </label>
                </div>
                <div class="border border-slate-200 rounded-lg divide-y divide-slate-100 max-h-64 overflow-y-auto">
                    <template x-for="s in sourceStudents" :key="s.id">
                        <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="student_ids[]" :value="s.id" x-model="selectedIds" class="w-4 h-4 rounded border-slate-300">
                            <span class="text-[13px] font-semibold text-slate-700" x-text="s.name"></span>
                            <span class="text-[12px] text-slate-400" x-text="'(' + s.roll_number + ')'"></span>
                        </label>
                    </template>
                    <p x-show="sourceStudents.length === 0" class="px-4 py-6 text-center text-[13px] text-slate-400">Aucun élève actif dans cette classe.</p>
                </div>
            </div>

            <div x-show="sourceClassId" x-cloak>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Classe de destination (niveau différent) <span class="text-red-500">*</span></label>
                <select name="to_class_id" x-model="toClassId" :disabled="eligibleClasses.length === 0"
                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm disabled:opacity-50">
                    <option value="">-- Choisir la classe --</option>
                    <template x-for="c in eligibleClasses" :key="c.id">
                        <option :value="c.id" x-text="c.name + ' (' + c.level + ')' + (c.branch_name ? ' — ' + c.branch_name : '')"></option>
                    </template>
                </select>
                <p x-show="sourceLevel && eligibleClasses.length === 0" class="text-[12px] text-orange-600 mt-1.5">Aucune classe d'un niveau différent n'est disponible. Créez-en une dans « Gestion des Classes ».</p>
                <p x-show="!sourceLevel" class="text-[12px] text-orange-600 mt-1.5">Le niveau de la classe source n'est pas défini. Configurez-le dans « Gestion des Classes » avant de promouvoir ces élèves.</p>
            </div>

            <div x-show="sourceClassId" x-cloak>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Année académique de destination <span class="text-red-500">*</span></label>
                <input type="text" name="to_academic_year" x-model="toAcademicYear" placeholder="Ex: 2026-2027"
                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm">
            </div>

            <div x-show="sourceClassId" x-cloak>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Motif (optionnel)</label>
                <textarea name="reason" rows="2" placeholder="Ex : promotion de fin d'année scolaire..."
                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"></textarea>
            </div>

            <div class="flex justify-end items-center gap-3">
                <span x-show="selectedIds.length > 0" class="text-[12.5px] font-semibold text-slate-500" x-text="selectedIds.length + ' élève(s) sélectionné(s)'"></span>
                <button type="submit" :disabled="selectedIds.length === 0 || !toClassId || !toAcademicYear"
                    class="bg-[#2F5F76] hover:bg-[#1E4357] disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-[13px] px-6 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
                    <i class="ph-bold ph-trend-up"></i>
                    Promouvoir
                </button>
            </div>
        </form>
    </div>

    <!-- History -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill ph-clock-counter-clockwise text-primary-dynamic"></i>
                Historique des promotions
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Élève</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">De</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Vers</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Année</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($history as $movement)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-[13px] font-bold text-slate-700">{{ $movement->student->first_name ?? '?' }} {{ $movement->student->last_name ?? '' }}</td>
                        <td class="px-5 py-4 text-[13px] text-slate-600">{{ $movement->fromClass->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-[13px] text-slate-600">{{ $movement->toClass->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-[13px] text-slate-600">{{ $movement->from_academic_year }} → {{ $movement->to_academic_year }}</td>
                        <td class="px-5 py-4 text-[12px] text-slate-500">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-500 text-[13px]">Aucune promotion enregistrée pour le moment.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@php
    $promotionStudentsJson = $students->map(fn($s) => [
        'id' => $s->id,
        'name' => $s->first_name . ' ' . $s->last_name,
        'roll_number' => $s->roll_number,
        'class_id' => $s->academic_class_id,
        'academic_year' => $s->academic_year,
    ]);
    $promotionClassesJson = $classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'level' => $c->level]);
    $promotionAllClassesJson = $allClasses->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'level' => $c->level, 'branch_name' => $c->branch->name ?? null]);
@endphp
<script>
    function studentPromotionForm() {
        return {
            allStudents: @json($promotionStudentsJson),
            classes: @json($promotionClassesJson),
            allClasses: @json($promotionAllClassesJson),
            sourceClassId: '',
            toClassId: '',
            toAcademicYear: '',
            sourceLevel: '',
            sourceStudents: [],
            eligibleClasses: [],
            selectedIds: [],
            selectAll: false,
            init() {
                const params = new URLSearchParams(window.location.search);
                const preselect = params.get('class_id');
                if (preselect) {
                    this.sourceClassId = preselect;
                    this.onSourceChange();
                }
            },
            onSourceChange() {
                this.toClassId = '';
                this.selectedIds = [];
                this.selectAll = false;
                const cls = this.classes.find(c => c.id == this.sourceClassId);
                this.sourceLevel = cls ? cls.level : '';
                this.sourceStudents = this.allStudents.filter(s => s.class_id == this.sourceClassId);
                this.eligibleClasses = this.sourceLevel
                    ? this.allClasses.filter(c => c.level && c.level !== this.sourceLevel)
                    : [];
                const latestYear = this.sourceStudents.length ? this.sourceStudents[0].academic_year : '';
                const m = latestYear && latestYear.match(/(\d{4})-(\d{4})/);
                this.toAcademicYear = m ? (parseInt(m[1]) + 1) + '-' + (parseInt(m[2]) + 1) : '';
            },
            toggleAll() {
                this.selectedIds = this.selectAll ? this.sourceStudents.map(s => s.id) : [];
            },
        };
    }
</script>
@endsection
