@extends('ParentPortal::layout')

@section('title', 'Devoirs')

@section('content')
<h1 class="text-[22px] font-bold text-slate-900 mb-6">Devoirs & Interrogations</h1>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Titre</th>
                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Matière</th>
                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Date</th>
                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Statut</th>
                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Note</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($assignments as $assignment)
                <tr>
                    <td class="px-5 py-3.5 font-bold text-slate-800 text-[13.5px]">{{ $assignment->title }}</td>
                    <td class="px-4 py-3.5 text-[12.5px] text-slate-500">{{ $assignment->subject->name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-[12.5px] text-slate-500">{{ $assignment->scheduled_at?->translatedFormat('d M Y') }}</td>
                    <td class="px-4 py-3.5">
                        @if(($assignment->submission?->status ?? 'non_remis') === 'remis')
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700">Remis</span>
                        @else
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500">Non remis</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 font-extrabold text-[#031C5B]">
                        {{ $assignment->submission?->score !== null ? number_format($assignment->submission->score, 2) . '/' . rtrim(rtrim(number_format($assignment->max_score, 2), '0'), '.') : '—' }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400 text-[13.5px]">Aucun devoir ou interrogation pour l'instant.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
