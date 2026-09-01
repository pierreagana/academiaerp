<?php

namespace App\Modules\Infirmary\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class ConsultationMotive extends Model
{
    use BelongsToSchool;
    protected $table = 'infirmary_consultation_motives';

    protected $fillable = [
        'school_id',
        'name',
    ];
}
