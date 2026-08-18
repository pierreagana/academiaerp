<?php

namespace App\Modules\Academic\Application\Services;

use App\Modules\Academic\Domain\Models\Guardian;
use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\SuperAdmin\Domain\Models\School;
use Illuminate\Support\Str;

class ParentPortalAccountService
{
    /**
     * Links a Guardian to the parent portal account matching its phone number —
     * creates one if none exists yet, or joins the existing one (this is what
     * makes the multi-school case work: a second school entering the same real
     * parent's phone number for a different child automatically joins the same
     * account instead of creating a duplicate login).
     *
     * @return string|null The generated plaintext password, only when a brand new account was created (null when joining an existing one — no new credentials to hand out).
     */
    public function sync(Guardian $guardian, string $name, string $phone, ?string $email): ?string
    {
        $existing = ParentAccount::where('phone', $phone)->first();

        if ($existing) {
            $guardian->update(['parent_id' => $existing->id]);

            return null;
        }

        $password = Str::password(10, symbols: false);

        $account = ParentAccount::create([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'password' => $password,
        ]);

        $guardian->update(['parent_id' => $account->id]);

        return $password;
    }

    /**
     * Self-service registration: a parent creates their own identity account,
     * unattached to any school. It only becomes useful once claimChild() links
     * it to real Guardian records.
     */
    public function registerSelf(array $data): ParentAccount
    {
        return ParentAccount::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
        ]);
    }

    /**
     * Links the authenticated parent to a child via school code + matricule, but
     * only if the phone already on file for one of that student's Guardian rows
     * matches the parent's own phone — that match (not the code/matricule, which
     * aren't secret) is the actual security boundary. Returns false on any
     * mismatch so callers can show one generic error regardless of which part
     * failed, avoiding an enumeration oracle.
     */
    public function claimChild(ParentAccount $parent, string $schoolCode, string $matricule): bool
    {
        $school = School::where('code', $schoolCode)->first();
        if (!$school) {
            return false;
        }

        $student = Student::where('school_id', $school->id)->where('roll_number', $matricule)->first();
        if (!$student) {
            return false;
        }

        $matchingGuardians = $student->guardians()->where('phone', $parent->phone)->get();
        if ($matchingGuardians->isEmpty()) {
            return false;
        }

        $claimed = false;
        foreach ($matchingGuardians as $guardian) {
            if ($guardian->parent_id === null || $guardian->parent_id === $parent->id) {
                $guardian->update(['parent_id' => $parent->id]);
                $claimed = true;
            }
        }

        return $claimed;
    }
}
