<?php

namespace App\Modules\SuperAdmin\Application\Services\PaymentWebhooks;

use App\Modules\SuperAdmin\Domain\Models\PaymentGateway;
use App\Support\Payments\CurrencyCode;
use Illuminate\Http\Request;

/**
 * Razorpay signs webhooks via the `X-Razorpay-Signature` header: a hex HMAC-SHA256
 * of the raw request body, keyed by the webhook secret configured in the dashboard.
 * https://razorpay.com/docs/webhooks/validate-test/
 */
class RazorpayWebhookVerifier implements WebhookVerifier
{
    public function verify(Request $request, PaymentGateway $gateway): bool
    {
        $signature = (string) $request->header('X-Razorpay-Signature');
        $secret = $gateway->webhook_secret;

        if ($signature === '' || empty($secret)) {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    public function isPaymentSuccessful(array $payload): bool
    {
        $event = strtolower((string) ($payload['event'] ?? ''));

        return str_contains($event, 'payment.captured') || str_contains($event, 'order.paid');
    }

    public function extractReference(array $payload): ?string
    {
        $entity = $payload['payload']['payment']['entity']
            ?? $payload['payload']['order']['entity']
            ?? [];

        // `receipt` is what GatewayCheckoutService::createRazorpayOrder() sets
        // (the actual Razorpay order field for a merchant reference); `notes`
        // is a separate free-form map, kept as a fallback for orders created
        // some other way that used it instead.
        return $entity['receipt'] ?? $entity['notes']['invoice_number'] ?? null;
    }

    public function extractAmount(array $payload): ?float
    {
        $entity = $payload['payload']['payment']['entity']
            ?? $payload['payload']['order']['entity']
            ?? [];

        $amount = $entity['amount'] ?? null;
        if ($amount === null) {
            return null;
        }

        $currency = strtoupper((string) ($entity['currency'] ?? ''));

        return CurrencyCode::isZeroDecimal($currency) ? (float) $amount : $amount / 100;
    }
}
