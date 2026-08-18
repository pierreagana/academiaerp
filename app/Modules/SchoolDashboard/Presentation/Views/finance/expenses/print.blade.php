<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Relevé des Dépenses</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #0F172A; padding: 40px; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        p.subtitle { color: #64748B; margin-top: 0; margin-bottom: 24px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th { text-align: left; background: #F8FAFC; padding: 8px 10px; border-bottom: 2px solid #E2E8F0; text-transform: uppercase; font-size: 10px; color: #64748B; }
        td { padding: 8px 10px; border-bottom: 1px solid #F1F5F9; }
        .total-row td { font-weight: bold; border-top: 2px solid #0F172A; }
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

    <h1>Relevé des Dépenses — {{ $school->name ?? 'Établissement' }}</h1>
    <p class="subtitle">Généré le {{ now()->format('d/m/Y à H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Référence</th>
                <th>Libellé</th>
                <th>Catégorie</th>
                <th>Bénéficiaire</th>
                <th>Statut</th>
                <th style="text-align: right;">Montant (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $e)
            <tr>
                <td>{{ $e->expense_date->format('d/m/Y') }}</td>
                <td>{{ $e->reference }}</td>
                <td>{{ $e->title }}</td>
                <td>{{ $e->category }}</td>
                <td>{{ $e->payee ?? '-' }}</td>
                <td>{{ \App\Modules\Finance\Domain\Models\Expense::STATUSES[$e->status] ?? $e->status }}</td>
                <td style="text-align: right;">{{ number_format($e->amount, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6">Total</td>
                <td style="text-align: right;">{{ number_format($expenses->sum('amount'), 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
