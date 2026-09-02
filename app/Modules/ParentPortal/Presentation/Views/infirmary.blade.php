@extends('ParentPortal::layout')

@section('title', 'Infirmerie & Suivi Santé')

@section('content')

<!-- HEADER & CHILD SELECTOR -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Infirmerie</h1>
        <p class="text-sm font-medium text-slate-500 mt-1">Suivi de santé et interventions médicales</p>
    </div>

    <div class="flex items-center gap-3">
        <!-- Student Dropdown -->
        @if($children->isNotEmpty())
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Élève :</span>
            <div class="relative">
                <select onchange="window.location.href='{{ route('parent.infirmary') }}?student=' + this.value"
                        class="appearance-none bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-2xl pl-4 pr-9 py-2.5 outline-none focus:border-blue-500 shadow-xs cursor-pointer">
                    @foreach($children as $kid)
                        <option value="{{ $kid->id }}" {{ ($selectedChild && $selectedChild->id === $kid->id) ? 'selected' : '' }}>
                            {{ $kid->first_name }} {{ $kid->last_name }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                    <span class="material-symbols-outlined text-[16px]">expand_more</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Contact Infirmary Button -->
        @if($infirmaryPhone ?? null)
        <a href="tel:{{ $infirmaryPhone }}"
           class="inline-flex items-center gap-2 bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs px-4 py-2.5 rounded-2xl transition shadow-md shadow-blue-950/20">
            <span class="material-symbols-outlined text-[16px]">call</span>
            <span>Contacter l'infirmerie</span>
        </a>
        @endif
    </div>
</div>

@if(!$selectedChild)
<div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-12 text-center">
    <p class="text-sm text-slate-500">Aucun élève rattaché à votre compte.</p>
</div>
@else

<!-- TOP BANNER: APERÇU SANTÉ IA -->
<div class="bg-white rounded-3xl p-6 border-2 border-[#061536] shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] mb-6">
    <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-2xl bg-[#061536] text-white flex items-center justify-center shrink-0 shadow-xs">
            <span class="material-symbols-outlined text-[20px]">auto_awesome</span>
        </div>
        <div>
            <h2 class="text-sm font-black text-slate-900 mb-1">Aperçu Santé IA - {{ $selectedChild->first_name }}</h2>
            <p class="text-xs font-medium text-slate-600 leading-relaxed">
                {!! preg_replace('/\*\*(.*?)\*\*/', '<strong class="text-slate-900 font-bold">$1</strong>', $aiHealthOverview) !!}
            </p>
        </div>
    </div>
</div>

<!-- 3 METRIC / ALERT CARDS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    
    <!-- CARD 1: INTERVENTION EN COURS -->
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-extrabold text-slate-800">Intervention en cours</h3>
            <span class="material-symbols-outlined text-[18px] text-slate-400">add_box</span>
        </div>
        <div>
            <div class="text-4xl font-black text-slate-900 tracking-tight mb-2">
                {{ $activeInterventionsCount }}
            </div>
            <p class="text-xs font-medium text-slate-400">{{ $activeInterventionsCount > 0 ? "Signalée(s) aujourd'hui." : "Aucune intervention signalée aujourd'hui." }}</p>
        </div>
    </div>

    <!-- CARD 2: MÉDICAMENTS -->
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-extrabold text-slate-800">Médicaments</h3>
            <span class="material-symbols-outlined text-[18px] text-slate-400">medical_services</span>
        </div>
        <div>
            <div class="text-4xl font-black text-slate-900 tracking-tight mb-2">
                {{ $medicationsAdministeredCount }}
            </div>
            <p class="text-xs font-medium text-slate-400">Intervention(s) avec soins ce mois-ci.</p>
        </div>
    </div>

    <!-- CARD 3: ALERTES ALLERGIES -->
    <div class="bg-white rounded-3xl p-6 border border-slate-100 border-t-4 border-t-rose-500 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-extrabold text-slate-800">Alertes Allergies</h3>
            <span class="material-symbols-outlined text-[18px] text-rose-500">warning</span>
        </div>
        <div>
            <div class="mb-3">
                @forelse($allergies as $alg)
                    <span class="inline-block text-xs font-extrabold px-3 py-1 rounded-full bg-rose-50 text-rose-700">
                        {{ $alg->name }}
                    </span>
                @empty
                    <span class="text-xs text-slate-400">Aucune allergie déclarée.</span>
                @endforelse
            </div>
            @if($allergies->contains(fn($a) => $a->severity === 'haute'))
            <p class="text-xs font-semibold text-slate-500">Allergie sévère déclarée — vigilance recommandée.</p>
            @endif
        </div>
    </div>

</div>

<!-- BOTTOM ROW: JOURNAL DES INTERVENTIONS (Col 7) + ORDONNANCES & DOC (Col 5) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    
    <!-- JOURNAL DES INTERVENTIONS (Col 7) -->
    <div class="lg:col-span-7 bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-sm font-extrabold text-slate-900">Journal des interventions</h2>
            <button type="button" class="text-xs font-bold text-blue-700 hover:underline">Voir tout</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">
                        <th class="py-2.5">Date & Heure</th>
                        <th class="py-2.5">Cause</th>
                        <th class="py-2.5">Soins / Médicament</th>
                        <th class="py-2.5 text-right">Infirmier(e)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($interventions as $item)
                    @php
                        $dt = $item->arrival_time instanceof \Carbon\Carbon ? $item->arrival_time : \Carbon\Carbon::parse($item->arrival_time);
                    @endphp
                    <tr>
                        <td class="py-4 font-bold text-slate-900">
                            <div>{{ $dt->translatedFormat('d M Y') }}</div>
                            <div class="text-[11px] font-medium text-slate-400">{{ $dt->format('H:i') }}</div>
                        </td>
                        <td class="py-4 font-semibold text-slate-800">
                            {{ $item->motive }}
                        </td>
                        <td class="py-4 font-medium text-slate-600">
                            {{ $item->care_notes }}
                        </td>
                        <td class="py-4 text-right font-bold text-slate-700">
                            {{ $item->createdBy?->name ?? 'Infirmerie' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-xs text-slate-400">Aucune intervention enregistrée.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ORDONNANCES & DOCUMENTS (Col 5) -->
    <div class="lg:col-span-5 bg-white rounded-3xl p-6 border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-extrabold text-slate-900">Ordonnances & Doc.</h2>
                </div>
                <span class="material-symbols-outlined text-[19px] text-slate-400">description</span>
            </div>

            <div class="space-y-3 mb-5">
                @forelse($documents as $doc)
                <div class="p-3.5 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[18px]">{{ $doc['icon'] }}</span>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-xs font-bold text-slate-900 truncate">{{ $doc['title'] }}</h3>
                            <p class="text-[11px] font-medium text-slate-400 mt-0.5">{{ $doc['date_info'] }}</p>
                        </div>
                    </div>

                    <a href="{{ $doc['url'] }}" class="text-slate-400 hover:text-blue-700 transition p-1">
                        <span class="material-symbols-outlined text-[18px]">download</span>
                    </a>
                </div>
                @empty
                <p class="text-xs text-slate-400 text-center py-4">Aucun document pour l'instant.</p>
                @endforelse
            </div>
        </div>

        <div>
            <button type="button" 
                    class="w-full inline-flex items-center justify-center gap-2 border-2 border-dashed border-slate-200 hover:border-slate-300 text-slate-700 font-bold text-xs py-3 rounded-2xl hover:bg-slate-50 transition">
                <span class="material-symbols-outlined text-[17px]">upload</span>
                <span>Ajouter un document</span>
            </button>
        </div>
    </div>

</div>

@endif

@endsection
