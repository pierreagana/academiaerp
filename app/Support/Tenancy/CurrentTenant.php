<?php

namespace App\Support\Tenancy;

use Illuminate\Support\Facades\Auth;

class CurrentTenant
{
    /**
     * The school_id every tenant-scoped query/write should be confined to,
     * or null when the caller must see/write across every school
     * (superadmin, or no authenticated web user at all — artisan, seeders,
     * queue workers).
     */
    public static function schoolId(): ?int
    {
        $user = Auth::user();

        if (!$user || $user->role === 'superadmin') {
            return null;
        }

        return $user->school_id;
    }
}
