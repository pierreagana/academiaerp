<?php

namespace App\Modules\Bulletin\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BulletinEvaluationType extends Model
{
    use SoftDeletes;

    const HOMEWORK_TYPE_HOMEWORK = 'devoir_maison';
    const HOMEWORK_TYPE_TEST = 'interrogation';

    protected $fillable = ['school_id', 'name', 'coefficient', 'linked_homework_type'];

    protected $casts = [
        'coefficient' => 'decimal:2',
    ];

    public function grades()
    {
        return $this->hasMany(BulletinGrade::class, 'evaluation_type_id');
    }
}
