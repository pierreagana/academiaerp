@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-[1400px] w-full mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-[26px] font-bold text-[#0F172A] tracking-tight">Contrôle d'Accès</h1>
            <p class="text-[14px] text-slate-500 mt-1">Journal réel des entrées et sorties, basé sur les passages enregistrés.</p>
        </div>
        <a href="{{ route('school.academic.presence.access.export') }}" class="bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-5 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
            <i class="ph-bold ph-download-simple"></i>
            Générer rapport
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('warning'))
    <div class="p-4 text-sm text-orange-800 rounded-lg bg-orange-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-warning-circle text-lg"></i>
        <span class="font-medium">{{ session('warning') }}</span>
    </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Sur le Campus</p>
            <div class="flex items-center justify-between">
                <h3 class="text-[30px] font-extrabold text-[#0F172A]">{{ number_format($onCampus, 0, ',', ' ') }}</h3>
                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="ph-fill ph-graduation-cap text-[20px]"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Pic d'Entrée (Aujourd'hui)</p>
            <div class="flex items-center justify-between">
                <h3 class="text-[22px] font-extrabold text-[#0F172A]">{{ $peakHour ?? 'Aucune donnée' }}</h3>
                <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600">
                    <i class="ph-fill ph-clock text-[20px]"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-red-100">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Tentatives Non Autorisées</p>
            <div class="flex items-center justify-between">
                <h3 class="text-[30px] font-extrabold text-red-600">{{ $unauthorizedCount }}</h3>
                <div class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center text-red-600">
                    <i class="ph-fill ph-shield-warning text-[20px]"></i>
                </div>
            </div>
            <p class="text-[12px] text-slate-400 mt-1">Aujourd'hui</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Check-in form -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <h3 class="text-[15px] font-bold text-slate-900 mb-1">Nouveau Passage</h3>
            <p class="text-[12.5px] text-slate-500 mb-4">Scannez la carte (QR) ou saisissez le matricule.</p>
            <form action="{{ route('school.academic.presence.access.checkin') }}" method="POST" class="space-y-3">
                @csrf
                <input type="text" name="scanned_code" autofocus required placeholder="Code / Matricule..."
                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76]">
                <select name="action" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76]">
                    <option value="entry">Entrée</option>
                    <option value="exit">Sortie</option>
                </select>
                <select name="access_point_id" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76]">
                    <option value="">-- Portail (optionnel) --</option>
                    @foreach($accessPoints as $point)
                        <option value="{{ $point->id }}">{{ $point->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-5 py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                    <i class="ph-bold ph-scan"></i>
                    Enregistrer le passage
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-[12px] font-bold text-slate-500 uppercase tracking-wider mb-2">Portails</p>
                <div class="space-y-1.5 mb-3">
                    @foreach($accessPoints as $point)
                        <div class="flex items-center justify-between text-[13px] text-slate-700 bg-slate-50 rounded-lg px-3 py-1.5">
                            <span>{{ $point->name }}</span>
                            <form action="{{ route('school.academic.presence.access.points.destroy', $point->id) }}" method="POST" onsubmit="return confirm('Supprimer ce portail ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600"><i class="ph-bold ph-x"></i></button>
                            </form>
                        </div>
                    @endforeach
                </div>
                <form action="{{ route('school.academic.presence.access.points.store') }}" method="POST" class="flex gap-2">
                    @csrf
                    <input type="text" name="name" required placeholder="Nom du portail..." class="flex-1 bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[12.5px] rounded-lg px-3 py-2 outline-none focus:border-[#2F5F76]">
                    <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 rounded-lg transition"><i class="ph-bold ph-plus"></i></button>
                </form>
            </div>
        </div>

        <!-- Journal -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="text-[15px] font-bold text-slate-900">Journal en Temps Réel</h3>
                <form action="{{ route('school.academic.presence.access') }}" method="GET" class="flex items-center gap-2">
                    <select name="role_label" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-slate-700 text-[12.5px] font-semibold rounded-lg px-3 py-1.5 outline-none">
                        <option value="">Tous les Rôles</option>
                        <option value="Élève" {{ ($filters['role_label'] ?? '') === 'Élève' ? 'selected' : '' }}>Élève</option>
                        <option value="Personnel" {{ ($filters['role_label'] ?? '') === 'Personnel' ? 'selected' : '' }}>Personnel</option>
                        <option value="Inconnu" {{ ($filters['role_label'] ?? '') === 'Inconnu' ? 'selected' : '' }}>Inconnu</option>
                    </select>
                    <select name="access_point_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-slate-700 text-[12.5px] font-semibold rounded-lg px-3 py-1.5 outline-none">
                        <option value="">Tous les Portails</option>
                        @foreach($accessPoints as $point)
                            <option value="{{ $point->id }}" {{ ($filters['access_point_id'] ?? '') == $point->id ? 'selected' : '' }}>{{ $point->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-4 py-3 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Heure</th>
                            <th class="px-4 py-3 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Individu</th>
                            <th class="px-4 py-3 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Rôle</th>
                            <th class="px-4 py-3 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Action</th>
                            <th class="px-4 py-3 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Portail</th>
                            <th class="px-4 py-3 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 py-3 text-[12.5px] text-slate-600">{{ $log->occurred_at->format('H:i:s') }}</td>
                            <td class="px-4 py-3">
                                <div class="text-[13px] font-bold text-slate-800">{{ $log->person_name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $log->scanned_code }}</div>
                            </td>
                            <td class="px-4 py-3 text-[12.5px] text-slate-600">{{ $log->role_label }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 text-[12.5px] font-semibold {{ $log->action === 'entry' ? 'text-blue-600' : 'text-slate-500' }}">
                                    <i class="ph-bold {{ $log->action === 'entry' ? 'ph-sign-in' : 'ph-sign-out' }}"></i>
                                    {{ $log->action === 'entry' ? 'Entrée' : 'Sortie' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-[12.5px] text-slate-600">{{ $log->accessPoint->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($log->authorized)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-50 text-green-600 border border-green-100">Autorisé</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-600 border border-red-100">Non Autorisé</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500 text-[13px]">Aucun passage enregistré pour le moment.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100 flex justify-center">
                {{ $logs->appends($filters)->links('pagination::tailwind') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
