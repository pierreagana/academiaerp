<?php

namespace App\Modules\Presence\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class AccessPoint extends Model
{
    use BelongsToSchool;
    protected $table = 'access_points';

    protected $fillable = ['school_id', 'name'];
}
