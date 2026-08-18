<?php

namespace App\Modules\Library\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class BookCategory extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'color',
    ];

    public function books()
    {
        return $this->hasMany(Book::class, 'category_id');
    }
}
