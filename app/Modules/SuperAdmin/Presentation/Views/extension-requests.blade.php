@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[32px] font-extrabold text-[#111827]">Demandes d'Extensions</h2>
            <p class="text-[15px] text-slate-500 mt-1">Modules payants demandés par les établissements en plus de leur forfait.</p>
        </div>
        @if($pendingCount > 0)
            <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold px-3 py-1.5 rounded-full">
                {{ $pendingCount }} en attente
            </span>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl shadow-sm">
            <i class="ph ph-check-circle text-emerald-600 text-xl"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-6">Établissement</th>
                        <th class="py-3.5 px-4">Module Demandé</th>
                        <th class="py-3.5 px-4">Prix</th>
                        <th class="py-3.5 px-4">Demandé le</th>
                        <th class="py-3.5 px-4">Statut</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($requests as $req)
                        @php
                            $module = \App\Modules\SuperAdmin\Domain\Models\SaasModule::where('name', $req->module_name)->first();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-bold text-slate-900">{{ $req->school->name ?? '—' }}</td>
                            <td class="py-4 px-4 font-medium">{{ $req->module_name }}</td>
                            <td class="py-4 px-4 font-medium">{{ $module ? number_format($module->price, 0, ',', ' ') . ' FCFA/an' : '—' }}</td>
                            <td class="py-4 px-4 font-medium text-slate-500">{{ $req->created_at->format('d M Y') }}</td>
                            <td class="py-4 px-4">
                                @if($req->status === 'pending')
                                    <span class="inline-flex items-center bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-bold px-2.5 py-1 rounded-full">En attente</span>
                                @elseif($req->status === 'approved')
                                    <span class="inline-flex items-center bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold px-2.5 py-1 rounded-full">Approuvée</span>
                                @else
                                    <span class="inline-flex items-center bg-slate-100 text-slate-500 border border-slate-200 text-[11px] font-bold px-2.5 py-1 rounded-full">Refusée</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                @if($req->status === 'pending')
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('superadmin.extension-requests.approve', $req->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white rounded-lg transition">Approuver</button>
                                        </form>
                                        <form action="{{ route('superadmin.extension-requests.reject', $req->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 text-[11px] font-bold text-red-700 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition">Refuser</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400">
                                        {{ $req->decidedBy?->name ? 'par ' . $req->decidedBy->name : '' }}
                                        {{ $req->decided_at?->format('d M Y') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-6 text-center text-slate-400 text-[13px]">Aucune demande d'extension pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
