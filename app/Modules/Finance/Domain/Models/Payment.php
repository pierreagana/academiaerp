<?php

namespace App\Modules\Finance\Domain\Models;

use App\Modules\Academic\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'school_id',
        'student_id',
        'type',
        'amount',
        'method',
        'reference',
        'paid_at',
        'note',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public const METHODS = [
        'cash' => 'Espèces',
        'bank_transfer' => 'Virement Bancaire',
        'wave' => 'Wave Mobile Money',
        'orange_money' => 'Orange Money',
        'card' => 'Carte Bancaire',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
