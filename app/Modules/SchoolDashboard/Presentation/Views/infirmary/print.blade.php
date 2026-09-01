<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dossier de Santé</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #0F172A; padding: 40px; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        p.subtitle { color: #64748B; margin-top: 0; margin-bottom: 24px; font-size: 13px; }
        .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
        .info-box { border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px; }
        .info-box .label { font-size: 10px; text-transform: uppercase; color: #64748B; font-weight: bold; margin-bottom: 4px; }
        .info-box .value { font-size: 15px; font-weight: bold; }
        .alert-box { border: 1px solid #FECACA; background: #FEF2F2; border-radius: 8px; padding: 14px; margin-bottom: 24px; }
        .alert-box h3 { font-size: 13px; color: #B91C1C; margin: 0 0 8px 0; }
        .alert-box p { font-size: 13px; margin: 4px 0; }
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

    <h1>Dossier de Santé — {{ $student->first_name }} {{ $student->last_name }}</h1>
    <p class="subtitle">{{ $school->name ?? 'Établissement' }} · Généré le {{ now()->format('d/m/Y à H:i') }}</p>

    <div class="info-grid">
        <div class="info-box">
            <div class="label">Matricule</div>
            <div class="value">#{{ $student->roll_number }}</div>
        </div>
        <div class="info-box">
            <div class="label">Classe</div>
            <div class="value">{{ $student->academicClass->name ?? '-' }}</div>
        </div>
        <div class="info-box">
            <div class="label">Groupe Sanguin</div>
            <div class="value">{{ $student->blood_group ?? 'Inconnu' }}</div>
        </div>
    </div>

    @if($student->allergies || $student->medical_conditions || $parentAllergies->isNotEmpty())
    <div class="alert-box">
        <h3>Alertes & Antécédents</h3>
        @if($student->allergies)
            <p><strong>Allergies (fiche d'inscription) :</strong> {{ $student->allergies }}</p>
        @endif
        @if($student->medical_conditions)
            <p><strong>Conditions Médicales :</strong> {{ $student->medical_conditions }}</p>
        @endif
        @foreach($parentAllergies as $allergy)
            <p><strong>{{ $allergy->name }}{{ $allergy->severity ? ' — ' . $allergy->severity : '' }} :</strong> {{ $allergy->notes ?? 'Signalé par le parent' }} <em>(signalé par le parent)</em></p>
        @endforeach
    </div>
    @endif

    @if($parentVaccines->isNotEmpty())
    <h2 class="section">Carnet de Vaccination (signalé par le parent)</h2>
    <table>
        <thead>
            <tr>
                <th>Vaccin</th>
                <th>Fait le</th>
                <th>Prochain rappel</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parentVaccines as $vaccine)
            <tr>
                <td>{{ $vaccine->name }}</td>
                <td>{{ $vaccine->administered_at->format('d/m/Y') }}</td>
                <td>{{ $vaccine->next_due_at?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $vaccine->notes ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($parentPrescriptions->isNotEmpty())
    <h2 class="section">Ordonnances / Documents (signalé par le parent)</h2>
    <table>
        <thead>
            <tr>
                <th>Document</th>
                <th>Envoyé le</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parentPrescriptions as $doc)
            <tr>
                <td>{{ $doc->name }}</td>
                <td>{{ $doc->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @php $emergencyContact = $student->guardians->first(); @endphp
    @if($emergencyContact)
    <div class="info-box" style="margin-bottom: 24px;">
        <div class="label">Contact d'Urgence</div>
        <div class="value">{{ $emergencyContact->name }} — {{ $emergencyContact->phone }}</div>
    </div>
    @endif

    <h2 class="section">Historique des Interventions</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Motif</th>
                <th>Température</th>
                <th>Soins</th>
                <th>Décision</th>
            </tr>
        </thead>
        <tbody>
            @forelse($history as $item)
            <tr>
                <td>{{ $item->arrival_time->format('d/m/Y H:i') }}</td>
                <td>{{ $item->motive }}</td>
                <td>{{ $item->temperature ? $item->temperature . '°C' : '-' }}</td>
                <td>{{ $item->care_notes ?? '-' }}</td>
                <td>{{ \App\Modules\Infirmary\Domain\Models\Intervention::DECISIONS[$item->decision] ?? $item->decision }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5">Aucune intervention enregistrée.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
