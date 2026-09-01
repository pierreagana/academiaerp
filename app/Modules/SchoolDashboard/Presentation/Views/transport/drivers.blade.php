@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: false }">
    @include('SchoolDashboard::transport._tabs')

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Chauffeurs</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Gérez les chauffeurs et leurs documents.</p>
        </div>
        <button @click="createOpen = true" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
            <i class="ph-bold ph-plus"></i> Ajouter un Chauffeur
        </button>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="p-4 text-sm text-red-800 rounded-xl bg-red-50 flex items-start gap-2 border border-red-100" role="alert">
        <i class="ph-fill ph-warning-circle text-lg mt-0.5"></i>
        <div>
            @foreach($errors->all() as $error)
                <p class="font-semibold">{{ $error }}</p>
            @endforeach
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Liste des Chauffeurs</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Nom</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Téléphone</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Documents</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Assistant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($drivers as $driver)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#031C5B]/10 text-[#031C5B] flex items-center justify-center font-bold text-[11px] shrink-0">
                                    {{ substr($driver->first_name, 0, 1) }}{{ substr($driver->last_name, 0, 1) }}
                                </div>
                                <span class="text-[13.5px] font-bold text-slate-800">{{ $driver->first_name }} {{ $driver->last_name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">
                            {{ $driver->phone ?? '—' }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @if($driver->id_card_front || $driver->id_card_back)
                                    <span class="px-2 py-1 rounded-full text-[10.5px] font-bold bg-blue-100 text-blue-700">Pièce d'identité</span>
                                @endif
                                @if($driver->license_front || $driver->license_back)
                                    <span class="px-2 py-1 rounded-full text-[10.5px] font-bold bg-emerald-100 text-emerald-700">Permis</span>
                                @endif
                                @if(!$driver->id_card_front && !$driver->id_card_back && !$driver->license_front && !$driver->license_back)
                                    <span class="px-2 py-1 rounded-full text-[10.5px] font-bold bg-slate-100 text-slate-500">Aucun document</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">
                            @if($driver->has_assistant)
                                {{ $driver->assistant_name }}
                                @if($driver->assistant_phone)
                                    <span class="block text-[11px] text-slate-400 font-normal">{{ $driver->assistant_phone }}</span>
                                @endif
                            @else
                                Aucun
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun chauffeur enregistré.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('SchoolDashboard::transport._driver_modal')
</div>
@endsection
