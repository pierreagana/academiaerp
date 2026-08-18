<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['name', 'code', 'is_default'];
}
