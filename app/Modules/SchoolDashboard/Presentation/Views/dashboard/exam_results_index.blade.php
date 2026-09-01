@extends('SchoolDashboard::layouts.app')

@section('title', 'Résultats aux examens')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Résultats aux examens</h2>
        <p class="text-sm text-slate-500 mt-1">
            Validez ici le nombre d'élèves admis par examen ({{ implode(' / ', $labels) }}) — le taux de réussite
            affiché aux parents dans School Track et la progression annuelle de l'établissement sont calculés
            automatiquement à partir de ces résultats, plus les promotions de classe. Aucune saisie manuelle de %.
        </p>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm font-semibold rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">Progression annuelle ({{ $year }})</p>
            <p class="text-xs text-slate-400 mt-1">Variation du score de performance (promotions + résultats d'examens) vs {{ \App\Modules\SuperAdmin\Domain\Models\School::previousAcademicYear($year) }}.</p>
        </div>
        @if($progressionAnnuelle === null)
            <span class="text-2xl font-bold text-slate-300">—</span>
        @else
            <span class="text-3xl font-bold {{ $progressionAnnuelle >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                {{ $progressionAnnuelle > 0 ? '+' : '' }}{{ $progressionAnnuelle }}%
            </span>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($labels as $type => $label)
            @php $session = $sessions->get($type); @endphp
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800">{{ $label }}</h3>
                    @if($session && $session->isValidated())
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-3 py-1">Validé</span>
                    @endif
                </div>

                @if(!in_array($type, $availableTypes, true))
                    <p class="text-sm text-slate-400 italic">Aucune classe de niveau {{ implode('/', \App\Modules\Academic\Domain\Models\ExamSession::levelsForType($type)) }} enregistrée pour cet établissement.</p>
                @elseif($session && $session->isValidated())
                    <div class="grid grid-cols-3 gap-3 text-center mb-4">
                        <div class="bg-slate-50 rounded-xl py-3">
                            <p class="text-xl font-bold text-slate-800">{{ $session->presented_count }}</p>
                            <p class="text-[11px] text-slate-500 mt-1">Présentés</p>
                        </div>
                        <div class="bg-emerald-50 rounded-xl py-3">
                            <p class="text-xl font-bold text-emerald-700">{{ $session->admitted_count }}</p>
                            <p class="text-[11px] text-emerald-600 mt-1">Admis</p>
                        </div>
                        <div class="bg-red-50 rounded-xl py-3">
                            <p class="text-xl font-bold text-red-700">{{ $session->failedCount() }}</p>
                            <p class="text-[11px] text-red-600 mt-1">Échoués</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-slate-600">Taux de réussite : <span class="font-bold text-slate-900">{{ $session->successRate() }}%</span></p>
                        <a href="{{ route('school.exam-results.create', ['type' => $type]) }}" class="text-xs font-bold text-blue-700 hover:underline">Modifier →</a>
                    </div>
                @else
                    <p class="text-sm text-slate-500 mb-4">Aucune session validée pour {{ $year }}.</p>
                    <a href="{{ route('school.exam-results.create', ['type' => $type]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#031C5B] text-white text-sm font-bold rounded-xl hover:bg-blue-900 transition">
                        <i class="ph ph-plus-circle"></i> Nouvelle session
                    </a>
                @endif
            </div>
        @endforeach
    </div>

    @if($history->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Historique</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-6 py-2">Année</th>
                        <th class="text-left px-6 py-2">Examen</th>
                        <th class="text-center px-6 py-2">Présentés</th>
                        <th class="text-center px-6 py-2">Admis</th>
                        <th class="text-center px-6 py-2">Taux</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($history as $row)
                        <tr>
                            <td class="px-6 py-2 font-semibold text-slate-700">{{ $row->academic_year }}</td>
                            <td class="px-6 py-2">{{ $labels[$row->exam_type] ?? $row->exam_type }}</td>
                            <td class="px-6 py-2 text-center">{{ $row->presented_count }}</td>
                            <td class="px-6 py-2 text-center">{{ $row->admitted_count }}</td>
                            <td class="px-6 py-2 text-center font-bold">{{ $row->successRate() }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
