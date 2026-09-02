@extends('ParentPortal::layout')

@section('title', 'Présence - ' . $child->first_name)

@section('content')

<!-- HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Suivi des Présences &bull; {{ $child->first_name }}</h1>
        <p class="text-sm font-medium text-slate-500 mt-0.5">Historique des 60 derniers jours scolaires &bull; Classe: {{ $child->academicClass->name ?? 'Non assignée' }}</p>
    </div>
</div>

<!-- 3 STATS TILES -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border border-slate-100 rounded-3xl p-5 shadow-xs">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Absences Injustifiées</span>
        <div class="text-2xl font-black {{ $unjustifiedAbsences > 0 ? 'text-rose-600' : 'text-slate-900' }}">
            {{ $unjustifiedAbsences }}
        </div>
    </div>

    <div class="bg-white border border-slate-100 rounded-3xl p-5 shadow-xs">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Retards Enregistrés</span>
        <div class="text-2xl font-black {{ $lateCount > 0 ? 'text-amber-600' : 'text-slate-900' }}">
            {{ $lateCount }}
        </div>
    </div>

    <div class="bg-[#061536] text-white rounded-3xl p-5 shadow-md shadow-blue-950/20">
        <span class="text-[11px] font-bold text-blue-200/80 uppercase tracking-wider block mb-1">Taux d'Assiduité Global</span>
        <div class="text-2xl font-black text-white">
            {{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}
        </div>
    </div>
</div>

<!-- ATTENDANCE RECORDS TABLE -->
<div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-sm font-extrabold text-slate-900">Journal des Présences</h2>
        <span class="text-xs font-bold bg-slate-100 text-slate-600 px-3 py-1 rounded-xl">{{ count($records) }} Enregistrements</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                    <th class="px-5 py-3.5">Date</th>
                    <th class="px-4 py-3.5">Statut</th>
                    <th class="px-4 py-3.5 text-right">Détails / Motif</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                @forelse($records as $record)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4 font-bold text-slate-900">{{ $record->date->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-4">
                            @if($record->status === 'present')
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    <span class="material-symbols-outlined text-[13px]">check</span> Présent
                                </span>
                            @elseif($record->status === 'late')
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200/60">
                                    <span class="material-symbols-outlined text-[13px]">schedule</span> Retard
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-3 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200/60">
                                    <span class="material-symbols-outlined text-[13px]">close</span> Absent{{ $record->justified ? ' (justifié)' : '' }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right font-medium text-slate-500">
                            {{ $record->late_minutes ? $record->late_minutes . ' min de retard' : ($record->notes ?? '—') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-8 text-center text-slate-400">Aucun enregistrement sur cette période.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
