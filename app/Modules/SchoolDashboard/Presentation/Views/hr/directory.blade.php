@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::hr._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Annuaire du Personnel</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Gérez les dossiers, contrats et statuts de l'ensemble des collaborateurs.</p>
    </div>

    <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3 flex-wrap">
        <div class="relative flex-1 min-w-[220px]">
            <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom, poste ou matricule..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[13px] rounded-lg pl-9 pr-3 py-2.5 outline-none focus:border-[#031C5B]">
        </div>
        <select name="department" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
            <option value="">Tous les Départements</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
        <select name="contract_type" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
            <option value="">Tous les Contrats</option>
            @foreach(\App\Modules\Academic\Domain\Models\Teacher::CONTRACT_TYPES as $value => $label)
                <option value="{{ $value }}" {{ request('contract_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">Filtrer</button>
        @if(request()->anyFilled(['search', 'department', 'contract_type']))
            <a href="{{ route('school.hr.directory') }}" class="text-[12.5px] font-bold text-slate-500 hover:text-slate-700">Réinitialiser</a>
        @endif
    </form>

    @php
        $contractStyles = ['cdi' => 'bg-emerald-100 text-emerald-700', 'cdd' => 'bg-amber-100 text-amber-700', 'prestataire' => 'bg-blue-100 text-blue-700'];
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($employees as $employee)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-[#031C5B]/10 text-[#031C5B] flex items-center justify-center font-bold text-[13px] shrink-0 overflow-hidden">
                        @if($employee['photo_path'])
                            <img src="{{ asset('storage/' . $employee['photo_path']) }}" class="w-full h-full object-cover">
                        @else
                            {{ mb_substr($employee['name'], 0, 1) }}
                        @endif
                    </div>
                    <div>
                        <p class="text-[14.5px] font-bold text-slate-800">{{ $employee['name'] }}</p>
                        <p class="text-[12px] text-slate-500">{{ $employee['title'] }}</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold uppercase {{ $contractStyles[$employee['contract_type']] ?? '' }}">
                    {{ \App\Modules\Academic\Domain\Models\Teacher::CONTRACT_TYPES[$employee['contract_type']] ?? $employee['contract_type'] }}
                </span>
            </div>
            <div class="grid grid-cols-2 gap-3 bg-slate-50 rounded-xl p-3 mb-4">
                <div>
                    <p class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Département</p>
                    <p class="text-[13px] font-semibold text-slate-700 mt-0.5">{{ $employee['department'] ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Fin de Contrat</p>
                    <p class="text-[13px] font-semibold {{ $employee['contract_end_date'] ? 'text-red-600' : 'text-slate-700' }} mt-0.5">
                        {{ $employee['contract_end_date'] ? \Illuminate\Support\Carbon::parse($employee['contract_end_date'])->format('d M Y') : 'N/A (Permanent)' }}
                    </p>
                </div>
            </div>
            <a href="{{ $employee['show_url'] }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-slate-200 text-[#031C5B] rounded-xl text-[13px] font-bold hover:bg-slate-50 transition">
                Voir le Dossier <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-500 font-medium">Aucun employé trouvé.</div>
        @endforelse
    </div>
</div>
@endsection
