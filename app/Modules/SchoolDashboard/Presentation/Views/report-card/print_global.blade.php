<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Global — Livret Scolaire</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #0F172A; padding: 40px; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        p.subtitle { color: #64748B; margin-top: 0; margin-bottom: 24px; font-size: 13px; }
        .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
        .info-box { border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px; }
        .info-box .label { font-size: 10px; text-transform: uppercase; color: #64748B; font-weight: bold; margin-bottom: 4px; }
        .info-box .value { font-size: 22px; font-weight: bold; }
        h2.section { font-size: 15px; margin-top: 24px; margin-bottom: 8px; border-bottom: 2px solid #0F172A; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th { text-align: left; background: #F8FAFC; padding: 8px 10px; border-bottom: 2px solid #E2E8F0; text-transform: uppercase; font-size: 10px; color: #64748B; }
        td { padding: 8px 10px; border-bottom: 1px solid #F1F5F9; }
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

    <h1>Rapport Global — Livret Scolaire</h1>
    <p class="subtitle">{{ $school->name ?? 'Établissement' }} · {{ $current->name ?? 'Semestre non défini' }} · Généré le {{ now()->format('d/m/Y à H:i') }}</p>

    <div class="info-grid">
        <div class="info-box">
            <div class="label">Acquisition Compétences</div>
            <div class="value">{{ $acquisitionRate !== null ? $acquisitionRate . '%' : '—' }}</div>
        </div>
        <div class="info-box">
            <div class="label">Assiduité (30j)</div>
            <div class="value">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</div>
        </div>
        <div class="info-box">
            <div class="label">Répartition</div>
            <div class="value" style="font-size: 12px;">Acquis {{ $masteryBreakdown['acquis'] }}% · En cours {{ $masteryBreakdown['en_cours'] }}% · Non acquis {{ $masteryBreakdown['non_acquis'] }}%</div>
        </div>
    </div>

    <h2 class="section">Domaines à Surveiller</h2>
    <table>
        <thead><tr><th>Domaine</th><th>Taux d'acquisition</th><th>Évaluations</th></tr></thead>
        <tbody>
            @forelse($domainsAtRisk as $domain)
                <tr><td>{{ $domain['name'] }}</td><td>{{ $domain['rate'] }}%</td><td>{{ $domain['evaluated'] }}</td></tr>
            @empty
                <tr><td colspan="3">Aucune évaluation enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2 class="section">Classes</h2>
    <table>
        <thead><tr><th>Classe</th><th>Titulaire</th><th>Effectif</th><th>% Acquis</th></tr></thead>
        <tbody>
            @foreach($classesActive as $class)
                <tr>
                    <td>{{ $class['name'] }}</td>
                    <td>{{ $class['teacher'] ?? '—' }}</td>
                    <td>{{ $class['student_count'] }}</td>
                    <td>{{ $class['acquisition_rate'] !== null ? $class['acquisition_rate'] . '%' : 'Non évalué' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
