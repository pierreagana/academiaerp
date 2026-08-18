@extends('ParentPortal::layout')

@section('title', 'Transport')

@section('content')
<h1 class="text-[22px] font-bold text-slate-900 mb-6">Transport Scolaire</h1>

@if(!$morningStop && !$eveningStop)
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400 text-[13.5px]">
    Aucun trajet de bus assigné à cet élève.
</div>
@else
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
    <div class="flex items-center justify-between">
        <span class="text-[13px] font-semibold text-slate-500">Arrêt du matin (maison &rarr; école)</span>
        <span class="font-bold text-slate-800 text-[14px]">{{ $morningStop->name ?? 'Non défini' }}</span>
    </div>
    @if($morningStop)
    <div class="flex items-center justify-between">
        <span class="text-[13px] font-semibold text-slate-500">Heure de passage</span>
        <span class="font-bold text-slate-800 text-[14px]">{{ $morningStop->arrival_time }}</span>
    </div>
    @endif
    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
        <span class="text-[13px] font-semibold text-slate-500">Arrêt du soir (école &rarr; maison)</span>
        <span class="font-bold text-slate-800 text-[14px]">{{ $eveningStop->name ?? 'Non défini' }}</span>
    </div>
    @if($eveningStop)
    <div class="flex items-center justify-between">
        <span class="text-[13px] font-semibold text-slate-500">Heure de passage</span>
        <span class="font-bold text-slate-800 text-[14px]">{{ $eveningStop->arrival_time }}</span>
    </div>
    @endif
    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
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
