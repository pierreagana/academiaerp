<?php

namespace App\Modules\SuperAdmin\Application\Services\PaymentWebhooks;

use App\Modules\SuperAdmin\Domain\Models\PaymentGateway;
use Illuminate\Http\Request;

interface WebhookVerifier
{
    /**
     * Verify the request actually came from the gateway (signature/hash check).
     */
    public function verify(Request $request, PaymentGateway $gateway): bool;

    /**
     * Whether the verified payload represents a successful payment.
     */
    public function isPaymentSuccessful(array $payload): bool;

    /**
     * The merchant reference (expected to match Invoice::invoice_number, or a
     * "WALLET-{id}-..." recharge reference), or null if absent.
     */
    public function extractReference(array $payload): ?string;

    /**
     * The confirmed paid amount, in the gateway's decimal major unit (e.g.
     * 1000.00 XOF, not 1000 in some smallest-unit encoding) — needed to credit
     * a wallet recharge for the exact amount the gateway actually confirmed,
     * not just whatever amount we originally requested.
     */
    public function extractAmount(array $payload): ?float;
}
