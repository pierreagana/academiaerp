<?php

namespace App\Modules\Finance\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ScholarshipDocument extends Model
{
    protected $fillable = [
        'scholarship_id',
        'label',
        'file_path',
        'status',
    ];

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
}
