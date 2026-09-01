@extends('ParentPortal::layout')

@section('title', 'Diplômes')

@section('content')
<h1 class="text-[22px] font-bold text-slate-900 mb-1">Diplômes de {{ $child->first_name }}</h1>
<p class="text-[13.5px] text-slate-500 mb-6">Récompenses et distinctions reçues.</p>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @forelse($awards as $award)
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5 flex items-start gap-4">
        <span class="w-11 h-11 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0"><i class="ph-fill ph-medal text-[20px]"></i></span>
        <div class="min-w-0 flex-1">
            <p class="text-[14.5px] font-bold text-slate-800">{{ $award->type->name ?? '—' }}</p>
            <p class="text-[11.5px] text-slate-400 mb-1">{{ $award->type->category ?? '' }} &middot; {{ $award->awarded_date->format('d/m/Y') }}</p>
            @if($award->reason)
                <p class="text-[12.5px] text-slate-500">{{ $award->reason }}</p>
            @endif
            @if($award->material_reward)
                <p class="text-[11.5px] font-bold text-emerald-600 mt-1">{{ $award->material_reward }}</p>
            @endif
            <a href="{{ route('parent.diplomes.print', [$child->id, $award->id]) }}" target="_blank" class="inline-flex items-center gap-1.5 text-[12px] font-bold text-[#031C5B] hover:underline mt-2">
                <i class="ph-bold ph-printer"></i> Imprimer le diplôme
            </a>
        </div>
    </div>
    @empty
    <div class="md:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-sm p-10 text-center text-slate-400 text-[13.5px]">
        Aucun diplôme reçu pour le moment.
    </div>
    @endforelse
</div>
@endsection
