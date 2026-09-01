<?php

namespace App\Modules\Infirmary\Domain\Models;

use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\Academic\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class Allergy extends Model
{
    use BelongsToSchool;
    protected $table = 'infirmary_allergies';

    protected $fillable = [
        'school_id',
        'student_id',
        'name',
        'severity',
        'notes',
        'added_by_parent_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function addedByParent()
    {
        return $this->belongsTo(ParentAccount::class, 'added_by_parent_id');
    }
}
