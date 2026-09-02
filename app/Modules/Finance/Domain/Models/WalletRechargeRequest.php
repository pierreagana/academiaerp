<?php

namespace App\Modules\Finance\Domain\Models;

use App\Models\User;
use App\Modules\Academic\Domain\Models\ParentAccount;
use Illuminate\Database\Eloquent\Model;

class WalletRechargeRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'wallet_id',
        'parent_id',
        'school_id',
        'amount',
        'method',
        'status',
        'reference',
        'reviewed_by_user_id',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function parent()
    {
        return $this->belongsTo(ParentAccount::class, 'parent_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
