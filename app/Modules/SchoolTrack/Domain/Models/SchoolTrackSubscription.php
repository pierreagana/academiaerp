<?php

namespace App\Modules\SchoolTrack\Domain\Models;

use App\Modules\Academic\Domain\Models\ParentAccount;
use Illuminate\Database\Eloquent\Model;

/**
 * A parent's individual subscription to the School Track discovery tool —
 * unrelated to any school's SaaS package/forfait. No payment gateway exists
 * in this project, so `payment_method`/`amount_paid` record a self-reported
 * transaction the same way the existing fee-payment flow does; there is no
 * recurring auto-billing behind `expires_at` — access simply lapses until
 * the parent subscribes again.
 */
class SchoolTrackSubscription extends Model
{
    public const PLAN_MONTHLY = 'mensuel';
    public const PLAN_YEARLY = 'annuel';

    /** Placeholder default pricing (FCFA) — easy to change here, nowhere else. */
    public const PLAN_PRICES = [
        self::PLAN_MONTHLY => 2000.0,
        self::PLAN_YEARLY => 20000.0,
    ];

    protected $fillable = [
        'parent_id',
        'plan',
        'amount_paid',
        'payment_method',
        'status',
        'subscribed_at',
        'expires_at',
        'cancelled_at',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'subscribed_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(ParentAccount::class, 'parent_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }
}
