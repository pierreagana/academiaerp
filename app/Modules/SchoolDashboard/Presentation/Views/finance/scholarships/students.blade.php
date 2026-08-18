@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::finance.scholarships._tabs')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Élèves Boursiers</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Gérez l'ensemble des bénéficiaires de bourses.</p>
        </div>
        @include('SchoolDashboard::finance.scholarships._scholarship_modal', ['buttonLabel' => 'Nouvelle Attribution'])
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Filters -->
    <form action="{{ route('school.finance.scholarships.students') }}" method="GET" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="space-y-1.5">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Recherche</label>
            <div class="relative">
                <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nom, matricule..." class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
            </div>
        </div>
        <div class="space-y-1.5">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Type de Bourse</label>
            <select name="type_id" onchange="this.form.submit()" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                <option value="">Tous les types</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" {{ (string) $typeId === (string) $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-1.5">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Statut</label>
            <select name="status" onchange="this.form.submit()" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                <option value="">Tous les statuts</option>
                @foreach(\App\Modules\Finance\Domain\Models\Scholarship::STATUSES as $key => $label)
                    <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition">Filtrer</button>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Élève</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Classe</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Type de Bourse</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Montant Alloué</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Expiration</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($paginated as $s)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="font-extrabold text-[#0F172A] text-[14px]">{{ $s->student->first_name }} {{ $s->student->last_name }}</div>
                            <div class="text-[12px] text-slate-500">{{ $s->student->roll_number ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $s->student->academicClass->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $s->type->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ number_format($s->monthly_amount, 0, ',', ' ') }} FCFA / mois</td>
                        <td class="px-5 py-4">
                            @php
                                $statusStyles = [
                                    'active' => 'bg-emerald-50 text-emerald-700',
                                    'pending' => 'bg-violet-50 text-violet-700',
                                    'suspended' => 'bg-red-50 text-red-700',
                                    'rejected' => 'bg-slate-100 text-slate-500',
                                    'expired' => 'bg-slate-100 text-slate-500',
                                ];
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-bold {{ $statusStyles[$s->status] ?? 'bg-slate-100 text-slate-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ \App\Modules\Finance\Domain\Models\Scholarship::STATUSES[$s->status] ?? $s->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $s->expiry_date?->format('d M Y') ?? '-' }}</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('school.finance.scholarships.show', $s->id) }}" class="text-[#031C5B] font-bold text-[13px] hover:underline">Voir le dossier</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun boursier trouvé.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="text-[13px] text-slate-500 font-semibold">
                Affichage {{ $paginated->firstItem() ?? 0 }} à {{ $paginated->lastItem() ?? 0 }} sur {{ number_format($paginated->total(), 0, ',', ' ') }} élèves
            </div>
            <div class="flex items-center">
                {{ $paginated->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
@endsection
