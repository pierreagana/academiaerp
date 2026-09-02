<?php

namespace App\Modules\Academic\Domain\Models;

use App\Modules\SchoolTrack\Domain\Models\SchoolTrackSubscription;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ParentAccount extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['name', 'phone', 'email', 'password', 'password_changed_at', 'address', 'latitude', 'longitude'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'password_changed_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function guardianRecords()
    {
        return $this->hasMany(Guardian::class, 'parent_id');
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class, 'parent_id');
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class, 'parent_id');
    }

    public function schoolTrackSubscriptions()
    {
        return $this->hasMany(SchoolTrackSubscription::class, 'parent_id');
    }

    /** The parent's currently-valid School Track access, or null if never subscribed / lapsed. */
    public function activeSchoolTrackSubscription(): ?SchoolTrackSubscription
    {
        return $this->schoolTrackSubscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();
    }

    public function wallet()
    {
        return $this->morphOne(\App\Modules\Finance\Domain\Models\Wallet::class, 'owner');
    }

    public function getOrCreateWallet(): \App\Modules\Finance\Domain\Models\Wallet
    {
        return $this->wallet ?? $this->wallet()->create(['balance' => 0, 'currency' => 'XOF']);
    }

    public function walletRechargeRequests()
    {
        return $this->hasMany(\App\Modules\Finance\Domain\Models\WalletRechargeRequest::class, 'parent_id');
    }

    public function notificationPreference()
    {
        return $this->hasOne(\App\Modules\Transport\Domain\Models\NotificationPreference::class, 'parent_id');
    }

    public function getOrCreateNotificationPreference(): \App\Modules\Transport\Domain\Models\NotificationPreference
    {
        // ->fresh(): create([]) relies on the table's own column defaults
        // (see migration) rather than repeating them here — but Eloquent's
        // insertGetId only fetches the new primary key back, not those
        // DB-computed default values, so the in-memory instance needs a
        // reload or every boolean/nullable field reads back as unset (PHP
        // null) despite the row actually holding real 1/0 values.
        return $this->notificationPreference ?? $this->notificationPreference()->create([])->fresh();
    }
}
