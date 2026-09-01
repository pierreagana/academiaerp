@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Gestion des Devoirs de Classe</h1>
            <p class="text-[13.5px] text-slate-500 mt-1">Supervisez et planifiez les interrogations et contrôles.</p>
        </div>
        @if(auth()->user()->teacher)
        <a href="{{ route('school.academic.homework.tests.create') }}" class="bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-5 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
            <i class="ph-bold ph-calendar-plus"></i> Planifier une Évaluation
        </a>
        @endif
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @php $inProgress = $assignments->first(fn ($a) => $a->liveStatus === 'in_progress'); @endphp
    @if($inProgress)
    <div class="bg-white rounded-xl shadow-sm border-2 border-[#031C5B]/20 p-6">
        <div class="flex items-center gap-2 mb-2">
            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
            <span class="text-[11px] font-bold text-red-600 uppercase tracking-wider">Évaluation en cours</span>
        </div>
        <h2 class="text-[19px] font-extrabold text-slate-900">{{ $inProgress->title }}</h2>
        <p class="text-[13px] text-slate-500 mb-4">Classe de {{ $inProgress->academicClass->name ?? '—' }}</p>
        <a href="{{ route('school.academic.homework.live', $inProgress->id) }}" class="inline-flex items-center gap-2 bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-5 py-2.5 rounded-lg transition">
            <i class="ph-bold ph-broadcast"></i> Voir la session en direct
        </a>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-[15px] font-bold text-slate-800">Toutes les évaluations</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[13px]">
                    <thead>
                        <tr class="bg-slate-50/50 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="px-5 py-3">Titre</th>
                            <th class="px-5 py-3">Classe</th>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Statut</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php $liveLabels = ['scheduled' => ['Planifiée', 'bg-slate-100 text-slate-600'], 'in_progress' => ['En cours', 'bg-red-50 text-red-600'], 'completed' => ['Terminée', 'bg-emerald-50 text-emerald-700']]; @endphp
                        @forelse($assignments as $assignment)
                            @php [$label, $badge] = $liveLabels[$assignment->liveStatus]; @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-5 py-4 font-bold text-slate-800">{{ $assignment->title }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ $assignment->academicClass->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ $assignment->scheduled_at->translatedFormat('d M, H\hi') }}</td>
                                <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $badge }}">{{ $label }}</span></td>
                                <td class="px-5 py-4 text-right space-x-3">
                                    <a href="{{ route('school.academic.homework.live', $assignment->id) }}" class="text-[12px] font-bold text-[#031C5B] hover:underline">Session</a>
                                    <a href="{{ route('school.academic.homework.submissions', $assignment->id) }}" class="text-[12px] font-bold text-slate-500 hover:underline">Copies</a>
                                    @if($assignment->liveStatus !== 'in_progress')
                                    <form action="{{ route('school.academic.homework.destroy', $assignment->id) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer « {{ addslashes($assignment->title) }} » ? Cette action supprimera aussi toutes les copies et présences liées.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[12px] font-bold text-red-400 hover:text-red-600 transition">Supprimer</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">Aucune évaluation planifiée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-5">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h2 class="text-[15px] font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="ph-bold ph-calendar-blank text-[#031C5B]"></i> Calendrier Hebdomadaire
                </h2>
                <div class="space-y-3">
                    @forelse($upcomingWeek as $item)
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 rounded-full bg-[#031C5B] mt-1.5 shrink-0"></span>
                            <div>
                                <p class="text-[11.5px] font-bold text-slate-400">{{ $item->scheduled_at->translatedFormat('D j M') }} &middot; {{ $item->scheduled_at->format('H:i') }}</p>
                                <p class="text-[13px] font-bold text-slate-800">{{ $item->title }}</p>
                                <p class="text-[11.5px] text-slate-400">{{ $item->subject->name ?? '—' }} ({{ $item->academicClass->name ?? '—' }})</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-[12.5px] text-slate-400 py-2 text-center">Rien de prévu cette semaine.</p>
                    @endforelse
                </div>
                <a href="#" class="block text-center text-[12px] font-bold text-[#031C5B] hover:underline mt-4">Voir tout le planning</a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-[15px] font-bold text-slate-800">À Corriger</h2>
                    @if($toCorrect->isNotEmpty())
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-red-600">{{ $toCorrect->count() }} En attente</span>
                    @endif
                </div>
                <div class="space-y-3">
                    @forelse($toCorrect as $assignment)
                        <div class="p-3 rounded-xl border border-slate-100">
                            <p class="text-[12.5px] font-bold text-slate-800">{{ $assignment->title }}</p>
                            <p class="text-[11px] text-slate-400 mb-2">Classe {{ $assignment->academicClass->name ?? '—' }} &middot; {{ $assignment->pending_count }} copie(s)</p>
                            <a href="{{ route('school.academic.homework.submissions', $assignment->id) }}" class="block text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11.5px] py-1.5 rounded-lg transition">Saisie manuelle</a>
                        </div>
                    @empty
                        <p class="text-[12.5px] text-slate-400 py-2 text-center">Rien à corriger.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
