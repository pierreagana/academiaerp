@extends('SuperAdmin::layouts.app')

@section('content')

    {{-- Flash messages --}}
    @if(session('success'))
    <div id="successToast" class="fixed top-6 right-6 z-[9999] flex items-center gap-3 bg-emerald-600 text-white px-5 py-3.5 rounded-2xl shadow-xl text-[14px] font-bold animate-fade-in">
        <i class="ph ph-check-circle text-xl shrink-0"></i>
        <span>{{ session('success') }}</span>
        <button onclick="document.getElementById('successToast').remove()" class="ml-2 text-white/70 hover:text-white transition">
            <i class="ph ph-x"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div id="errorToast" class="fixed top-6 right-6 z-[9999] flex items-center gap-3 bg-rose-600 text-white px-5 py-3.5 rounded-2xl shadow-xl text-[14px] font-bold">
        <i class="ph ph-warning text-xl shrink-0"></i>
        <span>{{ session('error') }}</span>
        <button onclick="document.getElementById('errorToast').remove()" class="ml-2 text-white/70 hover:text-white transition">
            <i class="ph ph-x"></i>
        </button>
    </div>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[28px] font-extrabold text-[#111827]">Centre de Support</h2>
            <p class="text-[15px] text-slate-500 mt-1">
                Gestion centralisée des requêtes des établissements ·
                <span class="font-bold text-[#031C5B]">{{ $tickets->count() }} ticket{{ $tickets->count() > 1 ? 's' : '' }}</span> en base
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3 shrink-0 mt-2 md:mt-0">
            <a href="{{ route('superadmin.support') }}"
               class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-lg text-[13px] font-bold hover:bg-slate-50 transition shadow-sm">
                <i class="ph ph-arrows-clockwise"></i> Actualiser
            </a>
        </div>
    </div>

    {{-- Top KPIs — 100% dynamiques depuis la BD --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">

        {{-- KPI 1 : Tickets Ouverts --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">TICKETS OUVERTS</h3>
                <div class="w-10 h-10 rounded-xl bg-[#031C5B] text-white flex items-center justify-center shrink-0 shadow-inner">
                    <i class="ph ph-ticket text-xl"></i>
                </div>
            </div>
            <div class="flex items-end gap-3 mt-auto">
                <h2 class="text-[40px] font-extrabold text-slate-900 leading-none">{{ $kpis['open_tickets'] }}</h2>
                @if($kpis['open_tickets'] > 0)
                <span class="inline-flex items-center gap-0.5 bg-[#FEF2F2] text-[#DC2626] text-[11px] font-bold px-2 py-1 rounded-full mb-1">
                    <i class="ph ph-warning"></i> Actif
                </span>
                @else
                <span class="inline-flex items-center gap-0.5 bg-emerald-50 text-emerald-700 text-[11px] font-bold px-2 py-1 rounded-full mb-1">
                    <i class="ph ph-check"></i> Tous traités
                </span>
                @endif
            </div>
        </div>

        {{-- KPI 2 : En Cours --}}
        <div class="bg-white rounded-2xl border border-[#E9D5FF] shadow-sm p-6 flex flex-col relative overflow-hidden">
            <i class="ph-fill ph-sparkle absolute right-10 top-4 text-4xl text-purple-100"></i>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <h3 class="text-[11px] font-bold text-[#7C3AED] uppercase tracking-widest leading-tight">EN<br>COURS</h3>
                <div class="w-10 h-10 rounded-xl bg-[#F5F3FF] text-[#7C3AED] flex items-center justify-center shrink-0 border border-[#E9D5FF]">
                    <i class="ph ph-clock-countdown text-xl"></i>
                </div>
            </div>
            <div class="flex items-end gap-3 mt-auto relative z-10">
                <h2 class="text-[40px] font-extrabold text-slate-900 leading-none">{{ $kpis['in_progress'] }}</h2>
                <span class="text-[12px] font-medium text-slate-500 mb-1">en traitement</span>
            </div>
        </div>

        {{-- KPI 3 : Répartition par Catégorie --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">RÉPARTITION</h3>
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                    <i class="ph ph-chart-pie-slice text-xl"></i>
                </div>
            </div>
            <div class="mt-auto space-y-3">
                @forelse($categoryDistribution->take(3) as $cat => $stat)
                @php
                    $colors = ['Technique' => '#031C5B', 'Commercial' => '#7C3AED', 'Facturation' => '#D97706', 'Assistance' => '#059669'];
                    $color = $colors[$cat] ?? '#64748B';
                @endphp
                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-[12px] font-bold" style="color: {{ $color }}">{{ $cat }}</span>
                        <span class="text-[12px] font-bold text-slate-600">{{ $stat['percent'] }}%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700" style="width: {{ $stat['percent'] }}%; background-color: {{ $color }}"></div>
                    </div>
                </div>
                @empty
                <p class="text-[12px] text-slate-400">Aucun ticket</p>
                @endforelse
            </div>
        </div>

        {{-- KPI 4 : Priorité Critique --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">PRIORITÉ CRITIQUE</h3>
                <div class="w-10 h-10 rounded-xl bg-[#FEF2F2] text-[#DC2626] flex items-center justify-center shrink-0 border border-[#FECACA]">
                    <i class="ph ph-warning text-xl"></i>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-auto">
                <h2 class="text-[40px] font-extrabold {{ $kpis['critical_pending'] > 0 ? 'text-[#DC2626]' : 'text-slate-400' }} leading-none">
                    {{ $kpis['critical_pending'] }}
                </h2>
                <p class="text-[13px] font-medium text-slate-500 leading-tight">
                    ticket{{ $kpis['critical_pending'] !== 1 ? 's' : '' }}<br>critique{{ $kpis['critical_pending'] !== 1 ? 's' : '' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Main Layout --}}
    <div class="flex flex-col lg:flex-row gap-6 mb-8" style="min-height: 650px;">

        {{-- Left: File d'attente — 100% données BD --}}
        <div class="w-full lg:w-[340px] bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col shrink-0">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-[18px] font-extrabold text-[#111827]">
                    File d'attente
                    <span class="ml-2 text-[13px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ $tickets->count() }}</span>
                </h3>
            </div>

            <div class="flex-1 overflow-y-auto p-3 space-y-2 bg-[#F8FAFC]">
                @forelse($tickets as $ticket)
                @php
                    $isActive = $activeTicket && $activeTicket->id === $ticket->id;
                    $bgClass  = $isActive
                        ? 'bg-blue-50 border-2 border-[#93C5FD] shadow-md'
                        : 'bg-white border border-slate-200 hover:border-slate-300 hover:shadow-sm';

                    $priorityMap = [
                        'critical' => ['bg-[#FEE2E2]', 'text-[#991B1B]', '🔴'],
                        'high'     => ['bg-[#FEE2E2]', 'text-[#991B1B]', '🟠'],
                        'normal'   => ['bg-slate-100',  'text-slate-600',  '🔵'],
                        'low'      => ['bg-[#D1FAE5]',  'text-[#065F46]',  '🟢'],
                    ];
                    $pm = $priorityMap[$ticket->priority] ?? ['bg-slate-100', 'text-slate-600', '⚪'];

                    $statusMap = [
                        'open'        => ['bg-rose-100 text-rose-700',    'Ouvert'],
                        'in_progress' => ['bg-amber-100 text-amber-700',  'En cours'],
                        'resolved'    => ['bg-emerald-100 text-emerald-700', 'Résolu'],
                        'closed'      => ['bg-slate-100 text-slate-500',  'Clôturé'],
                    ];
                    $sm = $statusMap[$ticket->status] ?? ['bg-slate-100 text-slate-600', $ticket->status];
                @endphp

                <a href="{{ route('superadmin.support', ['ticket' => $ticket->id]) }}"
                   class="{{ $bgClass }} rounded-xl p-4 cursor-pointer transition block">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1 min-w-0 pr-2">
                            <h4 class="text-[13px] font-bold {{ $isActive ? 'text-[#031C5B]' : 'text-[#111827]' }} leading-tight truncate">
                                {{ $ticket->subject }}
                            </h4>
                            <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                                {{ $ticket->school_name }} · <span class="font-mono">#{{ $ticket->ticket_id }}</span>
                            </p>
                        </div>
                        <span class="text-[10px] text-slate-400 shrink-0">{{ $ticket->created_at->format('d/m H:i') }}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="bg-[#DBEAFE] text-[#1E3A8A] text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">{{ $ticket->category }}</span>
                        <span class="{{ $pm[0] }} {{ $pm[1] }} text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">{{ strtoupper($ticket->priority) }}</span>
                        <span class="{{ $sm[0] }} text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $sm[1] }}</span>
                    </div>
                </a>
                @empty
                <div class="text-center py-12 text-slate-400">
                    <i class="ph ph-ticket text-5xl block mb-3"></i>
                    <p class="font-bold">Aucun ticket de support</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Right: Ticket Details Panel — dynamique selon le ticket actif --}}
        <div class="flex-1 bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col min-w-0">

            @if($activeTicket)
            @php
                $priorityLabels = [
                    'critical' => ['text-[#DC2626]', '🔴 Critique !'],
                    'high'     => ['text-[#DC2626]', '🟠 Haute'],
                    'normal'   => ['text-slate-700',  '🔵 Normale'],
                    'low'      => ['text-emerald-700','🟢 Basse'],
                ];
                $pl = $priorityLabels[$activeTicket->priority] ?? ['text-slate-700', $activeTicket->priority];

                $statusBadge = [
                    'open'        => 'bg-rose-100 text-rose-700 border-rose-200',
                    'in_progress' => 'bg-amber-100 text-amber-700 border-amber-200',
                    'resolved'    => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'closed'      => 'bg-slate-100 text-slate-600 border-slate-200',
                ];
                $sb = $statusBadge[$activeTicket->status] ?? 'bg-slate-100 text-slate-600 border-slate-200';

                $statusLabels = ['open' => 'Ouvert', 'in_progress' => 'En cours', 'resolved' => 'Résolu', 'closed' => 'Clôturé'];
                $slabel = $statusLabels[$activeTicket->status] ?? $activeTicket->status;
            @endphp

            {{-- Ticket Header --}}
            <div class="p-6 border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-[13px] font-mono font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg">#{{ $activeTicket->ticket_id }}</span>
                        <span class="text-slate-400">·</span>
                        <span class="text-[13px] font-bold text-slate-600">{{ $activeTicket->school_name }}</span>
                        <span class="text-[12px] font-bold {{ $sb }} border px-2.5 py-0.5 rounded-full">{{ $slabel }}</span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        {{-- Bouton Résoudre (uniquement si pas encore résolu) --}}
                        @if(!in_array($activeTicket->status, ['resolved', 'closed']))
                        <form action="{{ route('superadmin.support.close', $activeTicket->id) }}" method="POST"
                              onsubmit="return confirm('Marquer ce ticket comme résolu ?');">
                            @csrf
                            <button type="submit"
                                    class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-[13px] font-bold hover:bg-emerald-100 transition cursor-pointer">
                                <i class="ph ph-check-circle text-lg"></i> Résoudre
                            </button>
                        </form>
                        @else
                        <span class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 text-[13px] font-bold">
                            <i class="ph ph-check-circle text-lg"></i> Clôturé
                        </span>
                        @endif
                    </div>
                </div>

                <h2 class="text-[22px] font-extrabold text-[#031C5B] leading-tight mb-5">
                    {{ $activeTicket->subject }}
                </h2>

                <div class="bg-[#F8FAFC] border border-slate-200 rounded-xl p-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-[12px]">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">CATÉGORIE</span>
                        <span class="font-bold text-slate-800">{{ $activeTicket->category ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">PRIORITÉ</span>
                        <span class="font-bold {{ $pl[0] }}">{{ $pl[1] }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">STATUT</span>
                        <span class="font-bold text-slate-800">{{ $slabel }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">SOUMIS LE</span>
                        <span class="font-bold text-slate-800 font-mono">{{ $activeTicket->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            {{-- Messages / Description --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-white">

                {{-- Message original de l'école --}}
                <div class="flex items-start gap-4 max-w-3xl">
                    <div class="w-10 h-10 rounded-full bg-[#031C5B] text-white flex items-center justify-center shrink-0 font-bold text-[14px]">
                        {{ strtoupper(substr($activeTicket->school_name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-baseline gap-2 mb-2">
                            <h4 class="text-[14px] font-bold text-[#111827]">{{ $activeTicket->school_name }}</h4>
                            <span class="text-[11px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">Demandeur</span>
                            <span class="text-[12px] font-medium text-slate-400">{{ $activeTicket->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="bg-white border border-slate-200 text-slate-700 text-[14px] font-medium leading-relaxed rounded-2xl rounded-tl-sm p-4 shadow-sm">
                            {{ $activeTicket->description ?: 'Aucune description fournie.' }}
                        </div>
                    </div>
                </div>

                @if(in_array($activeTicket->status, ['resolved', 'closed']))
                <div class="flex items-start gap-4 max-w-3xl ml-auto justify-end">
                    <div>
                        <div class="flex items-baseline gap-2 mb-2 justify-end">
                            <span class="text-[12px] font-medium text-slate-400">Support AcademiaERP</span>
                            <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Résolu</span>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-[14px] font-medium leading-relaxed rounded-2xl rounded-tr-sm p-4">
                            Ticket marqué comme résolu par l'équipe support AcademiaERP. Si le problème persiste, n'hésitez pas à rouvrir un nouveau ticket.
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 font-bold text-[14px]">
                        SA
                    </div>
                </div>
                @endif

            </div>

            {{-- Reply Box — form fonctionnel --}}
            @if(!in_array($activeTicket->status, ['resolved', 'closed']))
            <div class="p-5 border-t border-slate-200 bg-[#F8FAFC] rounded-b-2xl">
                <form action="{{ route('superadmin.support.reply', $activeTicket->id) }}" method="POST">
                    @csrf
                    @if(isset($errors) && $errors->has('reply'))
                    <p class="text-rose-600 text-[12px] font-bold mb-2">{{ $errors->first('reply') }}</p>
                    @endif
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm
                                focus-within:border-[#031C5B] focus-within:ring-1 focus-within:ring-[#031C5B] transition">
                        <textarea name="reply" rows="3"
                                  placeholder="Tapez votre réponse ici..."
                                  class="w-full p-4 text-[14px] font-medium text-slate-700 outline-none resize-none bg-transparent"
                                  required>{{ old('reply') }}</textarea>

                        <div class="px-4 py-3 bg-[#F8FAFC] border-t border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-slate-400 font-medium">Réponse au ticket <span class="font-mono font-bold text-slate-600">#{{ $activeTicket->ticket_id }}</span></span>
                                <button type="button" id="btnAiDraft" onclick="generateDraft({{ $activeTicket->id }})"
                                        class="flex items-center gap-1.5 ml-3 bg-[#F5F3FF] text-[#7C3AED] px-3 py-1.5 rounded-lg text-[12px] font-bold hover:bg-[#EDE9FE] transition cursor-pointer">
                                    <i class="ph ph-sparkle-fill"></i> Brouillon IA
                                </button>
                                <span id="aiLoading" class="text-[11px] font-bold text-[#7C3AED] ml-2 hidden animate-pulse">
                                    Génération en cours...
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-[12px] font-medium text-slate-500">
                                    <i class="ph ph-eye text-lg align-middle"></i> Message public
                                </span>
                                <button type="submit"
                                        class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-lg text-[13px] font-bold hover:bg-blue-900 transition cursor-pointer">
                                    Envoyer <i class="ph ph-paper-plane-right text-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <script>
            function generateDraft(ticketId) {
                const btn = document.getElementById('btnAiDraft');
                const loader = document.getElementById('aiLoading');
                const textarea = document.querySelector('textarea[name="reply"]');
                
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                loader.classList.remove('hidden');
                
                fetch(`/superadmin/support/${ticketId}/ai-draft`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.draft) {
                        textarea.value = data.draft;
                    } else {
                        alert("Erreur: Impossible de générer le brouillon.");
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert("Une erreur de communication avec le serveur est survenue.");
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    loader.classList.add('hidden');
                });
            }
            </script>
            @else
            <div class="p-5 border-t border-slate-100 bg-emerald-50/50 rounded-b-2xl flex items-center justify-between">
                <span class="text-[13px] text-emerald-700 font-bold flex items-center gap-2">
                    <i class="ph ph-check-circle text-xl"></i> Ticket résolu — aucune action requise
                </span>
                <a href="{{ route('superadmin.support') }}"
                   class="text-[13px] font-bold text-[#031C5B] hover:underline flex items-center gap-1">
                    <i class="ph ph-arrow-left"></i> Retour à la liste
                </a>
            </div>
            @endif

            @else
            {{-- No ticket selected --}}
            <div class="flex-1 flex flex-col items-center justify-center text-center p-12 text-slate-400">
                <i class="ph ph-ticket text-[72px] mb-4 text-slate-200"></i>
                <p class="text-[18px] font-bold text-slate-400 mb-2">Aucun ticket sélectionné</p>
                <p class="text-[14px]">Cliquez sur un ticket dans la file d'attente pour voir les détails.</p>
            </div>
            @endif

        </div>
    </div>

    {{-- Tableau récapitulatif complet --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-[17px] font-extrabold text-[#111827]">Tous les Tickets</h3>
                <p class="text-[12px] text-slate-500 mt-0.5">{{ $tickets->count() }} ticket(s) enregistré(s) en base de données</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">ID Ticket</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">École</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Sujet</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Catégorie</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Priorité</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Statut</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Date</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tickets as $ticket)
                    @php
                        $rowPriorityColors = [
                            'critical' => 'bg-[#FEE2E2] text-[#991B1B]',
                            'high'     => 'bg-orange-100 text-orange-800',
                            'normal'   => 'bg-slate-100 text-slate-600',
                            'low'      => 'bg-emerald-100 text-emerald-700',
                        ];
                        $rpc = $rowPriorityColors[$ticket->priority] ?? 'bg-slate-100 text-slate-600';

                        $rowStatusColors = [
                            'open'        => 'bg-rose-100 text-rose-700',
                            'in_progress' => 'bg-amber-100 text-amber-800',
                            'resolved'    => 'bg-emerald-100 text-emerald-700',
                            'closed'      => 'bg-slate-100 text-slate-500',
                        ];
                        $rsc = $rowStatusColors[$ticket->status] ?? 'bg-slate-100 text-slate-600';
                        $rsLabel = ['open' => 'Ouvert', 'in_progress' => 'En cours', 'resolved' => 'Résolu', 'closed' => 'Clôturé'][$ticket->status] ?? $ticket->status;
                    @endphp
                    <tr class="hover:bg-slate-50 transition {{ $activeTicket?->id === $ticket->id ? 'bg-blue-50/50' : '' }}">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('superadmin.support', ['ticket' => $ticket->id]) }}"
                               class="font-mono font-bold text-[#031C5B] hover:underline">
                                #{{ $ticket->ticket_id }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5 font-medium text-slate-800 whitespace-nowrap">{{ $ticket->school_name }}</td>
                        <td class="px-5 py-3.5 text-slate-600 max-w-[240px] truncate">{{ $ticket->subject }}</td>
                        <td class="px-5 py-3.5">
                            <span class="bg-blue-50 text-blue-800 font-bold text-[11px] px-2 py-0.5 rounded">{{ $ticket->category }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="{{ $rpc }} font-bold text-[11px] px-2.5 py-0.5 rounded-full uppercase">{{ $ticket->priority }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="{{ $rsc }} font-bold text-[11px] px-2.5 py-0.5 rounded-full">{{ $rsLabel }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 font-medium whitespace-nowrap">
                            {{ $ticket->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('superadmin.support', ['ticket' => $ticket->id]) }}"
                                   class="text-[12px] font-bold text-[#031C5B] hover:bg-blue-50 px-2.5 py-1 rounded-lg transition flex items-center gap-1">
                                    <i class="ph ph-eye"></i> Voir
                                </a>
                                @if(!in_array($ticket->status, ['resolved', 'closed']))
                                <form action="{{ route('superadmin.support.close', $ticket->id) }}" method="POST"
                                      onsubmit="return confirm('Résoudre le ticket #{{ $ticket->ticket_id }} ?');" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="text-[12px] font-bold text-emerald-700 hover:bg-emerald-50 px-2.5 py-1 rounded-lg transition flex items-center gap-1 cursor-pointer">
                                        <i class="ph ph-check-circle"></i> Résoudre
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                            <i class="ph ph-ticket text-4xl block mb-2"></i>
                            Aucun ticket de support en base de données.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
