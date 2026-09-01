<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Livret Scolaire</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #0F172A; padding: 40px; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        p.subtitle { color: #64748B; margin-top: 0; margin-bottom: 24px; font-size: 13px; }
        .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
        .info-box { border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px; }
        .info-box .label { font-size: 10px; text-transform: uppercase; color: #64748B; font-weight: bold; margin-bottom: 4px; }
        .info-box .value { font-size: 15px; font-weight: bold; }
        h2.section { font-size: 15px; margin-top: 24px; margin-bottom: 8px; border-bottom: 2px solid #0F172A; padding-bottom: 4px; }
        h3.domain { font-size: 13px; margin-top: 14px; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 12px; }
        th { text-align: left; background: #F8FAFC; padding: 8px 10px; border-bottom: 2px solid #E2E8F0; text-transform: uppercase; font-size: 10px; color: #64748B; }
        td { padding: 8px 10px; border-bottom: 1px solid #F1F5F9; }
        .badge { font-size: 10px; font-weight: bold; padding: 2px 8px; border-radius: 10px; }
        .badge-acquis { background: #ECFDF5; color: #059669; }
        .badge-en_cours { background: #FFFBEB; color: #B45309; }
        .badge-non_acquis { background: #FEF2F2; color: #B91C1C; }
        .badge-none { background: #F1F5F9; color: #94A3B8; }
        .obs { border-left: 2px solid #E2E8F0; padding-left: 12px; margin-bottom: 10px; }
        .obs .who { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #64748B; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 16px; }
        .stat-box { border-radius: 8px; padding: 14px; text-align: center; background: #F8FAFC; }
        .stat-box.primary { background: #031C5B; color: white; }
        .stat-box .value { font-size: 20px; font-weight: bold; }
        .stat-box .label { font-size: 10px; text-transform: uppercase; margin-top: 4px; opacity: 0.7; }
        .signature-grid { display: flex; justify-content: space-between; margin-top: 50px; }
        .signature { text-align: center; width: 200px; }
        .signature .line { border-top: 1px solid #CBD5E1; margin-top: 40px; padding-top: 6px; font-size: 11px; color: #64748B; }
        .missing-note { font-size: 11px; color: #94A3B8; font-style: italic; }
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

    <h1>Livret Scolaire — {{ $student->first_name }} {{ $student->last_name }}</h1>
    <p class="subtitle">{{ $school->name ?? 'Établissement' }} · Année scolaire {{ $student->academic_year ?? '—' }} · Généré le {{ now()->format('d/m/Y à H:i') }}</p>

    <div class="info-grid">
        <div class="info-box">
            <div class="label">Matricule</div>
            <div class="value">#{{ $student->roll_number }}</div>
        </div>
        <div class="info-box">
            <div class="label">Classe</div>
            <div class="value">{{ $student->academicClass->name ?? '—' }}</div>
        </div>
        <div class="info-box">
            <div class="label">Date de naissance</div>
            <div class="value">{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d/m/Y') : '—' }}</div>
        </div>
    </div>

    <h2 class="section">Parcours Scolaire</h2>
    <table>
        <thead><tr><th>Année</th><th>Classe</th><th>Moyenne</th><th>Rang</th><th>Décision</th></tr></thead>
        <tbody>
            @foreach($yearRows as $row)
            <tr>
                <td><strong>{{ $row['academic_year'] ?? '—' }}</strong></td>
                <td>{{ $row['class'] }}</td>
                <td>{{ $row['average'] !== null ? number_format($row['average'], 2) . '/20' : '—' }}</td>
                <td>{{ $row['rank'] ? $row['rank'] . ' / ' . $row['classSize'] : '—' }}</td>
                <td>{{ $row['decision'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="section">Résultats par Trimestre</h2>
    @forelse($termResults as $term)
        <h3 class="domain">{{ $term['semester']->name }}</h3>
        @if($term['subjectGrades']->isNotEmpty())
        <table>
            <thead><tr><th>Matière</th><th>Coef</th><th>Moyenne</th><th>Appréciation</th></tr></thead>
            <tbody>
                @foreach($term['subjectGrades'] as $g)
                <tr>
                    <td><strong>{{ $g->subject->name ?? '—' }}</strong></td>
                    <td>{{ $g->subject->coefficient ?? 1 }}</td>
                    <td>{{ number_format($g->score, 2) }}/20</td>
                    <td>{{ $g->remark ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p class="subtitle" style="margin:0 0 12px;">Moyenne du trimestre : <strong>{{ $term['average'] !== null ? number_format($term['average'], 2) . '/20' : '—' }}</strong> &middot; Rang : <strong>{{ $term['rank'] ? $term['rank'] . ' / ' . $term['classSize'] : '—' }}</strong></p>
        @else
        <p class="missing-note">Aucune note enregistrée pour ce trimestre.</p>
        @endif
    @empty
        <p class="missing-note">Aucun trimestre configuré pour cette année scolaire.</p>
    @endforelse

    <h2 class="section">Cartographie des Compétences</h2>
    @forelse($competencyTree as $domainName => $subdomains)
        <h3 class="domain">{{ $domainName }}</h3>
        <table>
            <thead><tr><th>Compétence</th><th>Sous-domaine</th><th>Niveau</th></tr></thead>
            <tbody>
                @foreach($subdomains as $subdomainName => $competencies)
                    @foreach($competencies as $competency)
                        @php $level = $competencyMap[$competency->id]->level ?? null; @endphp
                        <tr>
                            <td>{{ $competency->statement }}</td>
                            <td>{{ $subdomainName }}</td>
                            <td><span class="badge badge-{{ $level ?? 'none' }}">{{ $level === 'acquis' ? 'Acquis' : ($level === 'en_cours' ? 'En cours' : ($level === 'non_acquis' ? 'Non acquis' : 'Non évalué')) }}</span></td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @empty
        <p class="missing-note">Aucun référentiel de compétences configuré pour cette école.</p>
    @endforelse

    <h2 class="section">Assiduité</h2>
    <div class="stats-grid">
        <div class="stat-box primary"><div class="value">{{ $totalDays }}</div><div class="label">Jours Observés</div></div>
        <div class="stat-box"><div class="value">{{ $justifiedAbsences }}</div><div class="label">Absences Justifiées</div></div>
        <div class="stat-box"><div class="value">{{ $unjustifiedAbsences }}</div><div class="label">Absences Non Justifiées</div></div>
        <div class="stat-box"><div class="value">{{ $lateCount }}</div><div class="label">Retards</div></div>
    </div>

    <h2 class="section">Vie Scolaire &amp; Observations</h2>
    @forelse($observations as $observation)
        <div class="obs">
            <div class="who">{{ $observation->teacher ? trim($observation->teacher->first_name . ' ' . $observation->teacher->last_name) : '—' }} — {{ $observation->created_at->format('d M Y') }}</div>
            <div>{{ $observation->comment }}</div>
        </div>
    @empty
        <p class="missing-note">Aucune observation enregistrée.</p>
    @endforelse

    <h2 class="section">Conseil de Classe</h2>
    <div class="stats-grid">
        <div class="stat-box primary"><div class="value">{{ $yearRows->first()['average'] !== null ? number_format($yearRows->first()['average'], 2) . '/20' : '—' }}</div><div class="label">Moyenne Générale</div></div>
        <div class="stat-box"><div class="value">{{ $yearRows->first()['rank'] ? $yearRows->first()['rank'] . ' / ' . $yearRows->first()['classSize'] : '—' }}</div><div class="label">Rang</div></div>
        <div class="stat-box" style="grid-column: span 2;"><div class="value" style="font-size:14px;">{{ $yearRows->first()['decision'] }}</div><div class="label">Décision</div></div>
    </div>

    <h2 class="section">Sanctions &amp; Distinctions</h2>
    @forelse($disciplinaryRecords as $record)
        @php
            $recordTypes = \App\Modules\Academic\Domain\Models\StudentDisciplinaryRecord::DISTINCTION_TYPES + \App\Modules\Academic\Domain\Models\StudentDisciplinaryRecord::SANCTION_TYPES;
        @endphp
        <div class="obs">
            <div class="who">{{ $record->category === 'distinction' ? 'Distinction' : 'Sanction' }} — {{ $record->recorded_date->format('d M Y') }}</div>
            <div><strong>{{ $recordTypes[$record->type] ?? $record->type }}</strong>@if($record->description) — {{ $record->description }} @endif</div>
        </div>
    @empty
        <p class="missing-note">Aucune sanction ni distinction enregistrée.</p>
    @endforelse
    <p class="missing-note">Orientation : non encore suivie dans le système.</p>

    <h2 class="section">Transport Scolaire</h2>
    @forelse($transportStops as $stop)
        <p>{{ $stop->period === 'morning' ? 'Matin' : 'Soir' }} — Route <strong>{{ $stop->route_name }}</strong>, arrêt <strong>{{ $stop->stop_name }}</strong></p>
    @empty
        <p class="missing-note">Élève non inscrit au service de transport.</p>
    @endforelse

    <div class="signature-grid">
        <div class="signature"><div class="line">Le Professeur Principal</div></div>
        <div class="signature"><div class="line">La Direction</div></div>
        <div class="signature"><div class="line">Parent / Responsable légal</div></div>
    </div>
</body>
</html>
