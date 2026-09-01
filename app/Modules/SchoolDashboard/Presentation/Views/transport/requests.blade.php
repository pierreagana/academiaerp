@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::transport._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Demandes d'Inscription — Bus</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Validez ou refusez les demandes d'inscription au transport scolaire.</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="flex items-center gap-2">
        @foreach(['pending' => 'En attente', 'approved' => 'Validées', 'rejected' => 'Refusées', 'withdrawn' => 'Retirées', 'all' => 'Toutes'] as $key => $label)
        <a href="{{ route('school.transport.requests', ['status' => $key]) }}" class="px-4 py-2 rounded-xl text-[13px] font-bold transition {{ $status === $key ? 'bg-[#031C5B] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                <thead>
                    <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-widest bg-slate-50 border-b border-slate-200">
                        <th class="py-3 px-5">Élève</th>
                        <th class="py-3 px-4">Arrêt</th>
                        <th class="py-3 px-4">Période</th>
                        <th class="py-3 px-4">Origine</th>
                        <th class="py-3 px-4">Demandé le</th>
                        <th class="py-3 px-5 text-center">Statut</th>
                        <th class="py-3 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[13px]">
                    @forelse($requests as $req)
                        @php
                            $badge = match($req->status) {
                                'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'rejected', 'withdrawn' => 'bg-red-100 text-red-700 border-red-200',
                                default => 'bg-amber-100 text-amber-800 border-amber-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-5 font-bold text-slate-900">{{ $req->student->first_name ?? '—' }} {{ $req->student->last_name ?? '' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $req->routeStop->name ?? '—' }} <span class="text-slate-400">({{ $req->routeStop->route->name ?? '—' }})</span></td>
                            <td class="py-3 px-4 text-slate-600">{{ $req->period === 'morning' ? 'Matin' : 'Soir' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $req->source === 'parent' ? 'Parent' : 'École' }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-5 text-center">
                                <span class="inline-flex items-center text-[11px] font-bold px-3 py-1 rounded-full {{ $badge }} border">{{ ucfirst($req->status) }}</span>
                                @if($req->status === 'rejected' && $req->rejection_reason)
                                    <p class="text-[10.5px] text-slate-400 mt-1">{{ $req->rejection_reason }}</p>
                                @endif
                            </td>
                            <td class="py-3 px-5 text-right">
                                @if($req->status === 'pending')
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('school.transport.requests.approve', $req->id) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-[11.5px] font-bold hover:bg-emerald-700">Valider</button>
                                    </form>
                                    <button type="button" onclick="document.getElementById('reject-{{ $req->id }}').classList.toggle('hidden')" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-[11.5px] font-bold hover:bg-red-100">Refuser</button>
                                </div>
                                <form id="reject-{{ $req->id }}" method="POST" action="{{ route('school.transport.requests.reject', $req->id) }}" class="hidden mt-2 flex items-center gap-2 justify-end">
                                    @csrf
                                    <input type="text" name="reason" placeholder="Motif (optionnel)" class="border border-slate-200 rounded-lg px-2.5 py-1.5 text-[11.5px] w-40">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-[11.5px] font-bold hover:bg-red-700">Confirmer</button>
                                </form>
                                @elseif($req->status === 'approved')
                                <form method="POST" action="{{ route('school.transport.requests.withdraw', $req->id) }}" onsubmit="return confirm('Retirer cet élève du service de transport ?');">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-[11.5px] font-bold hover:bg-red-50 hover:text-red-700">Retirer</button>
                                </form>
                                @else
                                <span class="text-slate-400 text-[11.5px]">{{ $req->reviewedBy->name ?? '—' }} · {{ $req->reviewed_at?->format('d/m/Y') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-slate-400 text-sm">Aucune demande.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
        <div class="p-4 border-t border-slate-200">{{ $requests->links() }}</div>
        @endif
    </div>
</div>
@endsection
