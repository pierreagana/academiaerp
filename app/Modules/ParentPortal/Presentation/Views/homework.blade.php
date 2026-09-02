@extends('ParentPortal::layout')

@section('title', 'Devoirs & Examens - ' . $child->first_name)

@section('content')

<!-- HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Devoirs & Examens &bull; {{ $child->first_name }}</h1>
        <p class="text-sm font-medium text-slate-500 mt-0.5">Suivi des travaux maison et interrogations &bull; Classe: {{ $child->academicClass->name ?? 'Non assignée' }}</p>
    </div>
</div>

<!-- HOMEWORK ASSIGNMENTS TABLE -->
<div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-sm font-extrabold text-slate-900">Liste des Travaux Assignés</h2>
        <span class="text-xs font-bold bg-slate-100 text-slate-600 px-3 py-1 rounded-xl">{{ count($assignments) }} Devoirs</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                    <th class="px-5 py-3.5">Titre</th>
                    <th class="px-4 py-3.5">Matière</th>
                    <th class="px-4 py-3.5">Date Prévue</th>
                    <th class="px-4 py-3.5">Statut</th>
                    <th class="px-4 py-3.5 text-right">Note Obtenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                @forelse($assignments as $assignment)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4 font-bold text-slate-900">{{ $assignment->title }}</td>
                        <td class="px-4 py-4 font-semibold text-slate-600">{{ $assignment->subject->name ?? '—' }}</td>
                        <td class="px-4 py-4 font-medium text-slate-500">{{ $assignment->scheduled_at?->translatedFormat('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-4">
                            @if(($assignment->submission?->status ?? 'non_remis') === 'remis')
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    <span class="material-symbols-outlined text-[13px]">check</span> Remis
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-500">
                                    Non remis
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right font-black text-sm text-[#061536]">
                            {{ $assignment->submission?->score !== null ? number_format($assignment->submission->score, 2) . '/' . rtrim(rtrim(number_format($assignment->max_score, 2), '0'), '.') : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400">Aucun devoir ou interrogation assigné pour l'instant.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
