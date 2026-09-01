<?php

namespace App\Support\Payments;

use App\Modules\SuperAdmin\Domain\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Creates a hosted checkout for one of the verified gateways and returns
 * either a redirect URL or (Razorpay only — it has no hosted redirect page)
 * the data needed to render its client-side Checkout.js modal.
 *
 * Every request shape below was verified against each provider's official
 * docs (see the plan doc) rather than guessed — amount units and auth scheme
 * differ per gateway, so nothing here is copy-pasted between methods.
 */
class GatewayCheckoutService
{
    public function createCheckout(
        PaymentGateway $gateway,
        string $reference,
        float $amount,
        string $currencyIso,
        string $description,
        string $successUrl,
        string $cancelUrl,
        string $payerEmail,
        ?string $payerName = null
    ): array {
        return match ($gateway->slug) {
            'stripe' => $this->createStripeSession($gateway, $reference, $amount, $currencyIso, $description, $successUrl, $cancelUrl),
            'razorpay' => $this->createRazorpayOrder($gateway, $reference, $amount, $currencyIso),
            'paystack' => $this->createPaystackTransaction($gateway, $reference, $amount, $currencyIso, $successUrl, $payerEmail),
            'flutterwave' => $this->createFlutterwavePayment($gateway, $reference, $amount, $currencyIso, $description, $successUrl, $payerEmail, $payerName),
            'wave' => $this->createWaveSession($gateway, $reference, $amount, $currencyIso, $successUrl, $cancelUrl),
            default => ['type' => 'error', 'message' => "La passerelle {$gateway->name} ne prend pas en charge le paiement en ligne direct."],
        };
    }

    private function createStripeSession(PaymentGateway $gateway, string $reference, float $amount, string $currencyIso, string $description, string $successUrl, string $cancelUrl): array
    {
        $response = Http::asForm()->timeout(15)
            ->withBasicAuth((string) $gateway->secret_key, '')
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'client_reference_id' => $reference,
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => strtolower($currencyIso),
                        'unit_amount' => CurrencyCode::toSmallestUnit($amount, $currencyIso),
                        'product_data' => ['name' => $description],
                    ],
                ]],
            ]);

        if (!$response->successful()) {
            Log::warning('Stripe checkout session creation failed', ['status' => $response->status(), 'body' => $response->body()]);
            return ['type' => 'error', 'message' => 'Impossible de créer la session de paiement Stripe.'];
        }

        return ['type' => 'redirect', 'url' => $response->json('url')];
    }

    private function createRazorpayOrder(PaymentGateway $gateway, string $reference, float $amount, string $currencyIso): array
    {
        $amountSmallest = CurrencyCode::toSmallestUnit($amount, $currencyIso);

        $response = Http::timeout(15)
            ->withBasicAuth((string) $gateway->api_key, (string) $gateway->secret_key)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $amountSmallest,
                'currency' => $currencyIso,
                'receipt' => $reference,
            ]);

        if (!$response->successful()) {
            Log::warning('Razorpay order creation failed', ['status' => $response->status(), 'body' => $response->body()]);
            return ['type' => 'error', 'message' => 'Impossible de créer la commande Razorpay.'];
        }

        // Razorpay has no hosted redirect page — the caller must render
        // Checkout.js client-side with these values.
        return [
            'type' => 'razorpay_checkout',
            'order_id' => $response->json('id'),
            'key' => $gateway->api_key,
            'amount' => $amountSmallest,
            'currency' => $currencyIso,
        ];
    }

    private function createPaystackTransaction(PaymentGateway $gateway, string $reference, float $amount, string $currencyIso, string $callbackUrl, string $payerEmail): array
    {
        $response = Http::timeout(15)
            ->withToken((string) $gateway->secret_key)
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $payerEmail,
                'amount' => CurrencyCode::toSmallestUnit($amount, $currencyIso),
                'currency' => $currencyIso,
                'reference' => $reference,
                'callback_url' => $callbackUrl,
            ]);

        if (!$response->successful() || !$response->json('status')) {
            Log::warning('PayStack transaction initialize failed', ['status' => $response->status(), 'body' => $response->body()]);
            return ['type' => 'error', 'message' => "Impossible d'initialiser la transaction PayStack."];
        }

        return ['type' => 'redirect', 'url' => $response->json('data.authorization_url')];
    }

    private function createFlutterwavePayment(PaymentGateway $gateway, string $reference, float $amount, string $currencyIso, string $description, string $redirectUrl, string $payerEmail, ?string $payerName): array
    {
        $response = Http::timeout(15)
            ->withToken((string) $gateway->secret_key)
            ->post('https://api.flutterwave.com/v3/payments', [
                'tx_ref' => $reference,
                // Flutterwave wants the plain decimal amount, unlike Stripe/PayStack/Razorpay.
                'amount' => (string) $amount,
                'currency' => $currencyIso,
                'redirect_url' => $redirectUrl,
                'customer' => [
                    'email' => $payerEmail,
                    'name' => $payerName ?? $description,
                ],
            ]);

        if (!$response->successful() || $response->json('status') !== 'success') {
            Log::warning('Flutterwave payment initiation failed', ['status' => $response->status(), 'body' => $response->body()]);
            return ['type' => 'error', 'message' => "Impossible d'initialiser le paiement Flutterwave."];
        }

        return ['type' => 'redirect', 'url' => $response->json('data.link')];
    }

    private function createWaveSession(PaymentGateway $gateway, string $reference, float $amount, string $currencyIso, string $successUrl, string $cancelUrl): array
    {
        $response = Http::timeout(15)
            ->withToken((string) $gateway->api_key)
            ->post('https://api.wave.com/v1/checkout/sessions', [
                // Wave also wants a plain decimal amount, like Flutterwave.
                'amount' => (string) round($amount),
                'currency' => $currencyIso,
                'success_url' => $successUrl,
                'error_url' => $cancelUrl,
                'client_reference' => $reference,
            ]);

        if (!$response->successful()) {
            Log::warning('Wave checkout session creation failed', ['status' => $response->status(), 'body' => $response->body()]);
            return ['type' => 'error', 'message' => 'Impossible de créer la session de paiement Wave.'];
        }

        return ['type' => 'redirect', 'url' => $response->json('wave_launch_url')];
    }
}
