<?php

namespace App\Modules\Finance\Application\Services;

use App\Models\User;
use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\Finance\Domain\Models\Wallet;
use App\Modules\Finance\Domain\Models\WalletRechargeRequest;
use App\Support\Notifications\NotificationDispatcher;

/**
 * Request/confirmation workflow for a parent topping up their Academia Pay
 * wallet — mirrors TransportEnrollmentService/CanteenEnrollmentService
 * exactly (parent requests, staff approves/rejects). No real external
 * gateway is active today, so every request currently goes through 'cash'
 * (approved once staff physically receives the deposit); a future active
 * gateway would credit automatically via the webhook instead (see
 * SubscriptionWebhookController's generic WALLET-{id}- handling), bypassing
 * this request table entirely for that path.
 */
class WalletRechargeService
{
    public function __construct(private NotificationDispatcher $notifications)
    {
    }

    public function request(ParentAccount $parent, float $amount, string $method, int $schoolId): WalletRechargeRequest
    {
        $wallet = $parent->getOrCreateWallet();

        return WalletRechargeRequest::create([
            'wallet_id' => $wallet->id,
            'parent_id' => $parent->id,
            'school_id' => $schoolId,
            'amount' => $amount,
            'method' => $method,
            'status' => WalletRechargeRequest::STATUS_PENDING,
        ]);
    }

    public function approve(WalletRechargeRequest $rechargeRequest, User $reviewer): void
    {
        $wallet = Wallet::findOrFail($rechargeRequest->wallet_id);
        $wallet->credit(
            (float) $rechargeRequest->amount,
            "RECHARGE-{$rechargeRequest->id}",
            $rechargeRequest->method,
            "Recharge Academia Pay confirmée par {$reviewer->name}"
        );

        $rechargeRequest->update([
            'status' => WalletRechargeRequest::STATUS_APPROVED,
            'reviewed_by_user_id' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        $amount = number_format((float) $rechargeRequest->amount, 0, ',', ' ');
        $this->notifications->notifyParent(
            $rechargeRequest->parent, 'payment', 'Recharge confirmée',
            "Votre recharge Academia Pay de {$amount} FCFA a été confirmée et ajoutée à votre solde."
        );
    }

    public function reject(WalletRechargeRequest $rechargeRequest, User $reviewer, ?string $reason = null): void
    {
        $rechargeRequest->update([
            'status' => WalletRechargeRequest::STATUS_REJECTED,
            'reviewed_by_user_id' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $amount = number_format((float) $rechargeRequest->amount, 0, ',', ' ');
        $this->notifications->notifyParent(
            $rechargeRequest->parent, 'payment', 'Recharge refusée',
            "Votre demande de recharge Academia Pay de {$amount} FCFA a été refusée" . ($reason ? " ({$reason})." : '.')
        );
    }
}
