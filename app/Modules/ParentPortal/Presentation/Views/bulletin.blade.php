@extends('ParentPortal::layout')

@section('title', 'Bulletin')

@section('content')
<h1 class="text-[22px] font-bold text-slate-900 mb-1">Bulletin</h1>
<p class="text-[13.5px] text-slate-500 mb-6">{{ $currentSemester->name ?? 'Semestre non défini' }}</p>

@if(!$isPublished)
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center">
    <i class="ph-bold ph-eye-slash text-3xl text-slate-300 mb-3 block"></i>
    <p class="font-bold text-slate-700 mb-1">Bulletin non encore publié</p>
    <p class="text-[13px] text-slate-500">L'établissement n'a pas encore publié le bulletin de ce semestre.</p>
</div>
@else
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-[#031C5B] text-white rounded-2xl p-5 text-center">
        <div class="text-[26px] font-extrabold">{{ $average !== null ? $average . '/20' : '—' }}</div>
        <div class="text-[10.5px] uppercase font-bold opacity-70 mt-1">Moy. Générale</div>
    </div>
    <div class="bg-white border border-slate-100 rounded-2xl p-5 text-center">
        <div class="text-[26px] font-extrabold text-slate-800">{{ $rank ? $rank . 'e' : '—' }}</div>
        <div class="text-[10.5px] uppercase font-bold text-slate-400 mt-1">Rang @if($classSize) sur {{ $classSize }} @endif</div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Matière</th>
                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Note/20</th>
                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase">Appréciation</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($grades as $grade)
                <tr>
                    <td class="px-5 py-3.5 font-bold text-slate-800 text-[13.5px]">{{ $grade->subject->name }}</td>
                    <td class="px-4 py-3.5 font-extrabold text-[#031C5B]">{{ number_format($grade->score, 2) }}</td>
                    <td class="px-4 py-3.5 text-[12.5px] italic text-slate-500">{{ $grade->remark ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400 text-[13.5px]">Aucune note publiée pour l'instant.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif
@endsection
