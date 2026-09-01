<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Modules\SuperAdmin\Application\Services\PaymentWebhooks\FlutterwaveWebhookVerifier;
use App\Modules\SuperAdmin\Application\Services\PaymentWebhooks\PaystackWebhookVerifier;
use App\Modules\SuperAdmin\Application\Services\PaymentWebhooks\RazorpayWebhookVerifier;
use App\Modules\SuperAdmin\Application\Services\PaymentWebhooks\StripeWebhookVerifier;
use App\Modules\SuperAdmin\Application\Services\PaymentWebhooks\WaveWebhookVerifier;
use App\Modules\SuperAdmin\Application\Services\PaymentWebhooks\WebhookVerifier;
use App\Modules\Finance\Domain\Models\Wallet;
use App\Modules\SuperAdmin\Domain\Models\Invoice;
use App\Modules\SuperAdmin\Domain\Models\PaymentGateway;
use App\Modules\SuperAdmin\Domain\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class SubscriptionWebhookController extends Controller
{
    private const VERIFIERS = [
        'stripe' => StripeWebhookVerifier::class,
        'razorpay' => RazorpayWebhookVerifier::class,
        'flutterwave' => FlutterwaveWebhookVerifier::class,
        'paystack' => PaystackWebhookVerifier::class,
        'wave' => WaveWebhookVerifier::class,
    ];

    public function handle(Request $request, string $gateway): Response
    {
        $verifierClass = self::VERIFIERS[$gateway] ?? null;
        if (!$verifierClass) {
            abort(404);
        }

        $paymentGateway = PaymentGateway::where('slug', $gateway)->first();
        if (!$paymentGateway || !$paymentGateway->isActive()) {
            abort(404);
        }

        /** @var WebhookVerifier $verifier */
        $verifier = app($verifierClass);
        $verified = $verifier->verify($request, $paymentGateway);

        $payload = json_decode($request->getContent(), true) ?? [];
        $reference = $verified ? $verifier->extractReference($payload) : null;
        $isSuccess = $verified && $verifier->isPaymentSuccessful($payload);

        $invoice = null;
        $walletCredited = null;
        $logMessage = null;

        if ($isSuccess && $reference) {
            if (str_starts_with($reference, 'WALLET-')) {
                // Recharge reference format: WALLET-{walletId}-{token}. The amount
                // credited is whatever the gateway confirms was actually paid, not
                // an amount we merely remembered requesting.
                preg_match('/^WALLET-(\d+)-/', $reference, $matches);
                $wallet = isset($matches[1]) ? Wallet::find((int) $matches[1]) : null;
                $amount = $verifier->extractAmount($payload);

                if ($wallet && $amount && $amount > 0) {
                    $wallet->credit($amount, $reference, $gateway, "Recharge via {$gateway}");
                    $walletCredited = $wallet;
                    $logMessage = "Webhook {$gateway} vérifié — portefeuille #{$wallet->id} rechargé de {$amount}";
                }
            } else {
                $invoice = Invoice::where('invoice_number', $reference)->first();
                if ($invoice && $invoice->status !== 'paid') {
                    $invoice->update(['status' => 'paid']);
                }
                if ($invoice) {
                    $logMessage = "Webhook {$gateway} vérifié — facture {$reference} marquée payée";
                }
            }
        }

        SystemLog::create([
            'level' => $verified ? 'info' : 'warning',
            'message' => $logMessage ?? ($verified ? "Webhook {$gateway} vérifié" : "Webhook {$gateway} rejeté (signature invalide)"),
            'context' => [
                'gateway' => $gateway,
                'verified' => $verified,
                'reference' => $reference,
                'invoice_id' => $invoice?->id,
                'wallet_id' => $walletCredited?->id,
                'ip' => $request->ip(),
            ],
            'source' => 'payment_webhook',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        if (!$verified) {
            return response('Invalid signature', 401);
        }

        return response('OK', 200);
    }
}
