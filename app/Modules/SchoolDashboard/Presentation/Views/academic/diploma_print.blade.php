<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Diplôme — {{ $award->type->name ?? '' }}</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #0F172A; padding: 32px; background: #F1F5F9; display: flex; flex-direction: column; align-items: center; }
        .print-bar { margin-bottom: 24px; display: flex; align-items: center; gap: 14px; }
        .print-bar button { padding: 10px 20px; background: #031C5B; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 13px; }
        @media print {
            .print-bar { display: none; }
            body { padding: 0; background: #fff; }
        }
        .diploma {
            position: relative;
            background-color: {{ $template->background_color }};
            @if($template->background_image_path)
                background-image: url('{{ asset('storage/' . $template->background_image_path) }}');
                background-size: cover;
                background-position: center;
            @endif
            color: {{ $template->text_color }};
            width: {{ $template->orientation === 'landscape' ? '900px' : '640px' }};
            min-height: {{ $template->orientation === 'landscape' ? '640px' : '900px' }};
            padding: 60px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            @if($template->layout === 'modern')
                border-left: 16px solid {{ $template->primary_color }};
                text-align: left;
                align-items: flex-start;
            @elseif($template->border_style === 'classic')
                border: 14px double {{ $template->primary_color }};
            @elseif($template->border_style === 'modern')
                border: 4px solid {{ $template->primary_color }};
            @endif
            @if($template->layout === 'elegant')
                font-family: Georgia, 'Times New Roman', serif;
            @endif
        }
        .diploma .corner { position: absolute; width: 22px; height: 22px; border-color: {{ $template->primary_color }}; }
        .diploma .corner-tl { top: 14px; left: 14px; border-top: 2px solid; border-left: 2px solid; }
        .diploma .corner-tr { top: 14px; right: 14px; border-top: 2px solid; border-right: 2px solid; }
        .diploma .corner-bl { bottom: 14px; left: 14px; border-bottom: 2px solid; border-left: 2px solid; }
        .diploma .corner-br { bottom: 14px; right: 14px; border-bottom: 2px solid; border-right: 2px solid; }
        .diploma .header-row { display: flex; align-items: center; gap: 12px; }
        .diploma .header-row.centered { flex-direction: column; gap: 4px; }
        .diploma .school { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 4px; opacity: .7; }
        .diploma .title { font-size: 44px; font-weight: 800; letter-spacing: 2px; margin-top: 22px; color: {{ $template->primary_color }}; }
        .diploma .subtitle { font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; margin-top: 10px; opacity: .8; }
        .diploma .recipient { font-size: 36px; margin-top: 26px; }
        .diploma .recipient.cursive { font-weight: 700; font-family: 'Brush Script MT', cursive; }
        .diploma .recipient.bold { font-weight: 800; }
        .diploma .body-text { font-size: 15px; line-height: 1.7; margin-top: 26px; max-width: 620px; white-space: pre-line; }
        .diploma .signatures { display: flex; align-items: flex-end; justify-content: space-between; width: 100%; margin-top: auto; padding-top: 60px; gap: 48px; }
        .diploma .signature { flex: 1; text-align: center; }
        .diploma .signature .name { font-size: 15px; font-weight: 700; border-top: 1px solid currentColor; padding-top: 8px; margin-top: 30px; }
        .diploma .signature .role { font-size: 12px; opacity: .7; margin-top: 2px; }
        .diploma .seal { position: absolute; transform: translate(-50%, -50%); width: 64px; height: 64px; object-fit: contain; opacity: .9; }
        .diploma .logo { position: absolute; transform: translate(-50%, -50%); width: 56px; height: 56px; object-fit: contain; }
    </style>
</head>
<body>
    <div class="print-bar">
        <button onclick="window.print()">Imprimer / Enregistrer en PDF</button>
    </div>

    @php
        $logoPos = $template->fields_layout['logo'] ?? ['x' => 50, 'y' => 12];
        $sealPos = $template->fields_layout['seal'] ?? ['x' => 50, 'y' => 88];
    @endphp

    <div class="diploma">
        @if($template->layout === 'elegant')
            <span class="corner corner-tl"></span>
            <span class="corner corner-tr"></span>
            <span class="corner corner-bl"></span>
            <span class="corner corner-br"></span>
        @endif

        @if($template->logo_path)
            <img src="{{ asset('storage/' . $template->logo_path) }}" class="logo" style="left: {{ $logoPos['x'] }}%; top: {{ $logoPos['y'] }}%;">
        @endif
        @if($template->seal_path)
            <img src="{{ asset('storage/' . $template->seal_path) }}" class="seal" style="left: {{ $sealPos['x'] }}%; top: {{ $sealPos['y'] }}%;">
        @endif

        <div class="header-row {{ $template->layout === 'modern' ? '' : 'centered' }}">
            <p class="school">{{ $school->name ?? '' }}</p>
        </div>
        <p class="title">{{ $template->title }}</p>
        <p class="subtitle">{{ $template->subtitle }}</p>
        <p class="recipient {{ $template->layout === 'modern' ? 'bold' : 'cursive' }}">{{ $award->recipientName ?? '' }}</p>
        <p class="body-text">{{ $body }}</p>

        <div class="signatures">
            <div class="signature">
                @if($template->signature_1_name)
                    <p class="name">{{ $template->signature_1_name }}</p>
                @else
                    <div style="border-top:1px solid currentColor; padding-top:8px; margin-top:30px; opacity:.3;">&nbsp;</div>
                @endif
                <p class="role">{{ $template->signature_1_title }}</p>
            </div>
            <div class="signature">
                @if($template->signature_2_name)
                    <p class="name">{{ $template->signature_2_name }}</p>
                @else
                    <div style="border-top:1px solid currentColor; padding-top:8px; margin-top:30px; opacity:.3;">&nbsp;</div>
                @endif
                <p class="role">{{ $template->signature_2_title }}</p>
            </div>
        </div>
    </div>
</body>
</html>
