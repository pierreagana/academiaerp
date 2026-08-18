@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Gestion des Devoirs Maison</h1>
            <p class="text-[13.5px] text-slate-500 mt-1">Supervisez et corrigez les travaux de vos élèves.</p>
        </div>
        @if(auth()->user()->teacher)
        <a href="{{ route('school.academic.homework.homework.create') }}" class="bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-5 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
            <i class="ph-bold ph-plus-circle"></i> Nouveau Devoir
        </a>
        @endif
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form action="{{ route('school.academic.homework.homework') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Classe</label>
                <select name="class_id" onchange="this.form.submit()" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    <option value="">Toutes les classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ (string) $classId === (string) $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Matière</label>
                <select name="subject_id" onchange="this.form.submit()" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    <option value="">Toutes les matières</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ (string) $subjectId === (string) $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
                <i class="ph-fill ph-info text-blue-500 text-lg"></i>
                <h2 class="text-[15px] font-bold text-slate-800">Devoirs en cours</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[13px]">
                    <thead>
                        <tr class="bg-slate-50/50 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="px-5 py-3">Titre du Devoir</th>
                            <th class="px-5 py-3">Date Limite</th>
                            <th class="px-5 py-3">Progression</th>
                            <th class="px-5 py-3">Statut</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($assignments as $assignment)
                            @php
                                $progress = $assignment->progress;
                                $statusMap = ['en_cours' => ['En cours', 'bg-blue-50 text-blue-700'], 'urgent' => ['Urgent', 'bg-amber-50 text-amber-700'], 'termine' => ['Terminé', 'bg-emerald-50 text-emerald-700'], 'en_retard' => ['En retard', 'bg-red-50 text-red-700']];
                                [$label, $badge] = $statusMap[$assignment->statusLabel];
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-800">{{ $assignment->title }}</p>
                                    <p class="text-[11.5px] text-slate-400">{{ $assignment->subject->name ?? '—' }} &middot; {{ $assignment->academicClass->name ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-500">{{ $assignment->scheduled_at->translatedFormat('d M, H\hi') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-[#031C5B]" style="width: {{ $progress['total'] > 0 ? round($progress['remis'] / $progress['total'] * 100) : 0 }}%"></div>
                                        </div>
                                        <span class="text-[11.5px] text-slate-500">{{ $progress['remis'] }}/{{ $progress['total'] }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $badge }}">{{ $label }}</span></td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('school.academic.homework.submissions', $assignment->id) }}" class="text-[12px] font-bold text-[#031C5B] hover:underline">Noter</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">Aucun devoir pour ces critères.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-[15px] font-bold text-slate-800 mb-1 flex items-center gap-2">
                <i class="ph-bold ph-pencil-simple-line text-[#031C5B]"></i> Rendus récents à corriger
            </h2>
            <p class="text-[12px] text-slate-500 mb-4">Copies remises, en attente de notation.</p>
            <div class="space-y-3">
                @forelse($toGrade as $submission)
                    <div class="p-3 rounded-xl border border-slate-100">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-7 h-7 rounded-full bg-[#EEF2F6] text-[#334155] font-bold text-[11px] flex items-center justify-center">
                                {{ substr($submission->student->first_name, 0, 1) }}{{ substr($submission->student->last_name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-[12.5px] font-bold text-slate-800">{{ $submission->student->first_name }} {{ $submission->student->last_name }}</p>
                                <p class="text-[10.5px] text-slate-400">{{ $submission->submitted_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                        <p class="text-[11.5px] text-slate-500 mb-2">Devoir : {{ $submission->assignment->title }}</p>
                        <a href="{{ route('school.academic.homework.submissions', $submission->homework_assignment_id) }}" class="block text-center bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[11.5px] py-1.5 rounded-lg transition">Noter</a>
                    </div>
                @empty
                    <p class="text-[12.5px] text-slate-400 py-4 text-center">Aucun rendu en attente de correction.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
