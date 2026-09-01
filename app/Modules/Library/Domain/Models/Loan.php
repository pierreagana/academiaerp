<?php

namespace App\Modules\Library\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class Loan extends Model
{
    use BelongsToSchool;
    protected $fillable = [
        'school_id',
        'book_id',
        'borrower_type',
        'borrower_id',
        'borrowed_at',
        'due_at',
        'returned_at',
        'reminded_at',
    ];

    protected $casts = [
        'borrowed_at' => 'date',
        'due_at' => 'date',
        'returned_at' => 'date',
        'reminded_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function borrower()
    {
        return $this->morphTo();
    }

    public function getStatusAttribute(): string
    {
        if ($this->returned_at) {
            return 'returned';
        }

        if ($this->due_at->lt(today())) {
            return 'overdue';
        }

        return 'active';
    }
}
