<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guardian extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name', 'relation', 'phone', 'email', 'address', 'status', 'school_id', 'parent_id'
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_guardian');
    }

    public function parentAccount()
    {
        return $this->belongsTo(ParentAccount::class, 'parent_id');
    }
}
