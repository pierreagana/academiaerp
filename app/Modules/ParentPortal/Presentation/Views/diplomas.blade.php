@extends('ParentPortal::layout')

@section('title', 'Diplômes & Distinctions - ' . $child->first_name)

@section('content')

<!-- HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Diplômes & Distinctions &bull; {{ $child->first_name }}</h1>
        <p class="text-sm font-medium text-slate-500 mt-0.5">Récompenses officielles et mentions d'excellence décernées par l'établissement.</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @forelse($awards as $award)
    <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] p-6 flex items-start gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 shadow-sm text-2xl">
            <i class="ph-fill ph-medal"></i>
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="text-base font-extrabold text-slate-900 leading-snug">{{ $award->type->name ?? 'Diplôme d\'Honneur' }}</h3>
            <p class="text-[11.5px] font-semibold text-slate-400 mt-0.5 mb-2">{{ $award->type->category ?? 'Distinction' }} &bull; {{ $award->awarded_date ? $award->awarded_date->format('d/m/Y') : '' }}</p>
            
            @if($award->reason)
                <p class="text-xs text-slate-600 leading-relaxed">{{ $award->reason }}</p>
            @endif

            @if($award->material_reward)
                <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-xl">
                    <span class="material-symbols-outlined text-[15px]">redeem</span>
                    <span>{{ $award->material_reward }}</span>
                </div>
            @endif

            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('parent.diplomes.print', [$child->id, $award->id]) }}" target="_blank" 
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-[#061536] hover:text-blue-700 transition">
                    <span class="material-symbols-outlined text-[16px]">print</span>
                    <span>Imprimer le certificat officiel</span>
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="md:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-sm p-12 text-center text-slate-400 text-sm">
        <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center mx-auto mb-3 text-2xl">
            <i class="ph-fill ph-medal"></i>
        </div>
        <p class="font-bold text-slate-700 mb-1">Aucun diplôme ou distinction pour l'instant</p>
        <p class="text-xs text-slate-400">Les récompenses attribuées par les professeurs ou la direction apparaîtront ici.</p>
    </div>
    @endforelse
</div>

@endsection
