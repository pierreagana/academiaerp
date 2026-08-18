<?php

namespace App\Modules\Academic\Application\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PortalAccountService
{
    /**
     * Create or update the portal login (`users` row) linked to a Teacher/Staff record.
     *
     * @param Model $owner The Teacher or Staff instance the login belongs to.
     * @param string $ownerColumn 'teacher_id' or 'staff_id'.
     * @param array $attrs first_name, last_name, email, login_id, role_id.
     * @param string|null $plainPassword Plaintext password, or null to leave it unchanged.
     */
    public function sync(Model $owner, string $ownerColumn, array $attrs, ?string $plainPassword, int $schoolId): void
    {
        if (empty($attrs['login_id'])) {
            return;
        }

        $user = User::where($ownerColumn, $owner->id)->first();

        if (!$user && empty($plainPassword)) {
            return;
        }

        $data = [
            'name' => trim(($attrs['first_name'] ?? '') . ' ' . ($attrs['last_name'] ?? '')),
            'email' => $attrs['email'] ?? $user->email ?? ($attrs['login_id'] . '@portal.local'),
            'login_id' => $attrs['login_id'],
            'role' => 'staff_portal',
            'role_id' => $attrs['role_id'] ?? null,
            'school_id' => $schoolId,
            $ownerColumn => $owner->id,
        ];

        if (!empty($plainPassword)) {
            $data['password'] = $plainPassword;
        }

        if ($user) {
            $user->update($data);
        } else {
            User::create($data);
        }
    }
}
