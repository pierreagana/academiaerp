@extends('ParentPortal::layout')

@section('title', 'Présence')

@section('content')
<h1 class="text-[22px] font-bold text-slate-900 mb-6">Présence</h1>

<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white border border-slate-100 rounded-2xl p-5 text-center">
        <div class="text-[24px] font-extrabold {{ $unjustifiedAbsences > 0 ? 'text-red-600' : 'text-slate-800' }}">{{ $unjustifiedAbsences }}</div>
        <div class="text-[10.5px] uppercase font-bold text-slate-400 mt-1">Absences Injustifiées</div>
    </div>
    <div class="bg-white border border-slate-100 rounded-2xl p-5 text-center">
        <div class="text-[24px] font-extrabold text-slate-800">{{ $lateCount }}</div>
        <div class="text-[10.5px] uppercase font-bold text-slate-400 mt-1">Retards</div>
    </div>
    <div class="bg-white border border-slate-100 rounded-2xl p-5 text-center">
        <div class="text-[24px] font-extrabold text-slate-800">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</div>
        <div class="text-[10.5px] uppercase font-bold text-slate-400 mt-1">Taux de Présence (60j)</div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Date</th>
                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Statut</th>
                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Détails</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($records as $record)
                <tr>
                    <td class="px-5 py-3.5 font-bold text-slate-800 text-[13.5px]">{{ $record->date->translatedFormat('d M Y') }}</td>
                    <td class="px-4 py-3.5">
                        @if($record->status === 'present')
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700">Présent</span>
                        @elseif($record->status === 'late')
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700">Retard</span>
                        @else
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-red-50 text-red-700">Absent{{ $record->justified ? ' (justifié)' : '' }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-[12.5px] text-slate-500">{{ $record->late_minutes ? $record->late_minutes . ' min' : ($record->notes ?? '—') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400 text-[13.5px]">Aucun enregistrement sur cette période.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
