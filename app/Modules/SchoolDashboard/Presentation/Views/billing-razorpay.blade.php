@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-16 bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center">
    <div class="w-14 h-14 rounded-2xl bg-[#031C5B] text-white flex items-center justify-center mx-auto mb-4">
        <i class="ph ph-credit-card text-2xl"></i>
    </div>
    <h2 class="text-lg font-bold text-slate-900">Redirection vers Razorpay…</h2>
    <p class="text-sm text-slate-500 mt-2">Une fenêtre de paiement sécurisée va s'ouvrir. Ne fermez pas cette page.</p>
    <button id="rzp-open-btn" type="button" class="mt-6 flex items-center justify-center gap-2 bg-[#031C5B] text-white px-6 py-3 rounded-xl text-sm font-bold hover:bg-blue-900 transition cursor-pointer w-full">
        Ouvrir le paiement Razorpay
    </button>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    function openRazorpayCheckout() {
        const options = {
            key: @json($key),
            order_id: @json($orderId),
            amount: @json($amount),
            currency: @json($currency),
            name: @json($school->name),
            description: @json($invoice ? "Facture {$invoice->invoice_number}" : 'Recharge portefeuille Academia Pay'),
            callback_url: @json($callbackUrl),
            prefill: {
                email: @json($school->contact_email ?? '')
            }
        };
        const rzp = new Razorpay(options);
        rzp.open();
    }

    document.getElementById('rzp-open-btn').addEventListener('click', openRazorpayCheckout);
    // Also try opening automatically — some browsers block the very first
    // popup without a user gesture, hence the manual button as a fallback.
    window.addEventListener('load', openRazorpayCheckout);
</script>
@endsection
