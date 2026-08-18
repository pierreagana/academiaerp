@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Aperçu Global</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Suivi des indicateurs clés et de l'acquisition des compétences de l'établissement.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('school.report-card.referentials') }}" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition">
                <i class="ph-bold ph-gear text-lg"></i> Référentiels
            </a>
            <a href="{{ route('school.report-card.evaluation') }}" class="flex items-center gap-2 bg-[#031C5B] text-white px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition">
                <i class="ph-bold ph-clipboard-text text-lg"></i> Évaluer
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    @if(!$current)
    <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-[14px] font-medium">
        Aucun semestre actif n'est défini pour cette école. Créez un semestre courant dans Académique &rarr; Semestre pour activer le suivi des compétences.
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="text-[15px] font-bold text-slate-800 leading-snug">Acquisition<br>Compétences</h3>
                <span class="w-9 h-9 rounded-lg bg-[#031C5B]/10 text-[#031C5B] flex items-center justify-center"><i class="ph-bold ph-target text-lg"></i></span>
            </div>
            <div class="flex items-end gap-2">
                <span class="text-[36px] font-extrabold text-[#031C5B] leading-none">{{ $acquisitionRate !== null ? $acquisitionRate . '%' : '—' }}</span>
                @if($acquisitionGrowth)
                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-1 rounded-full {{ str_starts_with($acquisitionGrowth, '+') ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                    <i class="ph-bold {{ str_starts_with($acquisitionGrowth, '+') ? 'ph-trend-up' : 'ph-trend-down' }}"></i> {{ $acquisitionGrowth }}
                </span>
                @endif
            </div>
            <p class="text-[12.5px] text-slate-500 mt-2">Moyenne globale du semestre {{ $current?->name ?? '' }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="text-[15px] font-bold text-slate-800 leading-snug">Assiduité<br>Moyenne</h3>
                <span class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="ph-bold ph-calendar-check text-lg"></i></span>
            </div>
            <div class="flex items-end gap-2">
                <span class="text-[36px] font-extrabold text-[#031C5B] leading-none">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</span>
                <span class="inline-flex items-center text-[11px] font-bold px-2 py-1 rounded-full bg-slate-100 text-slate-500">Stable</span>
            </div>
            <p class="text-[12.5px] text-slate-500 mt-2">Sur les 30 derniers jours</p>
        </div>

        <div class="bg-red-50/60 border border-red-100 rounded-2xl shadow-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="text-[15px] font-bold text-red-700 leading-snug">Alertes<br>Comportementales</h3>
                <span class="w-9 h-9 rounded-lg bg-red-100 text-red-600 flex items-center justify-center"><i class="ph-bold ph-bell-ringing text-lg"></i></span>
            </div>
            <span class="text-[36px] font-extrabold text-red-700 leading-none">{{ $alerts['count'] }}</span>
            <p class="text-[12.5px] text-red-600 mt-1 mb-3">Dossier(s) à réviser</p>
            @if(count($alerts['classes']) > 0)
                <div class="bg-white rounded-xl border border-red-100 p-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[12.5px] font-bold text-slate-800">{{ $alerts['classes'][0]['class'] }}</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">Priorité</span>
                    </div>
                    <p class="text-[11.5px] text-slate-500">{{ $alerts['classes'][0]['reason'] }}</p>
                </div>
            @else
                <p class="text-[12px] text-red-500/80">Aucun dossier ne dépasse les seuils de vigilance.</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-1">
                <span class="w-9 h-9 rounded-lg bg-[#031C5B] text-white flex items-center justify-center"><i class="ph-bold ph-magnifying-glass text-lg"></i></span>
                <h3 class="text-[16px] font-extrabold text-slate-900">Domaines à Surveiller</h3>
            </div>
            <p class="text-[12.5px] text-slate-500 mb-5">Taux d'acquisition réel le plus faible, ce semestre.</p>

            @forelse($domainsAtRisk as $domain)
                <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0">
                    <div>
                        <p class="text-[13.5px] font-bold text-slate-800">{{ $domain['name'] }}</p>
                        <p class="text-[11.5px] text-slate-400">{{ $domain['evaluated'] }} évaluation(s)</p>
                    </div>
                    <span class="text-[15px] font-extrabold {{ $domain['rate'] < 50 ? 'text-red-600' : 'text-amber-600' }}">{{ $domain['rate'] }}%</span>
                </div>
            @empty
                <p class="text-[13px] text-slate-400">Aucune évaluation enregistrée ce semestre.</p>
            @endforelse
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-[16px] font-extrabold text-slate-900 mb-5">Répartition des Maîtrises</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-[12.5px] font-bold mb-1"><span class="text-emerald-700">Acquis</span><span>{{ $masteryBreakdown['acquis'] }}%</span></div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-emerald-500" style="width: {{ $masteryBreakdown['acquis'] }}%"></div></div>
                </div>
                <div>
                    <div class="flex justify-between text-[12.5px] font-bold mb-1"><span class="text-amber-700">En cours</span><span>{{ $masteryBreakdown['en_cours'] }}%</span></div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-amber-500" style="width: {{ $masteryBreakdown['en_cours'] }}%"></div></div>
                </div>
                <div>
                    <div class="flex justify-between text-[12.5px] font-bold mb-1"><span class="text-red-700">Non acquis</span><span>{{ $masteryBreakdown['non_acquis'] }}%</span></div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-red-500" style="width: {{ $masteryBreakdown['non_acquis'] }}%"></div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-[16px] font-extrabold text-slate-900">Classes Actives</h3>
            <a href="{{ route('school.report-card.print-global') }}" target="_blank" class="text-[12.5px] font-bold text-[#031C5B] hover:underline">Générer Rapport Global</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($classesActive as $class)
                <div class="flex items-center justify-between py-3">
                    <div>
                        <p class="text-[13.5px] font-bold text-slate-800">{{ $class['name'] }}</p>
                        <p class="text-[12px] text-slate-400">{{ $class['teacher'] ?? 'Aucun titulaire' }} &middot; {{ $class['student_count'] }} élèves</p>
                    </div>
                    @if($class['acquisition_rate'] !== null)
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $class['acquisition_rate'] >= 70 ? 'bg-emerald-50 text-emerald-700' : ($class['acquisition_rate'] >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">{{ $class['acquisition_rate'] }}% Acquis</span>
                    @else
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500">Non évalué</span>
                    @endif
                </div>
            @empty
                <p class="text-[13px] text-slate-400 py-4">Aucune classe enregistrée.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
