@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Étudiant transféré</h1>
        <p class="text-[13.5px] text-slate-500 mt-1">Changez la section d'un élève au sein du même niveau (ex : 5ème B → 5ème A). Pour changer de niveau, utilisez « Promouvoir les élèves ».</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
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

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5" x-data="studentTransferForm()">
        <form action="{{ route('school.academic.students.transfer.store') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Élève <span class="text-red-500">*</span></label>
                <select name="student_id" x-model="studentId" @change="onStudentChange()" required
                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm">
                    <option value="">-- Sélectionner un élève --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }} ({{ $student->roll_number }}) — {{ $student->academicClass->name ?? 'Aucune classe' }}</option>
                    @endforeach
                </select>
            </div>

            <div x-show="studentId" x-cloak class="p-4 bg-slate-50 rounded-lg border border-slate-100 space-y-1">
                <p class="text-[13px] text-slate-600">Classe actuelle : <span class="font-bold text-slate-800" x-text="currentClassName"></span></p>
                <p class="text-[13px] text-slate-600">Niveau : <span class="font-bold text-slate-800" x-text="currentLevel || 'Non défini'"></span></p>
            </div>

            <div x-show="studentId" x-cloak>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Nouvelle section (même niveau) <span class="text-red-500">*</span></label>
                <select name="to_class_id" x-model="toClassId" :disabled="eligibleClasses.length === 0"
                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm disabled:opacity-50">
                    <option value="">-- Choisir la section --</option>
                    <template x-for="c in eligibleClasses" :key="c.id">
                        <option :value="c.id" x-text="c.name"></option>
                    </template>
                </select>
                <p x-show="currentLevel && eligibleClasses.length === 0" class="text-[12px] text-orange-600 mt-1.5">Aucune autre classe du niveau <span x-text="currentLevel"></span> n'est disponible. Créez-en une dans « Gestion des Classes ».</p>
                <p x-show="!currentLevel" class="text-[12px] text-orange-600 mt-1.5">Le niveau de la classe actuelle n'est pas défini. Configurez-le dans « Gestion des Classes » avant de transférer cet élève.</p>
            </div>

            <div x-show="studentId" x-cloak>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Motif (optionnel)</label>
                <textarea name="reason" rows="2" placeholder="Ex : rapprochement familial, réorganisation des effectifs..."
                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" :disabled="!studentId || !toClassId"
                    class="bg-[#2F5F76] hover:bg-[#1E4357] disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-[13px] px-6 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
                    <i class="ph-bold ph-arrows-left-right"></i>
                    Transférer l'élève
                </button>
            </div>
        </form>
    </div>

    <!-- History -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill ph-clock-counter-clockwise text-primary-dynamic"></i>
                Historique des transferts
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Élève</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">De</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Vers</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($history as $movement)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-[13px] font-bold text-slate-700">{{ $movement->student->first_name ?? '?' }} {{ $movement->student->last_name ?? '' }}</td>
                        <td class="px-5 py-4 text-[13px] text-slate-600">{{ $movement->fromClass->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-[13px] text-slate-600">{{ $movement->toClass->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-[12px] text-slate-500">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-slate-500 text-[13px]">Aucun transfert enregistré pour le moment.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@php
    $transferStudentsJson = $students->map(fn($s) => [
        'id' => $s->id,
        'class_id' => $s->academic_class_id,
        'class_name' => $s->academicClass->name ?? null,
        'level' => $s->academicClass->level ?? null,
    ]);
    $transferClassesJson = $classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'level' => $c->level]);
@endphp
<script>
    function studentTransferForm() {
        return {
            students: @json($transferStudentsJson),
            classes: @json($transferClassesJson),
            studentId: '',
            toClassId: '',
            currentClassName: '',
            currentLevel: '',
            eligibleClasses: [],
            onStudentChange() {
                this.toClassId = '';
                const s = this.students.find(x => x.id == this.studentId);
                if (!s) {
                    this.currentClassName = '';
                    this.currentLevel = '';
                    this.eligibleClasses = [];
                    return;
                }
                this.currentClassName = s.class_name || 'Aucune classe';
                this.currentLevel = s.level;
                this.eligibleClasses = s.level
                    ? this.classes.filter(c => c.level === s.level && c.id != s.class_id)
                    : [];
            },
        };
    }
</script>
@endsection
