<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class AwardType extends Model
{
    use BelongsToSchool;
    public const CATEGORIES = [
        'Récompenses académiques',
        'Récompenses de comportement',
        'Récompenses sportives',
        'Récompenses culturelles et artistiques',
        'Récompenses technologiques et scientifiques',
        'Récompenses citoyennes et sociales',
        'Récompenses spéciales',
    ];

    protected $fillable = ['school_id', 'category', 'name', 'order'];

    public function awards()
    {
        return $this->hasMany(Award::class);
    }

    public function diplomaTemplate()
    {
        return $this->hasOne(DiplomaTemplate::class);
    }

    /** Global catalog (shared by every school) plus this school's own custom models. */
    public static function availableFor(int $schoolId)
    {
        return self::where(fn ($q) => $q->whereNull('school_id')->orWhere('school_id', $schoolId));
    }
}
