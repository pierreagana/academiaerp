@extends('SchoolDashboard::layouts.app')

@section('title', 'Informations de l\'Établissement')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-slate-800">Profil de l'Établissement</h2>
        <div class="flex gap-3">
            <a href="{{ route('school.establishment.edit') }}" class="px-4 py-2 bg-primary-dynamic text-white font-bold rounded-xl hover:opacity-90 transition flex items-center gap-2">
                <i class="ph ph-pencil-simple"></i> Modifier
            </a>
            <a href="{{ route('school.dashboard') }}" class="px-4 py-2 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition">
                Retour
            </a>
        </div>
    </div>

    @if($school)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Logo et infos de base -->
        <div class="md:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col items-center text-center relative">
            @if($school->logo_url)
                <img src="{{ $school->logo_url }}" alt="Logo" class="w-32 h-32 rounded-xl object-cover mb-4 border border-slate-100 shadow-sm">
            @else
                <div class="w-32 h-32 rounded-xl bg-slate-100 flex items-center justify-center text-4xl font-bold text-slate-300 mb-4">
                    {{ strtoupper(substr($school->name, 0, 1)) }}
                </div>
            @endif
            <h3 class="text-xl font-bold text-slate-800 mb-1">{{ $school->name }}</h3>
            <p class="text-sm text-slate-500 mb-4">{{ $school->slogan ?? 'Aucun slogan' }}</p>
            
            <div class="w-full pt-4 border-t border-slate-100 flex justify-between items-center text-sm">
                <span class="text-slate-500">Forfait actuel</span>
                <span class="font-bold text-primary-dynamic bg-blue-50 px-3 py-1 rounded-full">{{ $school->plan_name }}</span>
            </div>
            @if($school->code)
            <div class="w-full mt-3 flex justify-between items-center text-sm">
                <span class="text-slate-500">Code Établissement</span>
                <span class="font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded-md font-mono text-xs">{{ $school->code }}</span>
            </div>
            @endif
            <div class="w-full mt-3 flex justify-between items-center text-sm">
                <span class="text-slate-500">Statut</span>
                <span class="font-bold {{ $school->status === 'active' ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50' }} px-3 py-1 rounded-full">
                    {{ ucfirst($school->status) }}
                </span>
            </div>
        </div>

        <!-- Coordonnées et Contact -->
        <div class="md:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h4 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-100 pb-2">Coordonnées</h4>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Responsable</label>
                    <p class="font-semibold text-slate-700">{{ auth()->user()->name }}</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Téléphone</label>
                    <p class="font-semibold text-slate-700">{{ $school->contact_phone ?? 'Non renseigné' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Email Officiel</label>
                    <p class="font-semibold text-slate-700">{{ $school->contact_email ?? 'Non renseigné' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Adresse</label>
                    <p class="font-semibold text-slate-700">{{ $school->location ?? 'Non renseignée' }}</p>
                </div>
            </div>

            <h4 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Position Géographique</h4>
            <div id="schoolMap" class="w-full h-64 rounded-xl border border-slate-200 z-10 relative bg-slate-100"></div>
        </div>
    </div>
    @else
    <div class="bg-yellow-50 text-yellow-800 p-4 rounded-xl">
        Aucun établissement n'est rattaché à votre compte.
    </div>
    @endif
</div>
@endsection

@push('scripts')
@if($school && $school->latitude && $school->longitude)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let lat = {{ $school->latitude }};
        let lng = {{ $school->longitude }};
        
        const map = L.map('schoolMap').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map)
            .bindPopup("<b>{{ $school->name }}</b><br>{{ $school->location }}")
            .openPopup();
    });
</script>
@endif
@endpush
