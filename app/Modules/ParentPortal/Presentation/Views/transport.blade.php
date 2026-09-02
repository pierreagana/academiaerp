@extends('ParentPortal::layout')

@section('title', 'Transport Scolaire - ' . $child->first_name)

@section('content')

<!-- HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Transport & Bus &bull; {{ $child->first_name }}</h1>
        <p class="text-sm font-medium text-slate-500 mt-0.5">Arrêts de ramassage, horaires et véhicule assigné.</p>
    </div>
</div>

@php
    $periods = [
        'morning' => ['label' => 'Matin', 'desc' => 'Maison → École', 'stop' => $morningStop, 'status' => $morningStatus],
        'evening' => ['label' => 'Soir', 'desc' => 'École → Maison', 'stop' => $eveningStop, 'status' => $eveningStatus],
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
@foreach($periods as $period => $p)
<div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] p-6 flex flex-col justify-between">
    <div>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                    <span class="material-symbols-outlined text-[19px]">directions_bus</span>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Trajet du {{ $p['label'] }}</h3>
                    <p class="text-[11px] font-medium text-slate-400">{{ $p['desc'] }}</p>
                </div>
            </div>
            @if($p['stop'])
                <span class="text-[10.5px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60">Inscrit</span>
            @endif
        </div>

        @if($p['stop'])
            <div class="space-y-3 bg-slate-50/70 p-4 rounded-2xl border border-slate-100/80">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500">Arrêt assigné</span>
                    <span class="text-xs font-black text-slate-900">{{ $p['stop']->name }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500">Heure de passage</span>
                    <span class="text-xs font-black text-[#061536]">{{ $p['stop']->arrival_time }}</span>
                </div>
            </div>
        @elseif($p['status']['status'] === 'pending')
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-800 text-xs font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-[17px]">hourglass_empty</span>
                <span>Demande envoyée, en attente de validation par l'école.</span>
            </div>
        @else
            @if($p['status']['status'] === 'withdrawn')
                <p class="text-slate-500 text-xs mb-3">Cet élève a été retiré de ce service. Vous pouvez faire une nouvelle demande.</p>
            @elseif($p['status']['status'] === 'rejected' && $p['status']['rejectionReason'])
                <div class="p-3 mb-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    <strong>Refus :</strong> {{ $p['status']['rejectionReason'] }}
                </div>
            @else
                <p class="text-slate-500 text-xs mb-3">Cet élève n'est pas inscrit pour ce trajet.</p>
            @endif

            <form method="POST" action="{{ route('parent.transport.request', $child->id) }}" class="space-y-3">
                @csrf
                <input type="hidden" name="period" value="{{ $period }}">
                <div>
                    <label class="text-[11.5px] font-bold text-slate-700 mb-1 block">Choisir un arrêt</label>
                    <select name="route_stop_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 outline-none focus:border-blue-500">
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
                <button type="submit" class="w-full bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs py-2.5 rounded-xl transition">
                    Demander l'inscription
                </button>
            </form>
        @endif
    </div>
</div>
@endforeach
</div>

@if($route || $bus)
<div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] p-6">
    <h3 class="text-sm font-extrabold text-slate-900 mb-4">Informations Véhicule & Chauffeur</h3>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div class="bg-slate-50 p-4 rounded-2xl">
            <span class="text-[11px] font-bold text-slate-400 block mb-0.5">Ligne / Itinéraire</span>
            <span class="font-extrabold text-slate-900">{{ $route->name ?? 'Ligne Principale' }}</span>
        </div>
        <div class="bg-slate-50 p-4 rounded-2xl">
            <span class="text-[11px] font-bold text-slate-400 block mb-0.5">Bus Assigné</span>
            <span class="font-extrabold text-slate-900">{{ $bus->bus_number ?? 'Bus #1' }} @if($bus?->plate_number) ({{ $bus->plate_number }}) @endif</span>
        </div>
        @if($bus?->driver)
        <div class="bg-slate-50 p-4 rounded-2xl">
            <span class="text-[11px] font-bold text-slate-400 block mb-0.5">Chauffeur</span>
            <span class="font-extrabold text-slate-900">{{ $bus->driver->first_name }} {{ $bus->driver->last_name }}</span>
        </div>
        @endif
    </div>
</div>
@endif

@endsection
