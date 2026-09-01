<?php

namespace App\Modules\Finance\Domain\Models;

use App\Modules\Finance\Domain\Exceptions\InsufficientWalletBalanceException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    protected $fillable = ['owner_type', 'owner_id', 'balance', 'currency'];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function owner()
    {
        return $this->morphTo();
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Credit the wallet (e.g. a confirmed recharge) and record the ledger entry.
     * Row-locked so a concurrent debit can't read a stale balance mid-transaction.
     */
    public function credit(float $amount, string $reference, ?string $gatewaySlug = null, ?string $description = null): WalletTransaction
    {
        return DB::transaction(function () use ($amount, $reference, $gatewaySlug, $description) {
            $wallet = static::query()->lockForUpdate()->findOrFail($this->id);
            $newBalance = round((float) $wallet->balance + $amount, 2);
            $wallet->update(['balance' => $newBalance]);

            return $wallet->transactions()->create([
                'type' => 'recharge',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference' => $reference,
                'gateway_slug' => $gatewaySlug,
                'description' => $description,
            ]);
        });
    }

    /**
     * Debit the wallet (e.g. paying an invoice from the balance). Refuses if
     * insufficient — row-locked so two concurrent debits can never both succeed
     * and push the balance negative.
     *
     * @throws InsufficientWalletBalanceException
     */
    public function debit(float $amount, string $reference, ?string $description = null): WalletTransaction
    {
        return DB::transaction(function () use ($amount, $reference, $description) {
            $wallet = static::query()->lockForUpdate()->findOrFail($this->id);

            if ((float) $wallet->balance < $amount) {
                throw new InsufficientWalletBalanceException((float) $wallet->balance, $amount);
            }

            $newBalance = round((float) $wallet->balance - $amount, 2);
            $wallet->update(['balance' => $newBalance]);

            return $wallet->transactions()->create([
                'type' => 'debit',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference' => $reference,
                'description' => $description,
            ]);
        });
    }
}
