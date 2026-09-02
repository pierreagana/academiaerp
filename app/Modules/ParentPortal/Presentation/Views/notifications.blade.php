@extends('ParentPortal::layout')

@section('title', 'Messages & Notifications')

@section('content')

<!-- HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Messages & Notifications</h1>
        <p class="text-sm font-medium text-slate-500 mt-0.5">Communications directes de l'établissement, alertes de présence et bilans.</p>
    </div>

    <div>
        <form action="{{ route('parent.notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 px-3.5 py-2 rounded-xl transition">
                <span class="material-symbols-outlined text-[16px]">done_all</span>
                <span>Tout marquer comme lu</span>
            </button>
        </form>
    </div>
</div>

<!-- NOTIFICATIONS LIST -->
<div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] divide-y divide-slate-100 overflow-hidden">
    @forelse($notifications as $n)
        <div class="p-5 flex items-start justify-between gap-4 transition hover:bg-slate-50/60 {{ $n->read_at ? '' : 'bg-blue-50/30' }}">
            <div class="flex items-start gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 {{ $n->read_at ? 'bg-slate-100 text-slate-500' : 'bg-blue-600 text-white shadow-xs shadow-blue-500/30' }}">
                    <span class="material-symbols-outlined text-[20px]">{{ $n->read_at ? 'mark_email_read' : 'notifications_active' }}</span>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-extrabold text-slate-900 leading-tight">{{ $n->title }}</h3>
                        @if(!$n->read_at)
                            <span class="w-2 h-2 rounded-full bg-blue-600 shrink-0"></span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $n->body }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 mt-2">{{ $n->created_at->translatedFormat('d M Y à H:i') }}</p>
                </div>
            </div>

            @if(!$n->read_at)
                <form action="{{ route('parent.notifications.read', $n->id) }}" method="POST">
                    @csrf
                    <button type="submit" title="Marquer comme lu" class="text-slate-400 hover:text-blue-600 p-1.5 rounded-lg hover:bg-slate-100 transition shrink-0">
                        <span class="material-symbols-outlined text-[18px]">check</span>
                    </button>
                </form>
            @endif
        </div>
    @empty
        <div class="p-12 text-center text-slate-400 text-sm">
            <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center mx-auto mb-3">
                <span class="material-symbols-outlined text-[28px]">notifications_off</span>
            </div>
            <p class="font-bold text-slate-700 mb-1">Aucune notification</p>
            <p class="text-xs text-slate-400">Toutes vos alertes et communications apparaîtront ici.</p>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $notifications->links() }}
</div>

@endsection
