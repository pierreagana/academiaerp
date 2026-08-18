<?php

namespace App\Modules\Presence\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class AccessPoint extends Model
{
    protected $table = 'access_points';

    protected $fillable = ['school_id', 'name'];
}
