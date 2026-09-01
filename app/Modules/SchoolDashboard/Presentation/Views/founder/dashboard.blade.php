@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Mes Établissements</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Vue d'ensemble de toutes les écoles de votre groupe.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Établissements</p>
            <p class="text-[24px] font-extrabold text-[#031C5B]">{{ $totals['schools'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Élèves</p>
            <p class="text-[24px] font-extrabold text-[#031C5B]">{{ number_format($totals['students'], 0, ',', ' ') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Enseignants</p>
            <p class="text-[24px] font-extrabold text-[#031C5B]">{{ number_format($totals['teachers'], 0, ',', ' ') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Scolarité Collectée</p>
            <p class="text-[24px] font-extrabold text-emerald-600">{{ number_format($totals['collected'], 0, ',', ' ') }} <span class="text-[13px] font-bold text-slate-400">FCFA</span></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Établissement</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Code</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-center">Succursales</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-center">Élèves</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-center">Enseignants</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-right">Recouvrement Scolarité</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schools as $row)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-[14px] font-bold text-slate-700">{{ $row['school']->name }}</td>
                        <td class="px-5 py-4 text-[12.5px] text-slate-500 font-mono">{{ $row['school']->code ?: '-' }}</td>
                        <td class="px-5 py-4 text-[13px] text-slate-600 text-center">{{ $row['branches_count'] }}</td>
                        <td class="px-5 py-4 text-[13px] text-slate-600 text-center">{{ $row['students_count'] }}</td>
                        <td class="px-5 py-4 text-[13px] text-slate-600 text-center">{{ $row['teachers_count'] }}</td>
                        <td class="px-5 py-4 text-[13px] text-right">
                            <span class="font-bold text-slate-800">{{ number_format($row['totalCollected'], 0, ',', ' ') }}</span>
                            <span class="text-slate-400"> / {{ number_format($row['totalExpected'], 0, ',', ' ') }} FCFA</span>
                            <span class="ml-2 text-[11px] font-bold px-2 py-0.5 rounded-full {{ $row['collectionRate'] >= 70 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $row['collectionRate'] }}%</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-500 text-[13px]">Aucun établissement dans votre groupe pour le moment.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
