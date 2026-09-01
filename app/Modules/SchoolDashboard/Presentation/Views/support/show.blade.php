@extends('SchoolDashboard::layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_id)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('school.support') }}" class="w-10 h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition shadow-sm shrink-0">
            <i class="ph-bold ph-arrow-left text-lg"></i>
        </a>
        <div>
            <p class="text-[11px] font-mono font-bold text-slate-400">#{{ $ticket->ticket_id }}</p>
            <h2 class="text-xl font-bold text-slate-800 tracking-tight">{{ $ticket->subject }}</h2>
        </div>
    </div>

    @if (session('success'))
    <div class="bg-emerald-50 text-emerald-800 p-4 rounded-xl font-medium border border-emerald-200 flex items-center gap-2">
        <i class="ph-fill ph-check-circle text-lg"></i>
        {{ session('success') }}
    </div>
    @endif

    @php
        $statusColors = [
            'open'        => 'bg-rose-100 text-rose-700',
            'in_progress' => 'bg-amber-100 text-amber-800',
            'resolved'    => 'bg-emerald-100 text-emerald-700',
            'closed'      => 'bg-slate-100 text-slate-500',
        ];
        $statusLabels = ['open' => 'Ouvert', 'in_progress' => 'En cours', 'resolved' => 'Résolu', 'closed' => 'Clôturé'];
        $priorityLabels = \App\Modules\SchoolDashboard\Presentation\Controllers\SupportController::PRIORITIES;
    @endphp

    <div class="flex flex-wrap items-center gap-2">
        <span class="text-[11px] font-bold px-2.5 py-1 rounded-lg {{ $statusColors[$ticket->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
        <span class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600">{{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}</span>
        <span class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800">{{ $ticket->category }}</span>
        <span class="text-[11px] text-slate-400 ml-1">Ouvert le {{ $ticket->created_at->translatedFormat('d M Y à H:i') }}</span>
    </div>

    <!-- Fil de discussion -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
        @foreach($ticket->thread() as $msg)
            @if($msg->sender_type === 'support')
                <div class="flex items-start gap-3 max-w-xl">
                    <div class="w-9 h-9 rounded-full bg-[#031C5B] text-white flex items-center justify-center shrink-0 font-bold text-[13px]">SA</div>
                    <div>
                        <div class="flex items-baseline gap-2 mb-1.5">
                            <h4 class="text-[13px] font-bold text-[#111827]">{{ $msg->sender_name ?? 'Support AcademiaERP' }}</h4>
                            <span class="text-[11px] font-medium text-slate-400">{{ $msg->created_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        <div class="bg-blue-50 border border-blue-100 text-slate-700 text-[13.5px] font-medium leading-relaxed rounded-2xl rounded-tl-sm p-3.5">
                            {{ $msg->message }}
                        </div>
                    </div>
                </div>
            @else
                <div class="flex items-start gap-3 max-w-xl ml-auto justify-end">
                    <div>
                        <div class="flex items-baseline gap-2 mb-1.5 justify-end">
                            <span class="text-[11px] font-medium text-slate-400">{{ $msg->created_at->translatedFormat('d M Y, H:i') }}</span>
                            <h4 class="text-[13px] font-bold text-[#111827]">Vous</h4>
                        </div>
                        <div class="bg-[#031C5B] text-white text-[13.5px] font-medium leading-relaxed rounded-2xl rounded-tr-sm p-3.5">
                            {{ $msg->message }}
                        </div>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-slate-600 text-white flex items-center justify-center shrink-0 font-bold text-[13px]">
                        {{ strtoupper(substr($ticket->school_name, 0, 2)) }}
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Répondre -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <form action="{{ route('school.support.reply', $ticket->id) }}" method="POST">
            @csrf
            @if($errors->has('message'))
            <p class="text-rose-600 text-[12px] font-bold mb-2">{{ $errors->first('message') }}</p>
            @endif
            <textarea name="message" rows="3" required
                class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-xl px-4 py-3 outline-none focus:border-[#031C5B] focus:ring-1 focus:ring-[#031C5B] transition"
                placeholder="Écrire une réponse au support...">{{ old('message') }}</textarea>
            <div class="flex justify-end mt-3">
                <button type="submit" class="bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-6 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="ph-bold ph-paper-plane-right"></i> Envoyer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
