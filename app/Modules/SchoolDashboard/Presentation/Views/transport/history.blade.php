@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::transport._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Historique des Embarquements</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Chaque montée et descente scannée — chauffeur ou école — avec l'adresse où elle a eu lieu.</p>
    </div>

    <!-- Résumé -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                <i class="ph-bold ph-arrow-circle-up text-emerald-700 text-2xl"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Montées</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ $boardedCount }}</h3>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                <i class="ph-bold ph-arrow-circle-down text-amber-700 text-2xl"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Descentes</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ $alightedCount }}</h3>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3 flex-wrap">
        <select name="action" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] outline-none focus:border-[#031C5B]">
            <option value="">Montée et descente</option>
            <option value="board" {{ ($filters['action'] ?? null) === 'board' ? 'selected' : '' }}>Montée uniquement</option>
            <option value="alight" {{ ($filters['action'] ?? null) === 'alight' ? 'selected' : '' }}>Descente uniquement</option>
        </select>
        <select name="period" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] outline-none focus:border-[#031C5B]">
            <option value="">Matin et soir</option>
            <option value="morning" {{ ($filters['period'] ?? null) === 'morning' ? 'selected' : '' }}>Matin</option>
            <option value="evening" {{ ($filters['period'] ?? null) === 'evening' ? 'selected' : '' }}>Soir</option>
        </select>
        <select name="bus_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] outline-none focus:border-[#031C5B]">
            <option value="">Tous les bus</option>
            @foreach($buses as $bus)
                <option value="{{ $bus->id }}" {{ ($filters['bus_id'] ?? null) == $bus->id ? 'selected' : '' }}>{{ $bus->bus_number }}</option>
            @endforeach
        </select>
        <select name="route_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] outline-none focus:border-[#031C5B]">
            <option value="">Toutes les routes</option>
            @foreach($routes as $route)
                <option value="{{ $route->id }}" {{ ($filters['route_id'] ?? null) == $route->id ? 'selected' : '' }}>{{ $route->name }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" onchange="this.form.submit()" placeholder="Du" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] outline-none focus:border-[#031C5B]">
        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" onchange="this.form.submit()" placeholder="Au" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] outline-none focus:border-[#031C5B]">
        @if(array_filter($filters))
            <a href="{{ route('school.transport.history') }}" class="text-[12.5px] font-bold text-slate-500 hover:text-slate-700">Réinitialiser</a>
        @endif
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                <thead>
                    <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-widest bg-slate-50 border-b border-slate-200">
                        <th class="py-3 px-5">Élève</th>
                        <th class="py-3 px-4">Action</th>
                        <th class="py-3 px-4">Période</th>
                        <th class="py-3 px-4">Bus</th>
                        <th class="py-3 px-4">Route</th>
                        <th class="py-3 px-4">Adresse</th>
                        <th class="py-3 px-5 text-right">Scanné le</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[13px]">
                    @forelse($scans as $scan)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-5 font-bold text-slate-900">{{ $scan->student->first_name ?? '—' }} {{ $scan->student->last_name ?? '' }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $scan->action === 'board' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    <i class="ph-bold {{ $scan->action === 'board' ? 'ph-arrow-circle-up' : 'ph-arrow-circle-down' }}"></i>
                                    {{ $scan->action === 'board' ? 'Montée' : 'Descente' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $scan->period === 'morning' ? 'Matin' : 'Soir' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $scan->bus->bus_number ?? '—' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $scan->route->name ?? '—' }}</td>
                            <td class="py-3 px-4 text-slate-600 whitespace-normal max-w-xs">
                                @if($scan->address)
                                    <span class="flex items-start gap-1"><i class="ph-bold ph-map-pin mt-0.5 shrink-0"></i> {{ $scan->address }}</span>
                                @else
                                    <span class="text-slate-300">Non disponible</span>
                                @endif
                            </td>
                            <td class="py-3 px-5 text-right text-slate-500">{{ $scan->scanned_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-slate-400 text-sm">Aucun embarquement pour ces filtres.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($scans->hasPages())
        <div class="p-4 border-t border-slate-200">{{ $scans->links() }}</div>
        @endif
    </div>
</div>
@endsection
