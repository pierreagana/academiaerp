@php
    $fields = $face === 'front' ? ($template->front_fields ?? []) : ($template->back_fields ?? []);
    $catalog = $template->fieldCatalog();
    $isLandscape = $template->orientation === 'landscape';
    $faceWidth = $isLandscape ? '460px' : '320px';
@endphp
<div style="width: {{ $faceWidth }}; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); break-inside: avoid; margin-bottom: 16px;">
    <div style="background-color: {{ $template->primary_color }}; padding: 20px; display: flex; align-items: center; gap: 12px;">
        <div style="width: 40px; height: 40px; border-radius: 9999px; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
            @if($template->logo_path)
                <img src="{{ asset('storage/' . $template->logo_path) }}" style="width: 100%; height: 100%; object-fit: cover;">
            @else
                <i class="ph-fill ph-graduation-cap" style="color: {{ $template->primary_color }}; font-size: 18px;"></i>
            @endif
        </div>
        <div style="color: #fff; min-width: 0;">
            <p style="font-weight: 800; font-size: 15px; margin: 0; line-height: 1.2;">{{ $school->name ?? 'École' }}</p>
            <p style="font-size: 11px; opacity: .8; margin: 2px 0 0;">{{ $school->city ?? $school->address ?? '' }}</p>
        </div>
    </div>
    <div style="position: relative; padding: 20px; background-color: {{ $template->background_color }}; overflow: hidden; min-height: 150px;">
        @if($template->watermark_path)
            <img src="{{ asset('storage/' . $template->watermark_path) }}" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; opacity: .1; padding: 24px;">
        @endif

        <div style="position: relative; {{ $face === 'front' && $isLandscape ? 'display: flex; gap: 16px; align-items: flex-start;' : '' }}">
            @if($face === 'front')
                <div style="{{ $isLandscape ? 'flex-shrink: 0;' : 'margin-bottom: 16px;' }} {{ !$isLandscape && $template->photo_position === 'center' ? 'display: flex; justify-content: center;' : '' }} {{ !$isLandscape && $template->photo_position === 'right' ? 'display: flex; justify-content: flex-end;' : '' }}">
                    <div style="width: {{ $isLandscape ? '80px' : '96px' }}; height: {{ $isLandscape ? '80px' : '96px' }}; border-radius: 12px; background: #f1f5f9; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                        @if($card['photo'])
                            <img src="{{ $card['photo'] }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <i class="ph-bold ph-user" style="font-size: 28px; color: #cbd5e1;"></i>
                        @endif
                    </div>
                </div>
            @endif
            <div style="{{ $isLandscape ? 'display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; flex: 1;' : '' }}">
                @foreach($fields as $key)
                    <div style="{{ $isLandscape ? '' : 'margin-bottom: 12px;' }}">
                        <p style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; opacity: .6; color: {{ $template->text_color }}; margin: 0 0 2px;">{{ $catalog[$key] ?? $key }}</p>
                        <p style="font-size: 15px; font-weight: 700; color: {{ $template->text_color }}; margin: 0;">{{ $card['data'][$key] ?? '-' }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        @if($face === 'front' && $card['qr'])
            <div class="qrcode-print" data-qr="{{ $card['qr'] }}" style="position: absolute; bottom: 16px; right: 16px; background: #fff; padding: 4px; border-radius: 6px;"></div>
        @endif
    </div>
</div>
