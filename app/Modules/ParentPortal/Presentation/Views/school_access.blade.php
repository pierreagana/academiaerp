@extends('ParentPortal::layout')

@section('title', 'Accès Scolaire' . ($selectedChild ? ' – ' . $selectedChild->first_name : ''))

@push('styles')
<style>
    .pulse-dot::before {
        content:''; display:inline-block; width:8px; height:8px; border-radius:50%;
        margin-right:6px; animation: pulse-ring 1.4s ease infinite;
    }
    .pulse-green::before { background:#10b981; box-shadow: 0 0 0 0 rgba(16,185,129,.6); }
    .pulse-slate::before { background:#94a3b8; }
    @keyframes pulse-ring {
        0%   { box-shadow: 0 0 0 0 rgba(16,185,129,.5); }
        70%  { box-shadow: 0 0 0 8px rgba(16,185,129,0); }
        100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
    }
    .day-cell { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; cursor:default; }
    .day-present { background:#d1fae5; color:#065f46; }
    .day-absent  { background:#fee2e2; color:#991b1b; }
    .day-late    { background:#fef3c7; color:#92400e; }
    .day-future  { background:#f8fafc; color:#e2e8f0; border: 1px dashed #e2e8f0; }
</style>
@endpush

@section('content')

{{-- CHILD SWITCHER --}}
@if($children->count() > 1)
<div class="flex gap-2 flex-wrap mb-5">
    @foreach($children as $kid)
    <a href="{{ route('parent.school-access') }}?student={{ $kid->id }}"
       class="flex items-center gap-2.5 px-4 py-2 rounded-xl text-[12.5px] font-bold transition border
              {{ $selectedChild && $selectedChild->id === $kid->id
                 ? 'bg-[#061536] text-white border-[#061536] shadow-md'
                 : 'bg-white text-slate-600 border-slate-200 hover:border-[#061536] hover:text-[#061536]' }}">
        <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 text-white text-[11px] flex items-center justify-center font-black">
            {{ substr($kid->first_name, 0, 1) }}
        </div>
        {{ $kid->first_name }} {{ $kid->last_name }}
    </a>
    @endforeach
</div>
@endif

@if(! $selectedChild)
<div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
    <span class="material-symbols-outlined text-5xl text-slate-300">badge</span>
    <p class="text-slate-500 font-semibold mt-3">Aucun enfant trouvé dans votre compte.</p>
</div>
@else

{{-- ══ TOP ROW : LIVE STATUS + 3 STAT CARDS ══ --}}
<div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mb-5">

    {{-- Live status card --}}
    <div class="lg:col-span-1 bg-white rounded-2xl border border-slate-200 p-6 flex flex-col justify-between shadow-sm">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Statut Temps Réel</p>
                <h2 class="text-[15px] font-extrabold text-slate-900 leading-tight">{{ $selectedChild->first_name }} {{ $selectedChild->last_name }}</h2>
                <p class="text-[12px] text-slate-400 mt-0.5">{{ $selectedChild->academicClass->name ?? '—' }}</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/30 shrink-0">
                <span class="material-symbols-outlined text-[22px]">badge</span>
            </div>
        </div>

        @if($currentStatus === 'in_school')
        <div class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-emerald-50 border border-emerald-200/60">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <span class="material-symbols-outlined text-[20px]">school</span>
            </div>
            <div>
                <p class="text-[12.5px] font-extrabold text-emerald-800 pulse-dot pulse-green">En Classe</p>
                <p class="text-[11px] text-emerald-600 font-medium mt-0.5">
                    Entrée : {{ $lastScan?->occurred_at->format('H:i') }} · {{ $lastScan?->accessPoint?->name ?? '—' }}
                </p>
            </div>
        </div>
        @elseif($currentStatus === 'out_of_school')
        <div class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-slate-50 border border-slate-200">
            <div class="w-10 h-10 rounded-xl bg-slate-400 text-white flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[20px]">home</span>
            </div>
            <div>
                <p class="text-[12.5px] font-extrabold text-slate-700 pulse-dot pulse-slate">Hors École</p>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                    Sortie : {{ $lastScan?->occurred_at->format('H:i') }} · {{ $lastScan?->occurred_at->format('d/m') }}
                </p>
            </div>
        </div>
        @else
        <div class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-slate-50 border border-slate-200">
            <div class="w-10 h-10 rounded-xl bg-slate-300 text-white flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[20px]">help_outline</span>
            </div>
            <p class="text-[12px] font-bold text-slate-400">Aucune donnée</p>
        </div>
        @endif

        <p class="text-[10.5px] text-slate-300 font-medium mt-4 pt-3 border-t border-slate-100">
            <span class="material-symbols-outlined text-[12px] align-middle">schedule</span>
            Actualisé : {{ now()->format('H:i') }}
        </p>
    </div>

    {{-- 3 Stats --}}
    <div class="lg:col-span-3 grid grid-cols-3 gap-4">
        @php
            $stats = [
                ['label'=>'Présences','val'=>$weeklyStats['present'],'total'=>$weeklyStats['total'],'color'=>'emerald','icon'=>'event_available'],
                ['label'=>'Absences', 'val'=>$weeklyStats['absent'], 'total'=>$weeklyStats['total'],'color'=>'rose',   'icon'=>'event_busy'],
                ['label'=>'Retards',  'val'=>$weeklyStats['late'],   'total'=>$weeklyStats['total'],'color'=>'amber',  'icon'=>'alarm'],
            ];
        @endphp
        @foreach($stats as $s)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">{{ $s['label'] }}</span>
                <span class="w-8 h-8 rounded-xl bg-{{ $s['color'] }}-100 text-{{ $s['color'] }}-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">{{ $s['icon'] }}</span>
                </span>
            </div>
            <p class="text-3xl font-black text-{{ $s['color'] }}-500">{{ $s['val'] }}<span class="text-[15px] text-slate-300 font-bold">/{{ $s['total'] }}</span></p>
            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-{{ $s['color'] }}-400 rounded-full" style="width:{{ $s['total'] > 0 ? round($s['val']/$s['total']*100) : 0 }}%"></div>
            </div>
            <p class="text-[11px] text-slate-400 font-medium">Cette semaine</p>
        </div>
        @endforeach
    </div>
</div>

{{-- ══ MIDDLE ROW : IA INSIGHT + HEATMAP ══ --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-5">

    {{-- IA Security Insight --}}
    <div class="lg:col-span-3 rounded-2xl border border-indigo-200/40 bg-gradient-to-br from-[#0f1d4a] to-[#1a3481] text-white p-6 shadow-lg shadow-indigo-900/20 flex flex-col gap-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[20px] text-blue-200">security</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-blue-300/60 uppercase tracking-widest">IA Insight Sécurité</p>
                <p class="text-[15px] font-extrabold text-white mt-0.5">Rapport Hebdomadaire</p>
            </div>
        </div>

        <p class="text-[13px] text-blue-100/80 font-medium leading-relaxed">
            {!! nl2br(e($aiSecurityInsight ?? 'Aucune donnée disponible.')) !!}
        </p>

        {{-- Today's mini timeline --}}
        <div class="pt-4 border-t border-white/10">
            <p class="text-[10px] font-bold text-blue-300/50 uppercase tracking-widest mb-3">Passages d'aujourd'hui</p>
            @php $todayLogs = $accessLogs->filter(fn($l) => $l->occurred_at->isToday())->sortBy('occurred_at'); @endphp
            <div class="flex gap-2 flex-wrap">
                @forelse($todayLogs as $tl)
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-bold
                    {{ $tl->action === 'entry' ? 'bg-emerald-500/20 text-emerald-200 border border-emerald-400/20' : 'bg-white/10 text-slate-300 border border-white/10' }}">
                    <span class="material-symbols-outlined text-[13px]">{{ $tl->action === 'entry' ? 'login' : 'logout' }}</span>
                    {{ $tl->action === 'entry' ? 'Entrée' : 'Sortie' }} {{ $tl->occurred_at->format('H:i') }}
                </div>
                @empty
                <p class="text-blue-300/40 text-[12px] italic">Aucun scan enregistré aujourd'hui.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Weekly heatmap --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex flex-col gap-4">
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Calendrier Semaine</p>
            <p class="text-[14px] font-extrabold text-slate-800 mt-0.5">{{ now()->startOfWeek()->format('d') }} – {{ now()->endOfWeek()->format('d M Y') }}</p>
        </div>

        @php
            $weekDayLabels = ['Lun','Mar','Mer','Jeu','Ven'];
            $todayDow = now()->dayOfWeek; // 0=Sun, 1=Mon...6=Sat
            $todayIdx = ($todayDow === 0) ? 4 : $todayDow - 1;

            $entryDaysIdx = $accessLogs
                ->where('action','entry')
                ->filter(fn($l) => $l->occurred_at >= now()->startOfWeek() && $l->occurred_at <= now()->endOfWeek())
                ->map(fn($l) => $l->occurred_at->dayOfWeek === 0 ? 6 : $l->occurred_at->dayOfWeek - 1)
                ->unique()->values()->toArray();

            $lateDaysIdx = $accessLogs
                ->where('action','entry')
                ->filter(fn($l) => $l->occurred_at >= now()->startOfWeek() && $l->occurred_at <= now()->endOfWeek() && $l->occurred_at->hour >= 8 && $l->occurred_at->minute > 10)
                ->map(fn($l) => $l->occurred_at->dayOfWeek === 0 ? 6 : $l->occurred_at->dayOfWeek - 1)
                ->unique()->values()->toArray();
        @endphp

        <div class="grid grid-cols-5 gap-2">
            @foreach($weekDayLabels as $i => $dLabel)
            @php
                if ($i > $todayIdx)            { $cls = 'day-future';  $sym = '·'; }
                elseif (in_array($i,$lateDaysIdx)) { $cls = 'day-late';   $sym = '⏰'; }
                elseif (in_array($i,$entryDaysIdx)){ $cls = 'day-present';$sym = '✓'; }
                else                               { $cls = 'day-absent'; $sym = '✗'; }
            @endphp
            <div class="flex flex-col items-center gap-1.5">
                <span class="text-[10px] font-bold text-slate-400">{{ $dLabel }}</span>
                <div class="day-cell {{ $cls }}">{{ $sym }}</div>
            </div>
            @endforeach
        </div>

        <div class="border-t border-slate-100 pt-3 space-y-2">
            @foreach([['bg-emerald-400','Présent',$weeklyStats['present'],'text-emerald-600'],['bg-rose-400','Absent',$weeklyStats['absent'],'text-rose-500'],['bg-amber-400','Retard',$weeklyStats['late'],'text-amber-500']] as [$bg,$lbl,$val,$tc])
            <div class="flex items-center justify-between text-[12px]">
                <span class="text-slate-500 font-medium flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm {{ $bg }} inline-block"></span>{{ $lbl }}</span>
                <span class="font-extrabold {{ $tc }}">{{ $val }}j</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ══ HISTORIQUE DES PASSAGES ══ --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
     x-data="{ filter: 'all' }">

    {{-- Table header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-4 border-b border-slate-100">
        <div>
            <h3 class="text-[15px] font-extrabold text-slate-900">Historique des Passages</h3>
            <p class="text-[12px] text-slate-400 font-medium mt-0.5">{{ $accessLogs->count() }} enregistrement(s) · Badge RFID &amp; Scan</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1">
                <button @click="filter='all'"
                        :class="filter==='all' ? 'bg-white shadow text-slate-900 font-bold' : 'text-slate-500'"
                        class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition">Tout</button>
                <button @click="filter='entry'"
                        :class="filter==='entry' ? 'bg-white shadow text-emerald-700 font-bold' : 'text-slate-500'"
                        class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition">Entrées</button>
                <button @click="filter='exit'"
                        :class="filter==='exit' ? 'bg-white shadow text-rose-600 font-bold' : 'text-slate-500'"
                        class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition">Sorties</button>
            </div>
            <button class="flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 text-[12px] font-bold text-slate-600 hover:bg-slate-50 transition">
                <span class="material-symbols-outlined text-[15px]">download</span> Exporter
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Date &amp; Heure</th>
                    <th class="text-left px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Type</th>
                    <th class="text-left px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Point d'Accès</th>
                    <th class="text-left px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Badge / Méthode</th>
                    <th class="text-left px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($accessLogs as $log)
                @php
                    $isEntry     = $log->action === 'entry';
                    $isAuth      = $log->authorized ?? true;
                    $pointName   = $log->accessPoint?->name ?? 'Point inconnu';
                    $code        = $log->scanned_code ?? '';
                    $method      = str_starts_with($code,'BADGE-') ? 'Badge RFID'
                                 : (str_starts_with($code,'BUS-') || str_contains($code,'BOARD') ? 'Scan Bus' : 'QR Code');
                    $methodIcon  = $method === 'Badge RFID' ? 'nfc' : ($method === 'Scan Bus' ? 'directions_bus' : 'qr_code_scanner');
                    $pointIcon   = str_contains($pointName,'Bus')||str_contains($pointName,'bus') ? 'directions_bus'
                                 : (str_contains($pointName,'Cantine') ? 'restaurant' : 'door_front');
                @endphp
                <tr class="hover:bg-slate-50/60 transition" x-show="filter==='all' || filter==='{{ $log->action }}'">
                    <td class="px-6 py-3.5">
                        <p class="font-bold text-slate-800">{{ $log->occurred_at->format('d/m/Y') }}</p>
                        <p class="text-slate-400 text-[11px] font-semibold">{{ $log->occurred_at->format('H:i:s') }}</p>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11.5px] font-bold
                            {{ $isEntry ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            <span class="material-symbols-outlined text-[13px]">{{ $isEntry ? 'login' : 'logout' }}</span>
                            {{ $isEntry ? 'Entrée' : 'Sortie' }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[14px]">{{ $pointIcon }}</span>
                            </div>
                            <span class="font-semibold text-slate-700">{{ $pointName }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-1.5 text-slate-500 font-medium text-[12px]">
                            <span class="material-symbols-outlined text-[14px] text-slate-400">{{ $methodIcon }}</span>
                            {{ $method }}
                        </div>
                        <p class="text-[10px] text-slate-300 font-mono mt-0.5">{{ Str::limit($code, 18) }}</p>
                    </td>
                    <td class="px-4 py-3.5">
                        @if($isAuth)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-bold">
                            <span class="material-symbols-outlined text-[12px]">check_circle</span> Autorisé
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-rose-100 text-rose-700 text-[11px] font-bold">
                            <span class="material-symbols-outlined text-[12px]">cancel</span> Refusé
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-16 text-center">
                        <span class="material-symbols-outlined text-4xl text-slate-200 block mb-2">history</span>
                        <p class="text-slate-400 font-semibold">Aucun passage enregistré.</p>
                        <p class="text-slate-300 text-[12px] mt-1">Les scans badge/RFID apparaîtront ici automatiquement.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($accessLogs->count() > 0)
    <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
        <p class="text-[11.5px] text-slate-400 font-medium">{{ $accessLogs->count() }} passage(s) affichés</p>
        <a href="#" class="text-[12px] font-bold text-blue-600 hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-[14px]">history</span> Voir tout l'historique
        </a>
    </div>
    @endif
</div>

@endif
@endsection
