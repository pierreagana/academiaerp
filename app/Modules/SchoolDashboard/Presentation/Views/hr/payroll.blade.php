@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::hr._tabs')

    @php
        $totalGross = $roster->sum('salary');
        $totalPaid = $roster->where('paid', true)->count();
        $totalPending = $roster->where('paid', false)->count();
    @endphp

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Traitement de la Paie</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Période : {{ now()->translatedFormat('F Y') }} · {{ $roster->count() }} Employés</p>
        </div>
        <form method="POST" action="{{ route('school.hr.payroll.run') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
                <i class="ph-bold ph-play"></i> Lancer la Paie
            </button>
        </form>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Masse Salariale Brute</p>
            <h3 class="text-2xl font-bold text-slate-800">{{ number_format($totalGross, 0, ',', ' ') }} FCFA</h3>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Payé ce Mois</p>
            <h3 class="text-2xl font-bold text-emerald-600">{{ $totalPaid }}</h3>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">En Attente</p>
            <h3 class="text-2xl font-bold text-amber-600">{{ $totalPending }}</h3>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Employé</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Salaire de Base</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($roster as $employee)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#031C5B]/10 text-[#031C5B] flex items-center justify-center font-bold text-[11px] shrink-0 overflow-hidden">
                                    @if($employee['photo_path'])
                                        <img src="{{ asset('storage/' . $employee['photo_path']) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ mb_substr($employee['name'], 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <p class="text-[13.5px] font-bold text-slate-800">{{ $employee['name'] }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $employee['role'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-[13.5px] font-bold text-slate-800">{{ number_format($employee['salary'], 0, ',', ' ') }} F</td>
                        <td class="px-5 py-4">
                            @if($employee['paid'])
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700">Payé</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700">En Attente</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun employé salarié actif. Renseignez un salaire dans l'Annuaire ou la fiche employé.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
