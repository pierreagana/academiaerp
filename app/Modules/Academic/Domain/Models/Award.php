<?php

namespace App\Modules\Academic\Domain\Models;

use App\Models\User;
use App\Modules\SuperAdmin\Domain\Models\School;
use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class Award extends Model
{
    use BelongsToSchool;
    public const RECIPIENT_TYPES = [
        'student' => 'Élève',
        'teacher' => 'Enseignant',
        'staff' => 'Personnel',
    ];

    public const MATERIAL_REWARDS = [
        "Bourse d'études",
        'Réduction sur les frais de scolarité',
        "Bon d'achat",
        'Ordinateur portable',
        'Tablette',
        'Livres',
        'Fournitures scolaires',
        'Trophée',
        'Médaille',
        'Certificat',
        'Attestation',
        'Cadeau',
    ];

    protected $fillable = [
        'school_id', 'award_type_id', 'recipient_type', 'recipient_id',
        'material_reward', 'reason', 'awarded_date', 'awarded_by',
    ];

    protected $casts = [
        'awarded_date' => 'date',
    ];

    public function type()
    {
        return $this->belongsTo(AwardType::class, 'award_type_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function awardedBy()
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    /**
     * Manual recipient resolution instead of Eloquent's native morphTo() —
     * this app stores short type strings ('student'/'teacher'/'staff') without
     * a registered Relation::morphMap() for them (only 'driver' is mapped,
     * in the Transport module), so a real morphTo() here would try to
     * instantiate a literal "student" class and fail.
     */
    public function recipient(): Student|Teacher|Staff|null
    {
        return match ($this->recipient_type) {
            'student' => Student::find($this->recipient_id),
            'teacher' => Teacher::find($this->recipient_id),
            'staff' => Staff::find($this->recipient_id),
            default => null,
        };
    }
}
