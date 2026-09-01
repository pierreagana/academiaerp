<?php

namespace App\Modules\SuperAdmin\Application\Services\PaymentWebhooks;

use App\Modules\SuperAdmin\Domain\Models\PaymentGateway;
use App\Support\Payments\CurrencyCode;
use Illuminate\Http\Request;

/**
 * PayStack signs webhooks via the `x-paystack-signature` header: a hex HMAC-SHA512
 * of the raw request body, keyed by the account's SECRET KEY (there is no separate
 * webhook secret for PayStack, unlike the other three gateways).
 * https://paystack.com/docs/payments/webhooks/
 */
class PaystackWebhookVerifier implements WebhookVerifier
{
    public function verify(Request $request, PaymentGateway $gateway): bool
    {
        $signature = (string) $request->header('x-paystack-signature');
        $secret = $gateway->secret_key;

        if ($signature === '' || empty($secret)) {
            return false;
        }

        $expected = hash_hmac('sha512', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    public function isPaymentSuccessful(array $payload): bool
    {
        $event = strtolower((string) ($payload['event'] ?? ''));

        return $event === 'charge.success';
    }

    public function extractReference(array $payload): ?string
    {
        return $payload['data']['reference'] ?? null;
    }

    public function extractAmount(array $payload): ?float
    {
        $amount = $payload['data']['amount'] ?? null;
        if ($amount === null) {
            return null;
        }

        $currency = strtoupper((string) ($payload['data']['currency'] ?? ''));

        return CurrencyCode::isZeroDecimal($currency) ? (float) $amount : $amount / 100;
    }
}
