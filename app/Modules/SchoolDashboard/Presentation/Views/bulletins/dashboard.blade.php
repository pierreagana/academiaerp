@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Tableau de bord des Bulletins</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">{{ $current?->name ?? 'Aucun semestre actif' }} — Vue d'ensemble</p>
        </div>
        <div class="flex items-center gap-3">
            @if(!$teacher)
            <a href="{{ route('school.academic.bulletins.template') }}" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition">
                <i class="ph-bold ph-sliders text-lg"></i> Modèle
            </a>
            @endif
            <a href="{{ route('school.academic.bulletins.grades') }}" class="flex items-center gap-2 bg-[#031C5B] text-white px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition">
                <i class="ph-bold ph-pencil-simple text-lg"></i> Saisir les Notes
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
        Aucun semestre actif n'est défini pour cette école. Créez un semestre courant dans Académique &rarr; Semestre pour activer la saisie des notes.
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-3">Notes Saisies</p>
            <p class="text-[32px] font-extrabold text-[#031C5B] leading-none mb-3">{{ $gradesEnteredRate !== null ? $gradesEnteredRate . '%' : '—' }}</p>
            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-[#031C5B]" style="width: {{ $gradesEnteredRate ?? 0 }}%"></div></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-3">Bulletins Validés</p>
            <p class="text-[32px] font-extrabold text-[#031C5B] leading-none mb-3">{{ $publicationRates['validated'] !== null ? $publicationRates['validated'] . '%' : '—' }}</p>
            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-blue-500" style="width: {{ $publicationRates['validated'] ?? 0 }}%"></div></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-3">Bulletins Publiés</p>
            <p class="text-[32px] font-extrabold text-[#031C5B] leading-none mb-3">{{ $publicationRates['published'] !== null ? $publicationRates['published'] . '%' : '—' }}</p>
            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-emerald-500" style="width: {{ $publicationRates['published'] ?? 0 }}%"></div></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-3">Écarts Significatifs</p>
            <p class="text-[32px] font-extrabold {{ count($significantGaps) > 0 ? 'text-red-600' : 'text-[#031C5B]' }} leading-none mb-3">{{ count($significantGaps) }}</p>
            <p class="text-[11.5px] text-slate-400">Élève(s) avec &gt;4 pts d'écart entre matières</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-[16px] font-extrabold text-slate-900 mb-1">Écarts Significatifs</h3>
            <p class="text-[12.5px] text-slate-500 mb-4">Élèves dont l'écart entre leur meilleure et leur plus faible note dépasse 4 points ce semestre.</p>
            @forelse($significantGaps as $flag)
                <a href="{{ route('school.academic.bulletins.print', $flag['student']->id) }}" target="_blank" class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0 hover:bg-slate-50/50 -mx-2 px-2 rounded-lg transition">
                    <span class="text-[13px] font-bold text-slate-700">{{ $flag['student']->first_name }} {{ $flag['student']->last_name }}</span>
                    <span class="text-[12px] font-bold text-red-600">Écart de {{ $flag['gap'] }} pts</span>
                </a>
            @empty
                <p class="text-[13px] text-slate-400">Aucun écart significatif détecté.</p>
            @endforelse
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-[16px] font-extrabold text-slate-900 mb-1">Saisies Incomplètes</h3>
            <p class="text-[12.5px] text-slate-500 mb-4">Classes/matières où toutes les notes n'ont pas encore été saisies.</p>
            @forelse($incompleteEntries as $entry)
                <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0">
                    <span class="text-[13px] font-bold text-slate-700">{{ $entry['class'] }} — {{ $entry['subject'] }}</span>
                    <span class="text-[12px] font-bold text-amber-600">{{ $entry['graded'] }}/{{ $entry['expected'] }}</span>
                </div>
            @empty
                <p class="text-[13px] text-slate-400">Toutes les notes sont à jour.</p>
            @endforelse
        </div>
    </div>

    @if($teacher)
    <div class="grid grid-cols-1 {{ $topRankings->count() > 1 ? 'lg:grid-cols-2' : '' }} gap-5">
        @foreach($topRankings as $entry)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-[16px] font-extrabold text-slate-900 mb-1">Classement — {{ $entry['class']->name }}</h3>
            <p class="text-[12.5px] text-slate-500 mb-4">10 premiers de la classe ce semestre.</p>
            @forelse($entry['ranking'] as $row)
                <a href="{{ route('school.academic.bulletins.print', $row['student']->id) }}" target="_blank" class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0 hover:bg-slate-50/50 -mx-2 px-2 rounded-lg transition">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 shrink-0 rounded-full bg-slate-100 text-slate-600 text-[11px] font-extrabold flex items-center justify-center">{{ $row['rank'] ?? '—' }}</span>
                        <span class="text-[13px] font-bold text-slate-700">{{ $row['student']->first_name }} {{ $row['student']->last_name }}</span>
                    </div>
                    <span class="text-[12.5px] font-bold text-[#031C5B]">{{ $row['average'] !== null ? number_format($row['average'], 2) . '/20' : '—' }}</span>
                </a>
            @empty
                <p class="text-[13px] text-slate-400">Aucune note saisie pour cette classe ce semestre.</p>
            @endforelse
        </div>
        @endforeach
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-[16px] font-extrabold text-slate-900">État des Bulletins par Classe</h3>
            <a href="{{ route('school.academic.bulletins.validation') }}" class="text-[12.5px] font-bold text-[#031C5B] hover:underline">Voir la validation</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-slate-200">
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Classe</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Titulaire</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Progression</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($classProgress as $row)
                        <tr>
                            <td class="px-5 py-3.5 font-bold text-slate-800 text-[13.5px]">{{ $row['class']->name }}</td>
                            <td class="px-4 py-3.5 text-slate-500 text-[13px]">{{ $row['teacher'] ? trim($row['teacher']->first_name . ' ' . $row['teacher']->last_name) : '—' }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-32 h-1.5 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-[#031C5B]" style="width: {{ $row['progress'] }}%"></div></div>
                                    <span class="text-[12px] font-bold text-slate-600">{{ $row['progress'] }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                @if($row['publication_status'] === 'published')
                                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700">Publié</span>
                                @elseif($row['publication_status'] === 'validated')
                                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700">Validé</span>
                                @else
                                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500">Saisie en cours</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400 text-[13px]">Aucune classe enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
