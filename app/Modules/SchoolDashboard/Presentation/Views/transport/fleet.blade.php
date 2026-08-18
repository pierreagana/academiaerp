@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: false, editOpen: null }">
    @include('SchoolDashboard::transport._tabs')

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Flotte de Bus</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Gérez les véhicules, chauffeurs et statuts de la flotte.</p>
        </div>
        <button @click="createOpen = true" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
            <i class="ph-bold ph-plus"></i> Ajouter un bus
        </button>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Bus</p>
                <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center text-blue-600"><i class="ph-fill ph-bus text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $stats['total'] }}</h3>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Bus Actifs</p>
                <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600"><i class="ph-fill ph-check-circle text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $stats['en_service'] }}</h3>
            <p class="text-[12px] text-slate-400 font-semibold mt-1">En Service</p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Maintenance Requise</p>
                <div class="w-9 h-9 rounded-full bg-amber-50 flex items-center justify-center text-amber-600"><i class="ph-fill ph-wrench text-lg"></i></div>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $stats['maintenance'] }}</h3>
        </div>
    </div>

    <!-- Inventaire de la Flotte -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Inventaire de la Flotte</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Bus</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Chauffeur Assigné</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Capacité</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                @php
                    $busStatusStyles = ['en_service' => 'bg-emerald-100 text-emerald-700', 'disponible' => 'bg-blue-100 text-blue-700', 'maintenance' => 'bg-amber-100 text-amber-700'];
                @endphp
                <tbody class="divide-y divide-slate-100">
                    @forelse($buses as $bus)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <p class="text-[13.5px] font-bold text-slate-800">{{ $bus->bus_number }}</p>
                            <p class="text-[11px] text-slate-500">{{ $bus->plate_number ?? '-' }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $busStatusStyles[$bus->status] ?? '' }}">
                                {{ \App\Modules\Transport\Domain\Models\Bus::STATUSES[$bus->status] ?? $bus->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            @if($bus->driver)
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-[#031C5B]/10 text-[#031C5B] flex items-center justify-center font-bold text-[11px] shrink-0">
                                        {{ substr($bus->driver->first_name, 0, 1) }}{{ substr($bus->driver->last_name, 0, 1) }}
                                    </div>
                                    <span class="text-[13px] font-semibold text-slate-700">{{ $bus->driver->first_name }} {{ $bus->driver->last_name }}</span>
                                </div>
                            @else
                                <span class="text-[13px] text-slate-400">Non assigné</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $bus->capacity }} places</td>
                        <td class="px-5 py-4">
                            <button @click="editOpen = {{ $bus->id }}" class="text-[#031C5B] font-bold text-[13px] hover:underline">Modifier</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun bus enregistré.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('SchoolDashboard::transport._bus_modal', ['bus' => null])
    @foreach($buses as $bus)
        @include('SchoolDashboard::transport._bus_modal', ['bus' => $bus])
    @endforeach
</div>
@endsection
