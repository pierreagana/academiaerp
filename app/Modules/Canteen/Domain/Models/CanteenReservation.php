<?php

namespace App\Modules\Canteen\Domain\Models;

use App\Modules\Academic\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;

class CanteenReservation extends Model
{
    protected $table = 'canteen_reservations';

    /** Mirrors the real slot values used by the School Dashboard's menu planner. */
    public const SLOT_LABELS = [
        'breakfast' => 'Petit-déjeuner',
        'starter' => 'Entrée',
        'main' => 'Plat',
        'dessert' => 'Dessert',
    ];

    protected $fillable = [
        'school_id',
        'student_id',
        'menu_item_id',
        'date',
        'slot',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
