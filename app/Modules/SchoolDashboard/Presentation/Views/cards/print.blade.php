<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cartes — {{ \App\Modules\Cards\Domain\Models\CardTemplate::TYPES[$type] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('vendor/qrcodejs/qrcode.min.js') }}"></script>
    <style>
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #0F172A; padding: 32px; background: #F1F5F9; }
        .print-bar { margin-bottom: 24px; display: flex; align-items: center; gap: 14px; }
        .print-bar button { padding: 10px 20px; background: #031C5B; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 13px; }
        .print-bar span { color: #64748B; font-size: 13px; font-weight: 600; }
        .sheet { display: flex; flex-wrap: wrap; gap: 24px; }
        @media print {
            .print-bar { display: none; }
            body { padding: 0; background: #fff; }
            .sheet { gap: 16px; }
        }
    </style>
</head>
<body>
    <div class="print-bar">
        <button onclick="window.print()">Imprimer / Enregistrer en PDF</button>
        <span>{{ $cards->count() }} carte(s) — {{ \App\Modules\Cards\Domain\Models\CardTemplate::TYPES[$type] }} (Recto + Verso)</span>
    </div>

    <div class="sheet">
        @forelse($cards as $card)
            @include('SchoolDashboard::cards._card_face', ['face' => 'front'])
            @include('SchoolDashboard::cards._card_face', ['face' => 'back'])
        @empty
            <p style="color: #64748B;">Aucune personne sélectionnée.</p>
        @endforelse
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.qrcode-print').forEach(function (el) {
                var data = el.getAttribute('data-qr');
                if (data) {
                    new QRCode(el, { text: data, width: 48, height: 48, correctLevel: QRCode.CorrectLevel.M });
                }
            });
        });
    </script>
</body>
</html>
