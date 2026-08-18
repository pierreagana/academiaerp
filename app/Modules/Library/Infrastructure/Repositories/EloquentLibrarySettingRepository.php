<?php

namespace App\Modules\Library\Infrastructure\Repositories;

use App\Modules\Library\Domain\Models\LibrarySetting;
use App\Modules\Library\Domain\Repositories\LibrarySettingRepositoryInterface;

class EloquentLibrarySettingRepository implements LibrarySettingRepositoryInterface
{
    public function getForSchool(int $schoolId)
    {
        return LibrarySetting::firstOrCreate(
            ['school_id' => $schoolId],
            [
                'max_books_per_student' => 4,
                'loan_duration_days' => 14,
                'late_fee_per_day' => 0,
                'enforce_fees' => false,
                'card_format' => 'digital_qr',
                'auto_renew_staff_cards' => true,
            ]
        );
    }

    public function updateRules(int $schoolId, array $data)
    {
        $settings = $this->getForSchool($schoolId);
        $settings->update($data);
        return $settings;
    }

    public function updateAccess(int $schoolId, array $data)
    {
        $settings = $this->getForSchool($schoolId);
        $settings->update($data);
        return $settings;
    }
}
