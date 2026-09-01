@extends('ParentPortal::layout')

@section('title', 'Transport')

@section('content')
<h1 class="text-[22px] font-bold text-slate-900 mb-6">Transport Scolaire</h1>

@php
    $periods = [
        'morning' => ['label' => 'Matin', 'desc' => 'Maison → École', 'stop' => $morningStop, 'status' => $morningStatus],
        'evening' => ['label' => 'Soir', 'desc' => 'École → Maison', 'stop' => $eveningStop, 'status' => $eveningStatus],
    ];
@endphp

{{-- Each period (matin/soir) is requested, approved, and can be withdrawn
     independently — a child may well have one but not the other, so each
     gets its own card instead of an all-or-nothing view. --}}
<div class="space-y-4">
@foreach($periods as $period => $p)
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <p class="font-bold text-slate-800 text-[15px] mb-3">Arrêt du {{ strtolower($p['label']) }} <span class="font-normal text-slate-400 text-[12.5px]">({{ $p['desc'] }})</span></p>

    @if($p['stop'])
        <div class="flex items-center justify-between">
            <span class="text-[13px] font-semibold text-slate-500">Arrêt</span>
            <span class="font-bold text-slate-800 text-[14px]">{{ $p['stop']->name }}</span>
        </div>
        <div class="flex items-center justify-between mt-2">
            <span class="text-[13px] font-semibold text-slate-500">Heure de passage</span>
            <span class="font-bold text-slate-800 text-[14px]">{{ $p['stop']->arrival_time }}</span>
        </div>
    @elseif($p['status']['status'] === 'pending')
        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[13px] font-bold flex items-center gap-2">
            <i class="ph-bold ph-hourglass"></i> Demande envoyée, en attente de validation par l'école.
        </div>
    @else
        @if($p['status']['status'] === 'withdrawn')
            <p class="text-slate-500 text-[13px] mb-3">Cet élève a été retiré de ce service par l'école. Vous pouvez refaire une demande si vous le souhaitez.</p>
        @elseif($p['status']['status'] === 'rejected' && $p['status']['rejectionReason'])
            <div class="p-3 mb-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-[12.5px]">
                <span class="font-bold">Demande précédente refusée :</span> {{ $p['status']['rejectionReason'] }}
            </div>
        @else
            <p class="text-slate-500 text-[13px] mb-3">Cet élève n'est pas inscrit pour ce trajet — faites une demande si vous le souhaitez.</p>
        @endif
        <form method="POST" action="{{ route('parent.transport.request', $child->id) }}" class="flex items-end gap-3">
            @csrf
            <input type="hidden" name="period" value="{{ $period }}">
            <div class="flex-1">
                <label class="text-[11.5px] font-bold text-slate-600 mb-1.5 block">Arrêt souhaité</label>
                <select name="route_stop_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-[13px] font-bold">
                    @forelse($availableRoutes as $r)
                        <optgroup label="{{ $r->name }}">
                            @foreach($r->stops as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} @if($s->arrival_time) ({{ $s->arrival_time }}) @endif</option>
                            @endforeach
                        </optgroup>
                    @empty
                        <option disabled>Aucun itinéraire disponible</option>
                    @endforelse
                </select>
            </div>
            <button type="submit" class="bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-4 py-2 rounded-xl transition whitespace-nowrap">Demander</button>
        </form>
    @endif
</div>
@endforeach
</div>

@if($route || $bus)
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3 mt-4">
    <div class="flex items-center justify-between">
        <span class="text-[13px] font-semibold text-slate-500">Itinéraire</span>
        <span class="font-bold text-slate-800 text-[14px]">{{ $route->name ?? '—' }}</span>
    </div>
    <div class="flex items-center justify-between">
        <span class="text-[13px] font-semibold text-slate-500">Bus</span>
        <span class="font-bold text-slate-800 text-[14px]">{{ $bus->bus_number ?? '—' }} @if($bus?->plate_number) ({{ $bus->plate_number }}) @endif</span>
    </div>
    @if($bus?->driver)
    <div class="flex items-center justify-between">
        <span class="text-[13px] font-semibold text-slate-500">Chauffeur</span>
        <span class="font-bold text-slate-800 text-[14px]">{{ $bus->driver->first_name }} {{ $bus->driver->last_name }}</span>
    </div>
    @endif
</div>
@endif
@endsection
