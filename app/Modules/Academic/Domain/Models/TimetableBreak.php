<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\Tenancy\BelongsToSchool;

class TimetableBreak extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $fillable = ['name', 'color', 'start_time', 'end_time', 'school_id', 'academic_class_id', 'day_of_week'];

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class);
    }

    /**
     * Tailwind color families the timetable grid knows how to render
     * (interpolated as bg-{color}-50, border-{color}-200, text-{color}-700, ...).
     */
    public static function availableColors(): array
    {
        return ['slate', 'gray', 'zinc', 'amber', 'orange', 'rose', 'red', 'emerald', 'teal', 'sky', 'indigo', 'violet'];
    }

    public static function days(): array
    {
        return ['lundi' => 'Lundi', 'mardi' => 'Mardi', 'mercredi' => 'Mercredi', 'jeudi' => 'Jeudi', 'vendredi' => 'Vendredi'];
    }
}
