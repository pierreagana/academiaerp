<?php

namespace App\Modules\Library\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class BookCategory extends Model
{
    use BelongsToSchool;
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
