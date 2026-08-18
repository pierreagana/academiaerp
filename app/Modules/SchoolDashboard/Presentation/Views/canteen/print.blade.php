<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiches Cuisine</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #0F172A; padding: 40px; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        p.subtitle { color: #64748B; margin-top: 0; margin-bottom: 24px; font-size: 13px; }
        h2.day { font-size: 15px; margin-top: 28px; margin-bottom: 8px; border-bottom: 2px solid #0F172A; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 8px; }
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

    <h1>Fiches Cuisine — {{ $school->name ?? 'Établissement' }}</h1>
    <p class="subtitle">Semaine du {{ $current->format('d/m/Y') }} au {{ $current->copy()->addDays(4)->format('d/m/Y') }}</p>

    @php
        $slotLabels = ['breakfast' => 'Petit Déjeuner', 'starter' => 'Entrée', 'main' => 'Plat', 'dessert' => 'Dessert'];
        $grouped = $items->groupBy(fn ($item) => $item->date->format('Y-m-d'));
    @endphp

    @forelse($grouped as $dateKey => $dayItems)
        <h2 class="day">{{ \Illuminate\Support\Carbon::parse($dateKey)->translatedFormat('l d F Y') }}</h2>
        <table>
            <thead>
                <tr>
                    <th>Créneau</th>
                    <th>Plat</th>
                    <th>Description</th>
                    <th>Allergènes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dayItems->sortBy(fn($i) => array_search($i->slot, ['breakfast','starter','main','dessert'])) as $item)
                <tr>
                    <td>{{ $slotLabels[$item->slot] ?? $item->slot }}</td>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->description ?? '-' }}</td>
                    <td>{{ $item->allergens ? implode(', ', $item->allergens) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p>Aucun plat planifié pour cette semaine.</p>
    @endforelse
</body>
</html>
