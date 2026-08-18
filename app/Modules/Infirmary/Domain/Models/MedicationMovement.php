<?php

namespace App\Modules\Infirmary\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationMovement extends Model
{
    protected $table = 'infirmary_medication_movements';

    protected $fillable = [
        'school_id',
        'medication_id',
        'type',
        'quantity',
        'source',
        'created_by',
    ];

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
