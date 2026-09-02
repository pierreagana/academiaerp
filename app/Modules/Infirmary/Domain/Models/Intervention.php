<?php

namespace App\Modules\Infirmary\Domain\Models;

use App\Modules\Academic\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class Intervention extends Model
{
    use BelongsToSchool;
    protected $table = 'infirmary_interventions';

    public const DECISIONS = [
        'retour_classe' => 'Retour en Classe',
        'repos_infirmerie' => 'Repos Infirmerie',
        'parents_appeles' => 'Parents Appelés',
    ];

    protected $fillable = [
        'school_id',
        'student_id',
        'arrival_time',
        'temperature',
        'motive',
        'care_notes',
        'decision',
        'created_by',
    ];

    protected $casts = [
        'arrival_time' => 'datetime',
        'temperature' => 'decimal:1',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
