@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::transport._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Scanner — Embarquement Bus</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Scannez ou saisissez le matricule de l'élève avant qu'il ne monte dans le bus. Un élève sans inscription valide est refusé.</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="p-4 text-sm text-red-800 rounded-xl bg-red-50 flex items-center gap-2 border border-red-100" role="alert">
        <i class="ph-fill ph-warning-circle text-lg"></i>
        <span class="font-bold">{{ $errors->first() }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <form method="POST" action="{{ route('school.transport.scanner.scan') }}" class="space-y-4" x-data="{ action: '{{ old('action', 'board') }}' }">
                @csrf
                <div>
                    <label class="text-[12px] font-bold text-slate-600 mb-2 block">Action</label>
                    <div class="flex gap-2">
                        <button type="button" @click="action = 'board'" :class="action === 'board' ? 'bg-[#031C5B] text-white' : 'bg-slate-100 text-slate-600'" class="flex-1 py-2.5 rounded-xl text-[13px] font-bold transition flex items-center justify-center gap-1.5">
                            <i class="ph-bold ph-arrow-circle-up"></i> Montée
                        </button>
                        <button type="button" @click="action = 'alight'" :class="action === 'alight' ? 'bg-[#031C5B] text-white' : 'bg-slate-100 text-slate-600'" class="flex-1 py-2.5 rounded-xl text-[13px] font-bold transition flex items-center justify-center gap-1.5">
                            <i class="ph-bold ph-arrow-circle-down"></i> Descente
                        </button>
                    </div>
                    <input type="hidden" name="action" :value="action">
                </div>
                <div>
                    <label class="text-[12px] font-bold text-slate-600 mb-2 block">Matricule de l'élève</label>
                    <input type="text" name="matricule" autofocus placeholder="Scannez le QR ou saisissez le matricule" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-[15px] font-bold" value="{{ old('matricule') }}">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[12px] font-bold text-slate-600 mb-2 block">Période</label>
                        <select name="period" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-[13px] font-bold">
                            <option value="morning">Matin</option>
                            <option value="evening">Soir</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[12px] font-bold text-slate-600 mb-2 block">Bus (optionnel)</label>
                        <select name="bus_id" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-[13px] font-bold">
                            <option value="">—</option>
                            @foreach($buses as $bus)
                                <option value="{{ $bus->id }}">{{ $bus->bus_number }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-[12px] font-bold text-slate-600 mb-2 block">Route (optionnel)</label>
                    <select name="route_id" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-[13px] font-bold">
                        <option value="">—</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->name }} @if($route->bus) — {{ $route->bus->bus_number }} @endif ({{ $route->period ? \App\Modules\Transport\Domain\Models\Route::PERIODS[$route->period] : 'Matin+Soir' }})</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Précisez la route si le bus a plusieurs trajets sur cette période — sinon la vérification reste par période seule.</p>
                </div>
                <button type="submit" class="w-full bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[14px] py-3.5 rounded-xl transition" x-text="action === 'board' ? 'Valider l\'embarquement' : 'Valider la descente'"></button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[15px] font-extrabold text-slate-800">Derniers embarquements</h3>
                <a href="{{ route('school.transport.history') }}" class="text-[12px] font-bold text-[#031C5B] hover:underline">Historique complet →</a>
            </div>
            <div class="space-y-3">
                @forelse($recentScans as $scan)
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $scan->action === 'board' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $scan->action === 'board' ? 'Montée' : 'Descente' }}
                            </span>
                            <p class="text-[13px] font-bold text-slate-800">{{ $scan->student->first_name ?? '—' }} {{ $scan->student->last_name ?? '' }}</p>
                        </div>
                        <p class="text-[11.5px] text-slate-400 mt-0.5">{{ $scan->period === 'morning' ? 'Matin' : 'Soir' }} · {{ $scan->bus->bus_number ?? '—' }}</p>
                        @if($scan->address)
                            <p class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5"><i class="ph-bold ph-map-pin"></i> {{ $scan->address }}</p>
                        @endif
                    </div>
                    <span class="text-[11px] text-slate-400">{{ $scan->scanned_at->format('H:i') }}</span>
                </div>
                @empty
                <p class="text-[13px] text-slate-400">Aucun embarquement récent.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
