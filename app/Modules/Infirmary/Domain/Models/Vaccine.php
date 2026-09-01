<?php

namespace App\Modules\Infirmary\Domain\Models;

use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\Academic\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class Vaccine extends Model
{
    use BelongsToSchool;
    protected $table = 'infirmary_vaccines';

    protected $fillable = [
        'school_id',
        'student_id',
        'name',
        'administered_at',
        'next_due_at',
        'notes',
        'added_by_parent_id',
    ];

    protected $casts = [
        'administered_at' => 'date',
        'next_due_at' => 'date',
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
