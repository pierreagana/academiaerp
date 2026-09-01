<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;
use App\Modules\SuperAdmin\Domain\Models\PaymentGateway;
use App\Support\Payments\CurrencyCode;
use App\Support\Payments\GatewayCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WalletController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;
        $wallet = $school->getOrCreateWallet();
        $transactions = $wallet->transactions()->orderByDesc('created_at')->paginate(15);
        $gateways = PaymentGateway::where('status', 'active')->where('slug', '!=', 'academia_pay')->orderBy('id')->get();

        return view('SchoolDashboard::wallet', compact('wallet', 'transactions', 'gateways'));
    }

    public function recharge(Request $request, GatewayCheckoutService $checkoutService)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
            'gateway' => ['required', 'string'],
        ]);

        $school = auth()->user()->school;
        $wallet = $school->getOrCreateWallet();
        $gateway = PaymentGateway::where('slug', $data['gateway'])->where('status', 'active')->first();

        if (!$gateway) {
            return redirect()->route('school.wallet')->with('error', 'Cette passerelle de paiement n\'est pas disponible.');
        }

        $reference = "WALLET-{$wallet->id}-" . uniqid();
        $currencyIso = CurrencyCode::iso(GlobalSetting::where('key', 'currency')->value('value') ?? 'Franc CFA (XOF)');

        $result = $checkoutService->createCheckout(
            gateway: $gateway,
            reference: $reference,
            amount: (float) $data['amount'],
            currencyIso: $currencyIso,
            description: "Recharge portefeuille Academia Pay — {$school->name}",
            successUrl: route('school.billing.success'),
            cancelUrl: route('school.billing.cancel'),
            payerEmail: $school->contact_email ?? auth()->user()->email,
            payerName: $school->name,
        );

        if ($result['type'] === 'error') {
            return redirect()->route('school.wallet')->with('error', $result['message']);
        }

        if ($result['type'] === 'razorpay_checkout') {
            return view('SchoolDashboard::billing-razorpay', [
                'orderId' => $result['order_id'],
                'key' => $result['key'],
                'amount' => $result['amount'],
                'currency' => $result['currency'],
                'invoice' => null,
                'school' => $school,
                'callbackUrl' => route('school.billing.success'),
            ]);
        }

        return redirect()->away($result['url']);
    }
}
