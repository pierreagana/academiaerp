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
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th { text-align: left; background: #F8FAFC; padding: 8px 10px; border-bottom: 2px solid #E2E8F0; text-transform: uppercase; font-size: 10px; color: #64748B; }
        td { padding: 8px 10px; border-bottom: 1px solid #F1F5F9; }
        .badge { font-size: 10px; font-weight: bold; padding: 2px 8px; border-radius: 10px; }
        .badge-acquis { background: #ECFDF5; color: #059669; }
        .badge-en_cours { background: #FFFBEB; color: #B45309; }
        .badge-non_acquis { background: #FEF2F2; color: #B91C1C; }
        .badge-none { background: #F1F5F9; color: #94A3B8; }
        .obs { border-left: 2px solid #E2E8F0; padding-left: 12px; margin-bottom: 10px; }
        .obs .who { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #64748B; }
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
    <p class="subtitle">{{ $school->name ?? 'Établissement' }} · {{ $currentSemester->name ?? 'Semestre non défini' }} · Généré le {{ now()->format('d/m/Y à H:i') }}</p>

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
        <p>Aucun référentiel de compétences configuré.</p>
    @endforelse

    <h2 class="section">Observations & Comportement</h2>
    @forelse($observations as $observation)
        <div class="obs">
            <div class="who">{{ $observation->teacher ? trim($observation->teacher->first_name . ' ' . $observation->teacher->last_name) : '—' }} — {{ $observation->created_at->format('d M Y') }}</div>
            <div>{{ $observation->comment }}</div>
        </div>
    @empty
        <p>Aucune observation enregistrée.</p>
    @endforelse
</body>
</html>
