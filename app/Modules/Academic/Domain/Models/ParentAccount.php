<?php

namespace App\Modules\Academic\Domain\Models;

use App\Modules\SchoolTrack\Domain\Models\SchoolTrackSubscription;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ParentAccount extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['name', 'phone', 'email', 'password', 'fcm_token'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function guardianRecords()
    {
        return $this->hasMany(Guardian::class, 'parent_id');
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
}
