<?php

namespace App\Modules\Infirmary\Domain\Models;

use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\Academic\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class PrescriptionDocument extends Model
{
    use BelongsToSchool;
    protected $table = 'infirmary_prescription_documents';

    protected $fillable = [
        'school_id',
        'student_id',
        'name',
        'file_path',
        'mime_type',
        'size_bytes',
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
