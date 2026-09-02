<?php

namespace App\Modules\ParentPortal\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Application\Services\WalletRechargeService;
use App\Modules\Finance\Domain\Models\WalletRechargeRequest;
use App\Modules\ParentPortal\Application\Services\ParentPortalService;
use App\Modules\SuperAdmin\Domain\Models\PaymentGateway;
use App\Support\Payments\GatewayCheckoutService;
use Illuminate\Http\Request;

class MobileWalletController extends Controller
{
    /**
     * Single source of truth for "which payment methods can a parent use
     * right now" — reused for both fee payment and wallet recharge, so the
     * app never has its own hardcoded list to fall out of sync with
     * /superadmin/payment-gateways.
     */
    public function paymentMethods()
    {
        return response()->json([
            'methods' => PaymentGateway::where('status', 'active')->get(['slug', 'name']),
        ]);
    }

    public function show(Request $request)
    {
        $parent = $request->user();
        $wallet = $parent->getOrCreateWallet();

        $pending = WalletRechargeRequest::where('parent_id', $parent->id)
            ->where('status', WalletRechargeRequest::STATUS_PENDING)
            ->latest()
            ->first();

        return response()->json([
            'balance' => (float) $wallet->balance,
            'currency' => $wallet->currency,
            'pendingRecharge' => $pending ? [
                'amount' => (float) $pending->amount,
                'method' => $pending->method,
                'createdAt' => $pending->created_at->translatedFormat('d M Y'),
            ] : null,
            'transactions' => $wallet->transactions()->orderByDesc('created_at')->limit(30)->get()->map(fn ($t) => [
                'type' => $t->type,
                'amount' => (float) $t->amount,
                'balanceAfter' => (float) $t->balance_after,
                'description' => $t->description ?? '',
                'dateLabel' => $t->created_at->translatedFormat('d M Y'),
            ])->values(),
        ]);
    }

    /**
     * 'cash' creates a pending request a staff member confirms in person
     * (WalletRechargeService::approve() credits the wallet). Any other
     * method is only reachable if it's a real, active gateway — routed
     * through the exact same GatewayCheckoutService already used by
     * WalletController::recharge() for schools; the existing webhook
     * (SubscriptionWebhookController) already credits any Wallet row by id
     * from a `WALLET-{id}-...` reference regardless of owner type, so no
     * webhook changes are needed when a gateway like Wave goes active.
     */
    public function recharge(Request $request, WalletRechargeService $rechargeService, GatewayCheckoutService $checkoutService, ParentPortalService $parentPortalService)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
            'method' => ['required', 'string'],
        ]);

        $gateway = PaymentGateway::where('slug', $data['method'])->where('status', 'active')->first();
        if (!$gateway) {
            return response()->json(['message' => 'Ce moyen de paiement n\'est pas disponible.'], 422);
        }

        $parent = $request->user();

        if ($data['method'] === 'cash') {
            $firstChild = $parentPortalService->childrenOf($parent)->first();
            abort_if(!$firstChild, 404, "Aucun élève rattaché à votre compte.");

            $rechargeService->request($parent, (float) $data['amount'], 'cash', $firstChild->school_id);

            return response()->json([
                'type' => 'pending',
                'message' => 'Merci de déposer ce montant en espèces à l\'école — votre solde sera crédité après confirmation du personnel.',
            ]);
        }

        $wallet = $parent->getOrCreateWallet();
        $reference = "WALLET-{$wallet->id}-" . uniqid();

        $result = $checkoutService->createCheckout(
            gateway: $gateway,
            reference: $reference,
            amount: (float) $data['amount'],
            currencyIso: 'XOF',
            description: "Recharge portefeuille Academia Pay",
            successUrl: config('app.url'),
            cancelUrl: config('app.url'),
            payerEmail: $parent->email ?? 'parent@academiaerp.local',
            payerName: $parent->name,
        );

        if ($result['type'] === 'error') {
            return response()->json(['message' => $result['message'] ?? 'Erreur lors de l\'initialisation du paiement.'], 422);
        }

        return response()->json($result);
    }
}
