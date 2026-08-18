@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::finance._tabs')
    @include('SchoolDashboard::finance._fee_type_tabs')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Suivi des Paiements — {{ \App\Modules\Finance\Domain\Models\FeeLevel::TYPES[$type] }}</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Gérez et suivez les paiements {{ strtolower(\App\Modules\Finance\Domain\Models\FeeLevel::TYPES[$type]) }} des étudiants.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('school.finance.fees.payments.export', request()->query()) }}" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition">
                <i class="ph-bold ph-download-simple text-lg"></i>
                Export
            </a>
            @include('SchoolDashboard::finance._payment_modal')
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Filters -->
    <form action="{{ route('school.finance.fees.payments') }}" method="GET" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="space-y-1.5">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Recherche</label>
            <div class="relative">
                <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nom, ID Étudiant..." class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
            </div>
        </div>
        <div class="space-y-1.5">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Classe</label>
            <select name="class_id" onchange="this.form.submit()" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                <option value="">Toutes les classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ (string) $classId === (string) $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-1.5">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Statut</label>
            <select name="status" onchange="this.form.submit()" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                <option value="">Tous les statuts</option>
                <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Payé</option>
                <option value="partial" {{ $status === 'partial' ? 'selected' : '' }}>Partiel</option>
                <option value="late" {{ $status === 'late' ? 'selected' : '' }}>En retard</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="unconfigured" {{ $status === 'unconfigured' ? 'selected' : '' }}>Non configuré</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition">
                Filtrer
            </button>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Étudiant</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Classe</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Total {{ \App\Modules\Finance\Domain\Models\FeeLevel::TYPES[$type] }}</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Montant Payé</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Reste à Payer</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $s)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="font-extrabold text-[#0F172A] text-[14px]">{{ $s['student']->first_name }} {{ $s['student']->last_name }}</div>
                            <div class="text-[12px] text-slate-500 font-medium">ID: {{ $s['student']->roll_number ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $s['student']->academicClass->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ number_format($s['total'], 0, ',', ' ') }} FCFA</td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ number_format($s['paid'], 0, ',', ' ') }} FCFA</td>
                        <td class="px-5 py-4 text-[13px] font-bold {{ $s['remaining'] > 0 ? 'text-red-600' : 'text-slate-600' }}">{{ number_format($s['remaining'], 0, ',', ' ') }} FCFA</td>
                        <td class="px-5 py-4">
                            @php
                                $statusStyles = [
                                    'paid' => ['bg-[#A7F3D0] text-[#065F46]', 'Payé'],
                                    'partial' => ['bg-[#EDE9FE] text-[#5B21B6]', 'Partiel'],
                                    'late' => ['bg-[#FECDD3] text-[#9F1239]', 'En Retard'],
                                    'pending' => ['bg-slate-200 text-slate-700', 'En Attente'],
                                    'unconfigured' => ['bg-slate-100 text-slate-500', 'Non Configuré'],
                                ];
                                [$badgeClass, $badgeLabel] = $statusStyles[$s['status']] ?? ['bg-slate-100 text-slate-500', $s['status']];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[12px] font-bold {{ $badgeClass }}">{{ $badgeLabel }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('school.finance.fees.students.show', $s['student']->id) }}?type={{ $type }}" class="text-[#031C5B] font-bold text-[13px] hover:underline">Voir le dossier</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun étudiant trouvé.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="text-[13px] text-slate-500 font-semibold">
                Affichage {{ $students->firstItem() ?? 0 }} à {{ $students->lastItem() ?? 0 }} sur {{ number_format($students->total(), 0, ',', ' ') }} entrées
            </div>
            <div class="flex items-center">
                {{ $students->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
@endsection
