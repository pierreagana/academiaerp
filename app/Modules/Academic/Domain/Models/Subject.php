<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\Tenancy\BelongsToSchool;

class Subject extends Model
{
    use SoftDeletes, BelongsToSchool;
    
    protected $fillable = ['name', 'code', 'type', 'color', 'coefficient', 'language_id', 'school_id'];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'subject_teacher');
    }
}
