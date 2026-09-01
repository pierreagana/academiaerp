<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\Tenancy\BelongsToSchool;

class AcademicClass extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $fillable = ['name', 'level', 'cycle', 'capacity', 'school_id', 'branch_id'];

    /**
     * Official education levels grouped by cycle. `level` is the specific grade
     * (e.g. "6ème"); `cycle` is always derived from it (see cycleForLevel()) —
     * never set independently, so the two can never end up inconsistent.
     */
    public const LEVELS_BY_CYCLE = [
        'Cycle préscolaire' => ['PS', 'MS', 'GS'],
        'Cycle primaire' => ['CP1', 'CP2', 'CE1', 'CE2', 'CM1', 'CM2'],
        '1er Cycle' => ['6ème', '5ème', '4ème', '3ème'],
        '2nd Cycle' => ['2nde', '1ère', 'Terminale'],
        'Cycle supérieur' => ['BTS1', 'BTS2'],
    ];

    public static function allLevels(): array
    {
        return array_merge(...array_values(self::LEVELS_BY_CYCLE));
    }

    public static function cycleForLevel(?string $level): ?string
    {
        foreach (self::LEVELS_BY_CYCLE as $cycle => $levels) {
            if (in_array($level, $levels, true)) {
                return $cycle;
            }
        }

        return null;
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'academic_class_teacher', 'academic_class_id', 'teacher_id')->withTimestamps();
    }

    public function headTeacher()
    {
        return $this->belongsTo(Teacher::class, 'head_teacher_id');
    }

    public function subjects()
    {
        // `syllabuses` has one row per (class, subject, semester), not a plain
        // class<->subject pivot — without distinct(), a class with N semesters
        // returns every subject N times (also inflated bulletin completion counts).
        return $this->belongsToMany(Subject::class, 'syllabuses', 'academic_class_id', 'subject_id')->distinct();
    }
}
