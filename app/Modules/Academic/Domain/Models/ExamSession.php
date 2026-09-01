<?php

namespace App\Modules\Academic\Domain\Models;

use App\Modules\SuperAdmin\Domain\Models\School;
use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

/**
 * A school's validated result set for one official exam (bac/bepc/cepe/bts)
 * in one academic year. The pass rate isn't typed in by hand — it's derived
 * from `presented_count`/`admitted_count`, which in turn come straight from
 * the per-student rows in `exam_session_students`.
 */
class ExamSession extends Model
{
    use BelongsToSchool;
    public const TYPE_CEPE = 'cepe';
    public const TYPE_BEPC = 'bepc';
    public const TYPE_BAC = 'bac';
    public const TYPE_BTS = 'bts';

    /** Exam type => the AcademicClass::LEVELS_BY_CYCLE grade(s) that sit it. */
    public const LEVELS_BY_TYPE = [
        self::TYPE_CEPE => ['CM2'],
        self::TYPE_BEPC => ['3ème'],
        self::TYPE_BAC => ['Terminale'],
        self::TYPE_BTS => ['BTS1', 'BTS2'],
    ];

    public const LABELS = [
        self::TYPE_CEPE => 'CEPE',
        self::TYPE_BEPC => 'BEPC',
        self::TYPE_BAC => 'BAC',
        self::TYPE_BTS => 'BTS',
    ];

    protected $fillable = [
        'school_id', 'exam_type', 'academic_year',
        'presented_count', 'admitted_count', 'validated_at', 'validated_by',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    public static function levelsForType(string $type): array
    {
        return self::LEVELS_BY_TYPE[$type] ?? [];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classes()
    {
        return $this->belongsToMany(AcademicClass::class, 'exam_session_class');
    }

    public function results()
    {
        return $this->hasMany(ExamSessionStudent::class);
    }

    public function validator()
    {
        return $this->belongsTo(\App\Models\User::class, 'validated_by');
    }

    public function isValidated(): bool
    {
        return $this->validated_at !== null;
    }

    public function failedCount(): int
    {
        return max(0, $this->presented_count - $this->admitted_count);
    }

    public function successRate(): ?int
    {
        if ($this->presented_count <= 0) {
            return null;
        }

        return (int) round($this->admitted_count / $this->presented_count * 100);
    }
}
