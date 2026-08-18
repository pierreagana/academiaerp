<?php

namespace App\Modules\Library\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class LibrarySetting extends Model
{
    protected $fillable = [
        'school_id',
        'max_books_per_student',
        'loan_duration_days',
        'late_fee_per_day',
        'enforce_fees',
        'card_format',
        'auto_renew_staff_cards',
    ];

    protected $casts = [
        'enforce_fees' => 'boolean',
        'auto_renew_staff_cards' => 'boolean',
    ];
}
