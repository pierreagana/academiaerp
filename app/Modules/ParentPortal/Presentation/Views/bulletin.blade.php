@extends('ParentPortal::layout')

@section('title', 'Bulletin - ' . $child->first_name)

@section('content')

<!-- TOP HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Bulletin de Notes &bull; {{ $child->first_name }}</h1>
        <p class="text-sm font-medium text-slate-500 mt-0.5">Classe: <span class="font-bold text-slate-800">{{ $child->academicClass->name ?? 'Classe' }}</span> &bull; {{ $currentSemester->name ?? 'Semestre en cours' }}</p>
    </div>

    @if($isPublished)
    <div class="flex items-center gap-3">
        <button type="button" onclick="window.print()" 
                class="inline-flex items-center gap-2 bg-blue-100/80 hover:bg-blue-200/80 text-[#061536] font-bold text-xs px-4 py-2.5 rounded-xl transition shadow-xs">
            <span class="material-symbols-outlined text-[18px]">download</span>
            <span>Télécharger Bulletin (PDF)</span>
        </button>
    </div>
    @endif
</div>

@if(!$isPublished)
<div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-12 text-center">
    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-3">
        <span class="material-symbols-outlined text-[28px]">visibility_off</span>
    </div>
    <h3 class="text-base font-bold text-slate-800 mb-1">Bulletin en cours d'élaboration</h3>
    <p class="text-xs text-slate-500 max-w-sm mx-auto">L'administration scolaire n'a pas encore publié les notes officielles pour ce semestre.</p>
</div>
@else

<!-- SUMMARY METRIC TILES -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="bg-[#061536] text-white rounded-3xl p-6 shadow-md shadow-blue-950/20 flex items-center justify-between">
        <div>
            <span class="text-[11.5px] font-bold text-blue-200/80 uppercase tracking-wider block mb-1">Moyenne Générale</span>
            <div class="text-3xl font-black text-white">
                {{ $average !== null ? number_format($average, 2) : '—' }} <span class="text-base font-bold text-blue-300/70">/20</span>
            </div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-blue-300 text-2xl">
            <i class="ph-fill ph-calculator"></i>
        </div>
    </div>

    <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-xs flex items-center justify-between">
        <div>
            <span class="text-[11.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Rang dans la classe</span>
            <div class="text-3xl font-black text-slate-900">
                {{ $rank ? $rank . 'e' : '—' }} @if($classSize) <span class="text-sm font-bold text-slate-400">sur {{ $classSize }} élèves</span> @endif
            </div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-600 text-2xl">
            <i class="ph-fill ph-trophy"></i>
        </div>
    </div>
</div>

<!-- GRADES TABLE -->
<div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-sm font-extrabold text-slate-900">Notes par Matière</h2>
        <span class="text-xs font-bold bg-slate-100 text-slate-600 px-3 py-1 rounded-xl">{{ count($grades) }} Matières</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                    <th class="px-5 py-3.5">Matière</th>
                    <th class="px-4 py-3.5">Note / 20</th>
                    <th class="px-4 py-3.5">Appréciation</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                @forelse($grades as $grade)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4 font-bold text-slate-900 text-sm">{{ $grade->subject->name }}</td>
                        <td class="px-4 py-4 font-black text-sm {{ $grade->score >= 10 ? 'text-[#061536]' : 'text-rose-600' }}">
                            {{ number_format($grade->score, 2) }}
                        </td>
                        <td class="px-4 py-4 font-medium text-slate-500 italic">{{ $grade->remark ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-8 text-center text-slate-400">Aucune note enregistrée pour l'instant.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
