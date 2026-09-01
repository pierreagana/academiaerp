<?php

namespace App\Modules\SuperAdmin\Application\Services\PaymentWebhooks;

use App\Modules\SuperAdmin\Domain\Models\PaymentGateway;
use App\Support\Payments\CurrencyCode;
use Illuminate\Http\Request;

/**
 * Stripe signs webhooks via the `Stripe-Signature` header: "t=<unix ts>,v1=<hex hmac>".
 * The signed string is "{timestamp}.{raw body}", HMAC-SHA256 keyed by the webhook secret.
 * https://docs.stripe.com/webhooks/signature
 */
class StripeWebhookVerifier implements WebhookVerifier
{
    private const TOLERANCE_SECONDS = 300;

    public function verify(Request $request, PaymentGateway $gateway): bool
    {
        $header = (string) $request->header('Stripe-Signature');
        $secret = $gateway->webhook_secret;

        if ($header === '' || empty($secret)) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);
            $parts[$key][] = $value;
        }

        $timestamp = $parts['t'][0] ?? null;
        $signatures = $parts['v1'] ?? [];

        if (!$timestamp || empty($signatures)) {
            return false;
        }

        if (abs(time() - (int) $timestamp) > self::TOLERANCE_SECONDS) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $request->getContent(), $secret);

        foreach ($signatures as $signature) {
            if ($signature && hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }

    public function isPaymentSuccessful(array $payload): bool
    {
        $type = strtolower((string) ($payload['type'] ?? ''));

        return str_contains($type, 'succeeded') || str_contains($type, 'completed') || str_contains($type, 'invoice.paid');
    }

    public function extractReference(array $payload): ?string
    {
        $object = $payload['data']['object'] ?? [];

        return $object['metadata']['invoice_number']
            ?? $object['client_reference_id']
            ?? null;
    }

    public function extractAmount(array $payload): ?float
    {
        $object = $payload['data']['object'] ?? [];
        $amountTotal = $object['amount_total'] ?? null;

        if ($amountTotal === null) {
            return null;
        }

        $currency = strtoupper((string) ($object['currency'] ?? ''));

        return CurrencyCode::isZeroDecimal($currency) ? (float) $amountTotal : $amountTotal / 100;
    }
}
