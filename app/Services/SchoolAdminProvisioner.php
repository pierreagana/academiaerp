<?php

namespace App\Services;

use App\Mail\SchoolAdminCredentialsMail;
use App\Models\User;
use App\Modules\SuperAdmin\Domain\Models\School;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SchoolAdminProvisioner
{
    /**
     * Creates the school's admin login account with a freshly generated
     * password and emails the credentials to them. The plaintext password
     * only ever exists for the duration of this call — it's hashed before
     * being persisted and never logged or returned beyond the created User.
     */
    public static function createAndNotify(School $school, string $name, string $email): User
    {
        $plainPassword = Str::password(12, symbols: false);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($plainPassword),
            'role' => 'adminschool',
            'school_id' => $school->id,
        ]);

        try {
            Mail::to($email)->send(new SchoolAdminCredentialsMail($school, $user, $plainPassword));
        } catch (\Throwable $e) {
            // A misconfigured or unreachable SMTP server must never block
            // account creation — the account still works, only the
            // notification failed. Logged so a superadmin can investigate.
            Log::error('Failed to send school admin credentials email', [
                'school_id' => $school->id,
                'user_id' => $user->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }

        return $user;
    }
}
