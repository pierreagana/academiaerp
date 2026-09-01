<?php

namespace App\Modules\SuperAdmin\Application\Services\PaymentWebhooks;

use App\Modules\SuperAdmin\Domain\Models\PaymentGateway;
use Illuminate\Http\Request;

/**
 * Flutterwave does not HMAC-sign webhooks — it echoes back the merchant-configured
 * secret hash verbatim in the `verif-hash` header, to be compared directly.
 * https://flutterwave.com/support/integrations/what-is-a-secret-hash
 */
class FlutterwaveWebhookVerifier implements WebhookVerifier
{
    public function verify(Request $request, PaymentGateway $gateway): bool
    {
        $header = (string) $request->header('verif-hash');
        $secret = $gateway->webhook_secret;

        if ($header === '' || empty($secret)) {
            return false;
        }

        return hash_equals((string) $secret, $header);
    }

    public function isPaymentSuccessful(array $payload): bool
    {
        $status = strtolower((string) ($payload['data']['status'] ?? $payload['status'] ?? ''));

        return $status === 'successful' || $status === 'success';
    }

    public function extractReference(array $payload): ?string
    {
        return $payload['data']['tx_ref'] ?? $payload['txRef'] ?? null;
    }

    public function extractAmount(array $payload): ?float
    {
        // Flutterwave's amount is already the plain decimal major unit — no
        // smallest-unit conversion needed, unlike Stripe/Razorpay/PayStack.
        $amount = $payload['data']['amount'] ?? $payload['amount'] ?? null;

        return $amount !== null ? (float) $amount : null;
    }
}
