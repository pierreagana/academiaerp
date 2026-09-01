<?php

namespace App\Modules\Academic\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class StudentDisciplinaryRecord extends Model
{
    use BelongsToSchool;
    public const CATEGORY_DISTINCTION = 'distinction';
    public const CATEGORY_SANCTION = 'sanction';

    public const DISTINCTION_TYPES = [
        'felicitations' => 'Félicitations',
        'encouragements' => 'Encouragements',
        'tableau_honneur' => "Tableau d'honneur",
        'prix' => 'Prix',
    ];

    public const SANCTION_TYPES = [
        'avertissement' => 'Avertissement',
        'sanction_disciplinaire' => 'Sanction disciplinaire',
    ];

    protected $fillable = [
        'school_id', 'student_id', 'category', 'type', 'description', 'recorded_date', 'recorded_by',
    ];

    protected $casts = [
        'recorded_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
