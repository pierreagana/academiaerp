<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin — {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #0F172A; padding: 40px; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        p.subtitle { color: #64748B; margin-top: 0; margin-bottom: 24px; font-size: 13px; }
        .header-grid { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; gap: 24px; }
        .school-block { display: flex; gap: 14px; align-items: center; }
        .school-block img { width: 56px; height: 56px; object-fit: contain; }
        .info-box { border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px 16px; min-width: 220px; }
        .info-box .label { font-size: 10px; text-transform: uppercase; color: #64748B; font-weight: bold; margin-bottom: 4px; }
        .info-box .row { font-size: 13px; margin: 3px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 12.5px; margin-bottom: 20px; }
        th { text-align: left; background: #F8FAFC; padding: 8px 10px; border-bottom: 2px solid #E2E8F0; text-transform: uppercase; font-size: 10px; color: #64748B; }
        td { padding: 8px 10px; border-bottom: 1px solid #F1F5F9; vertical-align: top; }
        .remark { font-style: italic; color: #475569; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px; }
        .stat-box { border-radius: 8px; padding: 14px; text-align: center; background: #F8FAFC; }
        .stat-box.primary { background: #031C5B; color: white; }
        .stat-box .value { font-size: 22px; font-weight: bold; }
        .stat-box .label { font-size: 10px; text-transform: uppercase; margin-top: 4px; opacity: 0.7; }
        .signature-grid { display: flex; justify-content: space-between; margin-top: 50px; }
        .signature { text-align: center; width: 220px; }
        .signature .line { border-top: 1px solid #CBD5E1; margin-top: 40px; padding-top: 6px; font-size: 11px; color: #64748B; }
        .print-bar { margin-bottom: 20px; }
        .print-bar button { padding: 8px 16px; background: #031C5B; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
        @media print {
            .print-bar { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="print-bar">
        <button onclick="window.print()">Imprimer / Enregistrer en PDF</button>
    </div>

    <div class="header-grid">
        <div class="school-block">
            @if($school?->logo_url)
                <img src="{{ $school->logo_url }}" alt="Logo">
            @endif
            <div>
                <h1>{{ $school->name ?? 'Établissement' }}</h1>
                <p class="subtitle">
                    {{ $school->location ?? '' }}
                    @if($school?->contact_phone) &middot; {{ $school->contact_phone }} @endif
                </p>
            </div>
        </div>
        <div class="info-box">
            <div class="label">Informations Élève</div>
            <div class="row"><strong>{{ $student->first_name }} {{ $student->last_name }}</strong></div>
            <div class="row">Classe : {{ $student->academicClass->name ?? '—' }}</div>
            <div class="row">Matricule : {{ $student->roll_number }}</div>
        </div>
    </div>
    <p class="subtitle" style="margin-top:-12px;">Année Scolaire {{ now()->format('Y') }} &middot; {{ $currentSemester->name ?? 'Semestre non défini' }}</p>

    @if(!$isPublished)
    <div style="padding: 40px 20px; text-align: center; border: 1px dashed #E2E8F0; border-radius: 12px; color: #64748B;">
        <p style="font-size: 15px; font-weight: bold; color: #0F172A; margin-bottom: 6px;">Bulletin non encore publié</p>
        <p style="font-size: 13px;">Le professeur principal doit valider puis publier les bulletins de cette classe pour ce semestre avant qu'ils ne soient disponibles ici.</p>
    </div>
    @else
    <table>
        <thead>
            <tr>
                <th>Matière / Professeur</th>
                <th>Note/20</th>
                @if($template->show_coefficient)<th>Coef</th><th>Points</th>@endif
                @if($template->show_class_average)<th>Rang</th>@endif
                @if($template->show_highest_lowest)<th>Meilleure / Plus Faible</th>@endif
                <th>Appréciation</th>
            </tr>
        </thead>
        <tbody>
            @forelse($displayGrades as $grade)
                @php
                    $hasScore = $grade->score !== null;
                    $coef = $grade->subject->coefficient ?? 1;
                    $points = $hasScore ? $grade->score * $coef : null;
                    $subjectRank = $hasScore ? $stats->subjectRank($classGrades, $grade->subject_id, $student->id) : null;
                    $hiLo = $hasScore ? $stats->subjectHighestLowest($classGrades, $grade->subject_id) : null;
                    $remark = $hasScore ? ($grade->remark ?: ($template->suggested_remarks_enabled ? $stats->suggestedRemark($grade->score) : null)) : null;
                @endphp
                <tr>
                    <td>
                        <strong>{{ $grade->subject->name }}</strong><br>
                        <span style="color:#94A3B8;">{{ $grade->teacher ? trim($grade->teacher->first_name . ' ' . $grade->teacher->last_name) : '—' }}</span>
                    </td>
                    <td><strong>{{ $hasScore ? number_format($grade->score, 2) : '—' }}</strong></td>
                    @if($template->show_coefficient)
                        <td>{{ $coef }}</td>
                        <td>{{ $hasScore ? number_format($points, 2) : '—' }}</td>
                    @endif
                    @if($template->show_class_average)<td>{{ $subjectRank ? $subjectRank['rank'] . 'e / ' . $subjectRank['total'] : '—' }}</td>@endif
                    @if($template->show_highest_lowest)<td>{{ $hasScore ? (($hiLo['highest'] ?? '—') . ' / ' . ($hiLo['lowest'] ?? '—')) : '—' }}</td>@endif
                    <td class="remark">{{ $hasScore ? ($remark ?? '—') : 'Pas encore noté' }}</td>
                </tr>
            @empty
                <tr><td colspan="7">Aucune matière configurée pour cette classe.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="stats-grid">
        <div class="stat-box primary">
            <div class="value">{{ $average !== null ? $average . '/20' : '—' }}</div>
            <div class="label">Moy. Générale</div>
        </div>
        @if($template->show_ranking)
        <div class="stat-box">
            <div class="value">{{ $rank ? $rank . 'e' : '—' }}</div>
            <div class="label">Rang @if($classSize) sur {{ $classSize }} @endif</div>
        </div>
        @endif
        <div class="stat-box">
            <div class="value">{{ $unjustifiedAbsences }}</div>
            <div class="label">Absences Injustifiées</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $lateCount }}</div>
            <div class="label">Retards</div>
        </div>
        @if($isLastTerm && $annualAverage !== null)
        <div class="stat-box primary" style="grid-column: 1 / -1;">
            <div class="value">{{ $annualAverage }}/20</div>
            <div class="label">Moyenne Générale Finale Annuelle (T1+T2+T3)/3</div>
        </div>
        @endif
    </div>

    @if($template->show_signature_area)
    <div class="signature-grid">
        <div class="signature">
            <div class="line">Le Professeur Principal</div>
        </div>
        <div class="signature">
            <div class="line">Le Directeur — {{ now()->format('d/m/Y') }}</div>
        </div>
    </div>
    @endif
    @endif
</body>
</html>
