@extends('ParentPortal::layout')

@section('title', 'Carte Scolaire - ' . $child->first_name)

@section('content')

<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Carte Scolaire</h1>
    <p class="text-sm font-medium text-slate-500 mt-1">Carte d'identité scolaire de {{ $child->first_name }}, telle que configurée par l'établissement.</p>
</div>

<div class="flex flex-wrap gap-8 justify-center bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] p-8">
    @include('SchoolDashboard::cards._card_face', ['face' => 'front'])
    @include('SchoolDashboard::cards._card_face', ['face' => 'back'])
</div>

<div class="mt-4 flex justify-center">
    <button type="button" onclick="window.print()"
            class="inline-flex items-center gap-2 bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs px-5 py-2.5 rounded-2xl transition shadow-md shadow-blue-950/20 print:hidden">
        <i class="ph-bold ph-printer"></i>
        <span>Imprimer</span>
    </button>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
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
@endpush
