<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class Invoice extends Model
{
    use BelongsToSchool;
    protected $fillable = [
        'invoice_number',
        'school_id',
        'school_name',
        'amount',
        'status',
        'issue_date',
        'due_date',
        'plan_name',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
