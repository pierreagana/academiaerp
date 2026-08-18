@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-[1400px] w-full mx-auto space-y-6" x-data="{ search: '' }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('school.communication.events.calendar') }}" class="w-9 h-9 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition shrink-0">
                <i class="ph-bold ph-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-[24px] md:text-[28px] font-extrabold text-slate-800 tracking-tight">{{ $event->title }}</h1>
                <p class="text-[13px] text-slate-500 mt-1 flex items-center gap-2 flex-wrap">
                    <i class="ph-fill ph-calendar-blank"></i> {{ $event->start_at->format('d F Y') }}
                    <span class="text-slate-300">•</span>
                    <i class="ph-fill ph-map-pin"></i> {{ $event->location_type === 'internal' ? ($event->room->name ?? '-') : ($event->external_address ?? '-') }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('school.communication.events.edit', $event->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[14px] font-bold rounded-lg transition">
                <i class="ph-bold ph-pencil-simple"></i> Modifier
            </a>
            <a href="{{ route('school.communication.events.export', $event->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#031C5B] hover:bg-[#031C5B]/90 text-white text-[14px] font-bold rounded-lg transition shadow-sm">
                <i class="ph-bold ph-download-simple"></i> Exporter l'Appel
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- IA Prédictive -->
        <div class="bg-gradient-to-br from-[#F5F3FF] to-purple-50/50 border border-purple-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
                <i class="ph-fill ph-sparkle text-purple-600 text-lg"></i>
                <h3 class="font-extrabold text-slate-800 text-[14px]">IA Prédictive</h3>
            </div>
            <p class="text-[12.5px] text-slate-600 font-medium leading-relaxed mb-3">
                Analyse prédictive basée sur les données météorologiques et l'historique des sorties.
            </p>
            <div class="space-y-2">
                <div class="flex items-center gap-2 bg-white/70 rounded-lg px-3 py-2 text-[12.5px] font-bold text-emerald-700">
                    <i class="ph-fill ph-check-circle"></i> Taux de participation estimé : {{ $event->registrations->count() > 0 ? round($event->registrations->where('parental_authorization', '!=', 'refused')->count() / $event->registrations->count() * 100) : 0 }}%
                </div>
            </div>
        </div>

        <!-- Logistique & Transport -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
            <h3 class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2 mb-4"><i class="ph-bold ph-truck text-[#031C5B]"></i> Logistique & Transport</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <p class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider mb-1">Bus Affectés</p>
                    <p class="text-[20px] font-extrabold text-slate-800">{{ $event->bus_count ?? '-' }} <span class="text-[13px] text-slate-400 font-semibold">/ {{ $event->bus_capacity ?? '-' }} places</span></p>
                    @if($event->bus_status)
                        <span class="inline-flex items-center gap-1 mt-1 text-[11.5px] font-bold {{ $event->bus_status === 'confirmed' ? 'text-emerald-600' : 'text-amber-600' }}">
                            <i class="ph-fill {{ $event->bus_status === 'confirmed' ? 'ph-check-circle' : 'ph-clock' }}"></i>
                            {{ $event->bus_status === 'confirmed' ? 'Confirmé' : 'En attente' }}
                        </span>
                    @endif
                </div>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <p class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider mb-1">Restauration</p>
                    <p class="text-[20px] font-extrabold text-slate-800">{{ $event->catering_count ?? '-' }} <span class="text-[13px] text-slate-400 font-semibold">paniers</span></p>
                    @if($event->catering_status)
                        <span class="inline-flex items-center gap-1 mt-1 text-[11.5px] font-bold {{ $event->catering_status === 'confirmed' ? 'text-emerald-600' : 'text-amber-600' }}">
                            <i class="ph-fill {{ $event->catering_status === 'confirmed' ? 'ph-check-circle' : 'ph-clock' }}"></i>
                            {{ ['confirmed' => 'Confirmé', 'pending' => 'En attente', 'awaiting' => 'En attente traiteur'][$event->catering_status] ?? $event->catering_status }}
                        </span>
                    @endif
                </div>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <p class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider mb-1">Encadrement</p>
                    <p class="text-[20px] font-extrabold text-slate-800 mb-1">{{ $event->teachers->count() }} <span class="text-[13px] text-slate-400 font-semibold">professeur(s)</span></p>
                    <div class="flex -space-x-2">
                        @foreach($event->teachers->take(4) as $teacher)
                            <div class="w-7 h-7 rounded-full bg-[#031C5B] text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-slate-50" title="{{ $teacher->first_name }} {{ $teacher->last_name }}">
                                {{ substr($teacher->first_name, 0, 1) }}{{ substr($teacher->last_name, 0, 1) }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @if($event->equipment_needs || $event->catering_notes)
            <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4 text-[12.5px]">
                @if($event->equipment_needs)
                <div>
                    <p class="font-bold text-slate-500 mb-1.5">Besoins en matériel</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($event->equipment_needs as $item)
                            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-md font-semibold">{{ $item }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                @if($event->catering_notes)
                <div>
                    <p class="font-bold text-slate-500 mb-1.5">Restauration / Collation</p>
                    <p class="text-slate-600">{{ $event->catering_notes }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Liste des Élèves Inscrits -->
    @if($event->audience_type !== 'parents_only')
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2">
                <i class="ph-bold ph-users-three text-[#031C5B]"></i> Liste des Élèves Inscrits ({{ $event->registrations->count() }})
            </h3>
            <div class="relative w-full sm:w-64">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" x-model="search" placeholder="Rechercher un élève..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[13px] rounded-lg pl-9 pr-3 py-2 outline-none focus:border-[#031C5B] transition">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Élève</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Classe</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Autorisation Parentale</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Paiement{{ $event->participation_fee ? ' (' . number_format($event->participation_fee, 0, ',', ' ') . ' FCFA)' : '' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($event->registrations as $registration)
                        @php $student = $registration->student; @endphp
                        @continue(!$student)
                        <tr x-show="!search || {{ \Illuminate\Support\Js::from(strtolower($student->first_name . ' ' . $student->last_name)) }}.includes(search.toLowerCase())" class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-[#031C5B]/10 text-[#031C5B] flex items-center justify-center font-bold text-[11px]">
                                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                    </div>
                                    <span class="text-[13px] font-bold text-slate-800">{{ $student->first_name }} {{ $student->last_name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-[13px] font-semibold text-slate-600">{{ $student->academicClass->name ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <form action="{{ route('school.communication.events.registrations.authorization', [$event->id, $student->id]) }}" method="POST">
                                    @csrf
                                    @php
                                        $authStyles = ['pending' => 'bg-slate-100 text-slate-600', 'validated' => 'bg-emerald-100 text-emerald-700', 'refused' => 'bg-red-100 text-red-700'];
                                    @endphp
                                    <select name="status" onchange="this.form.submit()" class="text-[11.5px] font-bold rounded-full px-2.5 py-1 border-0 outline-none cursor-pointer {{ $authStyles[$registration->parental_authorization] ?? '' }}">
                                        <option value="pending" {{ $registration->parental_authorization === 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="validated" {{ $registration->parental_authorization === 'validated' ? 'selected' : '' }}>Validé</option>
                                        <option value="refused" {{ $registration->parental_authorization === 'refused' ? 'selected' : '' }}>Refusé</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-5 py-3">
                                @if($registration->payment_status === 'na')
                                    <span class="text-[11.5px] font-bold text-slate-400 italic">N/A</span>
                                @else
                                    <form action="{{ route('school.communication.events.registrations.payment', [$event->id, $student->id]) }}" method="POST">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="text-[11.5px] font-bold rounded-full px-2.5 py-1 border-0 outline-none cursor-pointer {{ $registration->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                            <option value="unpaid" {{ $registration->payment_status === 'unpaid' ? 'selected' : '' }}>Non réglé</option>
                                            <option value="paid" {{ $registration->payment_status === 'paid' ? 'selected' : '' }}>Réglé</option>
                                        </select>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun élève inscrit pour cet événement.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
