<?php

namespace App\Modules\Infirmary\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationMotive extends Model
{
    protected $table = 'infirmary_consultation_motives';

    protected $fillable = [
        'school_id',
        'name',
    ];
}
