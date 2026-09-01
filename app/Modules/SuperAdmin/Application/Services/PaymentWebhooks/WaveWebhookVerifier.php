<?php

namespace App\Modules\SuperAdmin\Application\Services\PaymentWebhooks;

use App\Modules\SuperAdmin\Domain\Models\PaymentGateway;
use Illuminate\Http\Request;

/**
 * Wave signs webhooks via the `Wave-Signature` header: "t=<unix ts>,v1=<hex hmac>".
 * Unlike Stripe, the signed string concatenates timestamp and raw body directly,
 * with NO separator: HMAC-SHA256("{timestamp}{raw body}", webhook secret).
 * https://docs.wave.com/webhook
 */
class WaveWebhookVerifier implements WebhookVerifier
{
    private const TOLERANCE_SECONDS = 300;

    public function verify(Request $request, PaymentGateway $gateway): bool
    {
        $header = (string) $request->header('Wave-Signature');
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

        // No "." between timestamp and body — Wave's own quirk, distinct from Stripe.
        $expected = hash_hmac('sha256', $timestamp . $request->getContent(), $secret);

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
        $paymentStatus = strtolower((string) ($payload['data']['payment_status'] ?? ''));

        return $type === 'checkout.session.completed' && $paymentStatus === 'succeeded';
    }

    public function extractReference(array $payload): ?string
    {
        return $payload['data']['client_reference'] ?? null;
    }

    public function extractAmount(array $payload): ?float
    {
        // Wave's amount is already the plain decimal major unit, like Flutterwave.
        $amount = $payload['data']['amount'] ?? null;

        return $amount !== null ? (float) $amount : null;
    }
}
