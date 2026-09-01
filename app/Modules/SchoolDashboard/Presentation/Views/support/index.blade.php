@extends('SchoolDashboard::layouts.app')

@section('title', 'Support')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Support AcademiaERP</h2>
        <p class="text-[13.5px] text-slate-500 mt-1">Contactez notre équipe pour toute question technique, commerciale ou de facturation.</p>
    </div>

    @if (session('success'))
    <div class="bg-emerald-50 text-emerald-800 p-4 rounded-xl font-medium border border-emerald-200 flex items-center gap-2">
        <i class="ph-fill ph-check-circle text-lg"></i>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 text-sm text-red-800 rounded-xl bg-red-50 border border-red-200">
        <div class="flex items-center gap-2 mb-2 font-bold">
            <i class="ph-fill ph-warning-circle text-lg"></i> Veuillez corriger les erreurs suivantes :
        </div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Nouveau ticket -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden" x-data="{ open: {{ $tickets->isEmpty() ? 'true' : 'false' }} }">
        <button type="button" @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between text-left">
            <h3 class="text-[15px] font-extrabold text-slate-900 flex items-center gap-2">
                <i class="ph-fill ph-headset text-[#031C5B]"></i> Ouvrir un nouveau ticket
            </h3>
            <i class="ph-bold ph-caret-down text-slate-400 transition-transform" :class="{ 'rotate-180': open }"></i>
        </button>

        <div x-show="open" x-collapse class="px-6 pb-6 border-t border-slate-100 pt-5">
            <form action="{{ route('school.support.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Sujet <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="255"
                        class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-1 focus:ring-[#031C5B] transition shadow-sm"
                        placeholder="Ex : Bulletin PDF incomplet pour la 6ème A">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Catégorie <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-1 focus:ring-[#031C5B] transition shadow-sm cursor-pointer">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Priorité <span class="text-red-500">*</span></label>
                        <select name="priority" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-1 focus:ring-[#031C5B] transition shadow-sm cursor-pointer">
                            @foreach($priorities as $key => $label)
                                <option value="{{ $key }}" {{ old('priority', 'normal') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Description <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="4" required maxlength="2000"
                        class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-1 focus:ring-[#031C5B] transition shadow-sm"
                        placeholder="Décrivez votre problème ou votre demande en détail...">{{ old('description') }}</textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-6 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
                        <i class="ph-bold ph-paper-plane-right"></i> Envoyer le ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mes tickets -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-[15px] font-extrabold text-slate-900">Mes tickets ({{ $tickets->count() }})</h3>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($tickets as $ticket)
                @php
                    $priorityColors = [
                        'critical' => 'bg-[#FEE2E2] text-[#991B1B]',
                        'high'     => 'bg-orange-100 text-orange-800',
                        'normal'   => 'bg-slate-100 text-slate-600',
                        'low'      => 'bg-emerald-100 text-emerald-700',
                    ];
                    $statusColors = [
                        'open'        => 'bg-rose-100 text-rose-700',
                        'in_progress' => 'bg-amber-100 text-amber-800',
                        'resolved'    => 'bg-emerald-100 text-emerald-700',
                        'closed'      => 'bg-slate-100 text-slate-500',
                    ];
                    $statusLabels = ['open' => 'Ouvert', 'in_progress' => 'En cours', 'resolved' => 'Résolu', 'closed' => 'Clôturé'];
                @endphp
                <a href="{{ route('school.support.show', $ticket->id) }}" class="block p-5 hover:bg-slate-50/60 transition">
                    <div class="flex items-start justify-between gap-3 mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-mono font-bold text-slate-400">#{{ $ticket->ticket_id }}</span>
                            <h4 class="text-[14px] font-bold text-slate-800">{{ $ticket->subject }}</h4>
                        </div>
                        <span class="shrink-0 text-[10px] font-bold text-slate-400">{{ $ticket->created_at->translatedFormat('d M Y') }}</span>
                    </div>
                    <p class="text-[13px] text-slate-500 mb-3">{{ $ticket->description }}</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $statusColors[$ticket->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $priorityColors[$ticket->priority] ?? 'bg-slate-100 text-slate-600' }}">{{ \App\Modules\SchoolDashboard\Presentation\Controllers\SupportController::PRIORITIES[$ticket->priority] ?? $ticket->priority }}</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-800">{{ $ticket->category }}</span>
                        @if($ticket->messages_count > 0)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-purple-50 text-purple-700 flex items-center gap-1"><i class="ph-fill ph-chat-circle-text"></i> {{ $ticket->messages_count }} réponse(s)</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="p-10 text-center">
                    <i class="ph-bold ph-headset text-slate-300 text-3xl mb-2"></i>
                    <p class="text-[13px] text-slate-400">Vous n'avez encore ouvert aucun ticket de support.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
