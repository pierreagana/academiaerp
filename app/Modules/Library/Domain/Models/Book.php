<?php

namespace App\Modules\Library\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'category_id',
        'title',
        'author',
        'isbn',
        'quantity_total',
        'quantity_available',
        'cover_path',
    ];

    public function category()
    {
        return $this->belongsTo(BookCategory::class, 'category_id');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function getStatusAttribute(): string
    {
        if ($this->quantity_available <= 0) {
            return 'checked_out';
        }

        if ($this->quantity_total > 0 && ($this->quantity_available / $this->quantity_total) <= 0.25) {
            return 'low_stock';
        }

        return 'available';
    }
}
