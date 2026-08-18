<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Semester extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['name', 'start_date', 'end_date', 'is_current', 'school_id', 'academic_year', 'term_number'];
}
